<?php

namespace App\Services;

use App\DTOs\AttendanceData;
use App\DTOs\AttendanceSubmitData;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\SchoolSetting;
use App\Models\Schedule;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected GeofencingService $geofencingService,
        protected QrTokenService $qrTokenService
    ) {}

    /**
     * Process presence clock-in or clock-out for teachers (legacy method compatible with DTO).
     */
    public function recordAttendance(AttendanceData $dto): array
    {
        // For compatibility, we map AttendanceData to AttendanceSubmitData
        $submitData = new AttendanceSubmitData(
            teacher_id: $dto->teacher_id,
            action_type: $this->determineAttendanceType($dto->teacher_id, Carbon::today()->toDateString()),
            latitude: $dto->latitude,
            longitude: $dto->longitude,
            method: $dto->method,
            qr_token: $dto->qr_token,
            status: null,
            date: null,
            ip_address: $dto->ip_address,
            user_agent: $dto->user_agent
        );

        return $this->submitAttendance($submitData);
    }

    /**
     * Helper to determine if pending presence is clock_in or clock_out.
     */
    protected function determineAttendanceType(int $teacherId, string $date): string
    {
        $attendance = $this->attendanceRepository->findByTeacherAndDate($teacherId, $date);
        return ($attendance && $attendance->clock_in && !$attendance->clock_out) ? 'clock_out' : 'clock_in';
    }

    /**
     * Submit core presence with transaction and validations.
     */
    public function submitAttendance(AttendanceSubmitData $dto): array
    {
        $today = $dto->date ?: Carbon::today()->toDateString();
        $now = Carbon::now();
        $timeString = $now->toTimeString();
        $teacher = Teacher::findOrFail($dto->teacher_id);
        $unit = $teacher->unit;

        if (!$unit) {
            throw new \Exception("Anda belum terdaftar di unit pendidikan mana pun.");
        }

        // 1. Fetch unit coordinates and geofence radius
        $schoolLat = $unit->latitude;
        $schoolLng = $unit->longitude;
        $schoolRadius = $unit->gps_radius;

        if ($schoolLat === null || $schoolLng === null) {
            throw new \Exception("Lokasi GPS unit Anda belum dikonfigurasi oleh administrator.");
        }

        // Calculate distance in meters using Haversine formula
        $distance = $this->geofencingService->calculateDistance(
            $dto->latitude,
            $dto->longitude,
            $schoolLat,
            $schoolLng
        );

        // 2. Fetch schedule for today's day of week
        $dayOfWeek = strtolower(Carbon::now()->format('l')); // 'monday', 'saturday', etc.
        $schedule = Schedule::where('unit_id', $unit->id)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$schedule || !$schedule->is_active) {
            throw new \Exception("Hari ini (" . ucfirst($dayOfWeek) . ") tidak ada jadwal presensi aktif untuk unit Anda.");
        }

        return DB::transaction(function () use ($dto, $today, $timeString, $teacher, $unit, $schoolRadius, $distance, $schedule) {
            
            // A. Check for Izin / Sakit / Alpa submissions (usually recorded by admin or system)
            $isManualStatus = $dto->status && in_array($dto->status, ['izin', 'sakit', 'alpa']);
            if ($isManualStatus) {
                // Check if already has check-in
                $existing = $this->attendanceRepository->findByTeacherAndDate($dto->teacher_id, $today);
                if ($existing) {
                    throw new \Exception("Sudah ada rekaman presensi hari ini.");
                }

                $attendance = $this->attendanceRepository->save([
                    'teacher_id' => $dto->teacher_id,
                    'date' => $today,
                    'status' => $dto->status,
                    'unit_id' => $unit->id,
                    'status_masuk' => ucfirst($dto->status),
                    'status_pulang' => null,
                    'reward' => false,
                ]);

                // Create log
                $this->attendanceRepository->createLog([
                    'attendance_id' => $attendance->id,
                    'teacher_id' => $dto->teacher_id,
                    'type' => 'clock_in',
                    'latitude' => 0,
                    'longitude' => 0,
                    'distance_meters' => 0,
                    'method' => 'manual',
                    'ip_address' => $dto->ip_address,
                    'user_agent' => $dto->user_agent,
                    'log_status' => 'accepted',
                    'reason' => 'Admin marked as ' . $dto->status,
                    'unit_id' => $unit->id,
                ]);

                // Record Spatie activity
                activity()
                    ->performedOn($attendance)
                    ->log("Mencatat status '{$dto->status}' untuk guru {$teacher->name} pada tanggal {$today}");

                return [
                    'success' => true,
                    'message' => "Status {$dto->status} berhasil dicatat.",
                ];
            }

            // B. GPS Accuracy & Geofence Checks (For both GPS and QR methods)
            $accuracyThreshold = (float) SchoolSetting::getValue('gps_accuracy_threshold', 50.0, $unit->id);
            if ($dto->accuracy > $accuracyThreshold) {
                // Log failed attempt
                $this->attendanceRepository->createLog([
                    'attendance_id' => null,
                    'teacher_id' => $dto->teacher_id,
                    'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'accuracy' => $dto->accuracy,
                    'distance_meters' => $distance,
                    'method' => $dto->method,
                    'ip_address' => $dto->ip_address,
                    'user_agent' => $dto->user_agent,
                    'log_status' => 'rejected',
                    'reason' => 'GPS_ACCURACY_TOO_LOW',
                    'unit_id' => $unit->id,
                ]);

                throw new \Exception("Lokasi GPS kurang akurat. Aktifkan GPS dengan akurasi tinggi dan coba kembali.");
            }

            if ($distance > $schoolRadius) {
                // Log failed attempt
                $this->attendanceRepository->createLog([
                    'attendance_id' => null,
                    'teacher_id' => $dto->teacher_id,
                    'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'accuracy' => $dto->accuracy,
                    'distance_meters' => $distance,
                    'method' => $dto->method,
                    'ip_address' => $dto->ip_address,
                    'user_agent' => $dto->user_agent,
                    'log_status' => 'rejected',
                    'reason' => 'OUTSIDE_GEOFENCE',
                    'unit_id' => $unit->id,
                ]);

                throw new \Exception("Anda berada di luar area presensi unit.");
            }

            // QR Code Specific Validation
            if ($dto->method === 'qr') {
                if (!$dto->qr_token) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'INVALID_QR',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code tidak valid.");
                }

                // Decrypt
                $decrypted = null;
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($dto->qr_token);
                } catch (\Exception $e) {
                    // Handled below
                }

                if (!$decrypted) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'INVALID_QR',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code tidak valid.");
                }

                $payload = json_decode($decrypted, true);
                if (!$payload || !isset($payload['timestamp'])) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'INVALID_QR',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code tidak valid.");
                }

                // Check Expiry
                $qrMaxAge = max(90, ((int) SchoolSetting::getValue('qr_rotation_interval', 30, $unit->id)) * 3);
                $age = time() - (int) $payload['timestamp'];
                if ($age < -5 || $age > $qrMaxAge) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'EXPIRED_QR',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code sudah kedaluwarsa.");
                }

                // Check Unit ID (Cross-unit protection)
                if (!isset($payload['unit_id']) || (int) $payload['unit_id'] !== (int) $unit->id) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'UNIT_MISMATCH',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code bukan untuk unit Anda.");
                }

                // Check Replay Protection
                $tokenHash = hash('sha256', $dto->qr_token);
                $alreadyUsed = \Illuminate\Support\Facades\DB::table('qr_token_usages')->where('token_hash', $tokenHash)->exists();
                if ($alreadyUsed) {
                    $this->attendanceRepository->createLog([
                        'attendance_id' => null,
                        'teacher_id' => $dto->teacher_id,
                        'type' => $dto->action_type === 'check_in' ? 'clock_in' : 'clock_out',
                        'latitude' => $dto->latitude,
                        'longitude' => $dto->longitude,
                        'accuracy' => $dto->accuracy,
                        'distance_meters' => $distance,
                        'method' => 'qr',
                        'ip_address' => $dto->ip_address,
                        'user_agent' => $dto->user_agent,
                        'log_status' => 'rejected',
                        'reason' => 'REPLAY_TOKEN',
                        'unit_id' => $unit->id,
                    ]);

                    throw new \Exception("QR Code sudah digunakan.");
                }

                // Record usage (this is inside transaction, so it will roll back if later validations fail)
                \Illuminate\Support\Facades\DB::table('qr_token_usages')->insert([
                    'token_hash' => $tokenHash,
                    'unit_id' => $unit->id,
                    'teacher_id' => $dto->teacher_id,
                    'attendance_type' => $dto->action_type,
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'accuracy' => $dto->accuracy,
                    'used_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // C. Core Check-In / Check-Out Logic
            $attendance = $this->attendanceRepository->findByTeacherAndDate($dto->teacher_id, $today);

            if ($dto->action_type === 'check_in') {
                // 1. Validasi presensi ganda check-in
                if ($attendance && $attendance->clock_in) {
                    throw new \Exception("Anda sudah melakukan presensi masuk hari ini.");
                }

                // 2. Evaluate check-in time status
                $rewardLimit = $schedule->reward_limit_time;
                $lateThreshold = $schedule->late_threshold_time;

                $statusMasuk = 'Tepat Waktu';
                $status = 'hadir'; // backward compatibility
                $reward = false;
                $penalty = 0.00;

                if ($timeString <= $rewardLimit) {
                    $statusMasuk = 'Tepat Waktu';
                    $reward = true;
                } elseif ($timeString <= $lateThreshold) {
                    $statusMasuk = 'Tepat Waktu';
                    $reward = false;
                } else {
                    $statusMasuk = 'Terlambat';
                    $status = 'terlambat'; // backward compatibility
                    $reward = false;
                    $penalty = 0.00;
                }

                $attendance = $this->attendanceRepository->save([
                    'teacher_id' => $dto->teacher_id,
                    'date' => $today,
                    'clock_in' => $timeString,
                    'status' => $status,
                    'penalty' => $penalty,
                    'unit_id' => $unit->id,
                    'status_masuk' => $statusMasuk,
                    'status_pulang' => null,
                    'reward' => $reward,
                ]);

                // Create log
                $this->attendanceRepository->createLog([
                    'attendance_id' => $attendance->id,
                    'teacher_id' => $dto->teacher_id,
                    'type' => 'clock_in',
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'accuracy' => $dto->accuracy,
                    'distance_meters' => $distance,
                    'method' => $dto->method,
                    'ip_address' => $dto->ip_address,
                    'user_agent' => $dto->user_agent,
                    'log_status' => 'accepted',
                    'reason' => null,
                    'unit_id' => $unit->id,
                ]);

                // Record Spatie activity
                activity()
                    ->performedOn($attendance)
                    ->log("Guru {$teacher->name} melakukan absen masuk dengan status {$statusMasuk} pada {$timeString}");

                $msg = "Presensi masuk berhasil dicatat pada " . Carbon::parse($timeString)->format('H:i') . " WIB.";
                if ($reward) {
                    $msg .= " Selamat, Anda mendapatkan Reward!";
                }

                return [
                    'success' => true,
                    'message' => $msg,
                    'status' => $statusMasuk,
                    'time' => Carbon::parse($timeString)->format('H:i'),
                ];
            } 
            
            // Check-Out
            elseif ($dto->action_type === 'check_out') {
                // 1. Validasi belum check-in
                if (!$attendance || !$attendance->clock_in) {
                    throw new \Exception("Anda belum melakukan presensi masuk hari ini.");
                }

                // 2. Validasi ganda check-out
                if ($attendance->clock_out) {
                    throw new \Exception("Anda sudah melakukan presensi pulang hari ini.");
                }

                // 3. Validasi rentang jam pulang (bisa diakses jam berapa saja maksimal jam 6 sore / 18:00)
                if ($timeString <= $attendance->clock_in) {
                    throw new \Exception("Waktu presensi pulang tidak boleh sebelum atau sama dengan waktu presensi masuk.");
                }

                $maxEndTime = SchoolSetting::getValue('work_end_time_end', '18:00:00', $unit->id);
                if (strlen($maxEndTime) === 5) {
                    $maxEndTime .= ':00';
                }
                if ($timeString > $maxEndTime) {
                    $formattedMaxTime = Carbon::parse($maxEndTime)->format('H:i');
                    throw new \Exception("Batas waktu presensi pulang hari ini sudah berakhir (Maksimal: {$formattedMaxTime} WIB).");
                }

                // Evaluate status pulang: jika pulang sebelum jadwalnya atau sebelum jam 15.15 maka kasih keterangan pulang lebih awal
                // Khusus hari Sabtu, jadwal pulang berakhir jam 13:00 sehingga batas pulang awal adalah 13:00
                $isSaturday = Carbon::parse($today)->isSaturday();
                $thresholdTime = $isSaturday ? $schedule->work_end_time : '15:15:00';

                if ($timeString < $schedule->work_end_time || $timeString < $thresholdTime) {
                    $statusPulang = 'Pulang Lebih Awal';
                } else {
                    $statusPulang = 'Normal';
                }

                $attendance->clock_out = $timeString;
                $attendance->status_pulang = $statusPulang;
                $attendance->save();

                // Create log
                $this->attendanceRepository->createLog([
                    'attendance_id' => $attendance->id,
                    'teacher_id' => $dto->teacher_id,
                    'type' => 'clock_out',
                    'latitude' => $dto->latitude,
                    'longitude' => $dto->longitude,
                    'accuracy' => $dto->accuracy,
                    'distance_meters' => $distance,
                    'method' => $dto->method,
                    'ip_address' => $dto->ip_address,
                    'user_agent' => $dto->user_agent,
                    'log_status' => 'accepted',
                    'reason' => null,
                    'unit_id' => $unit->id,
                ]);

                // Record Spatie activity
                activity()
                    ->performedOn($attendance)
                    ->log("Guru {$teacher->name} melakukan absen pulang dengan status {$statusPulang} pada {$timeString}");

                return [
                    'success' => true,
                    'message' => "Presensi pulang berhasil dicatat pada " . Carbon::parse($timeString)->format('H:i') . " WIB.",
                    'time' => Carbon::parse($timeString)->format('H:i'),
                ];
            }

            throw new \Exception("Aksi tidak didukung.");
        });
    }
}
