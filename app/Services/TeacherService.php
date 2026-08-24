<?php

namespace App\Services;

use App\DTOs\TeacherData;
use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TeacherService
{
    public function __construct(
        protected TeacherRepositoryInterface $teacherRepository
    ) {}

    /**
     * Get paginated active teachers with filters.
     */
    public function getTeachers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->teacherRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get paginated trashed teachers with filters.
     */
    public function getTrashedTeachers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->teacherRepository->getTrashedPaginated($filters, $perPage);
    }

    /**
     * Find a teacher by ID.
     */
    public function findTeacher(int $id): ?Teacher
    {
        return $this->teacherRepository->findById($id);
    }

    /**
     * Find a trashed teacher by ID.
     */
    public function findTrashedTeacher(int $id): ?Teacher
    {
        return $this->teacherRepository->findWithTrashed($id);
    }

    /**
     * Create a new teacher record.
     */
    public function createTeacher(TeacherData $dto): Teacher
    {
        $data = $dto->toArray();

        // Handle Avatar File Upload
        if ($dto->avatar && is_object($dto->avatar)) {
            $data['avatar'] = $this->uploadAndProcessAvatar($dto->avatar);
        }

        return $this->teacherRepository->create($data);
    }

    /**
     * Update an existing teacher record.
     */
    public function updateTeacher(int $id, TeacherData $dto): Teacher
    {
        $teacher = $this->teacherRepository->findById($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data pendidik ini.');
        }

        $data = $dto->toArray();

        // Handle Avatar Update
        if ($dto->avatar && is_object($dto->avatar)) {
            // Delete old avatar
            if ($teacher->avatar) {
                Storage::disk('public')->delete($teacher->avatar);
            }
            $data['avatar'] = $this->uploadAndProcessAvatar($dto->avatar);
        }

        return $this->teacherRepository->update($id, $data);
    }

    /**
     * Soft delete a teacher record.
     */
    public function deleteTeacher(int $id): bool
    {
        return $this->teacherRepository->delete($id);
    }

    /**
     * Restore a soft-deleted teacher record.
     */
    public function restoreTeacher(int $id): bool
    {
        return $this->teacherRepository->restore($id);
    }

    /**
     * Process and upload avatar using Intervention Image.
     */
    protected function uploadAndProcessAvatar($file): string
    {
        // Define directory path and filename
        $fileName = 'avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Ensure avatars folder exists
        Storage::disk('public')->makeDirectory('avatars');

        try {
            // Resize and compress avatar image using Intervention Image v3
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            
            // Resize image to 300x300 square crop
            $image->cover(300, 300);
            
            // Save compressed file to public storage disk
            $outputPath = Storage::disk('public')->path($fileName);
            $image->save($outputPath, 80); // quality 80%

            return $fileName;
        } catch (\Exception $e) {
            // Fallback to standard Laravel file upload if Intervention Image fails
            return $file->store('avatars', 'public');
        }
    }

    /**
     * Get all teachers without pagination (for exporting).
     */
    public function getAllTeachers(array $filters = [])
    {
        $query = Teacher::query();

        // Scope to current admin unit
        if (auth()->guard('web')->check()) {
            $unitId = auth()->guard('web')->user()->unit_id;
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'All Status') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['position']) && $filters['position'] !== 'All Positions') {
            $query->where('position', $filters['position']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Import teachers from Excel or CSV file.
     * Must be inside DB transaction to prevent partial inserts.
     */
    public function importTeachers($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            throw new \InvalidArgumentException('Format file tidak didukung. Harap unggah file Excel (.xlsx) atau CSV.');
        }

        try {
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $file);
        } catch (\Exception $e) {
            throw new \RuntimeException('Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($sheets) || empty($sheets[0])) {
            throw new \InvalidArgumentException('File kosong atau tidak dapat dibaca.');
        }

        $rows = $sheets[0];
        $headers = array_map('trim', array_map('strtolower', $rows[0]));

        $colNip = $this->findHeaderIndex($headers, ['nip', 'nomor induk pegawai']);
        $colName = $this->findHeaderIndex($headers, ['nama lengkap', 'nama', 'name', 'full name']);
        $colEmail = $this->findHeaderIndex($headers, ['email', 'surel']);
        $colPosition = $this->findHeaderIndex($headers, ['jabatan', 'posisi', 'position']);
        $colPhone = $this->findHeaderIndex($headers, ['telepon', 'phone', 'no telp', 'no hp', 'telepon/hp']);

        if ($colName === -1 || $colEmail === -1 || $colPosition === -1) {
            throw new \InvalidArgumentException('Header file tidak lengkap. File harus memiliki kolom: Nama Lengkap, Email, Jabatan (kolom NIP dan Telepon bersifat opsional).');
        }

        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $nipList = [];

        // Determine target unit_id from authenticated admin
        $unitId = auth()->guard('web')->check() ? auth()->guard('web')->user()->unit_id : null;
        if (!$unitId) {
            throw new \RuntimeException('Gagal mengimpor: Sesi unit admin tidak valid.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                if (empty(array_filter($row))) {
                    continue;
                }

                $nip = $colNip !== -1 && isset($row[$colNip]) ? trim($row[$colNip]) : null;
                $name = trim($row[$colName] ?? '');
                $email = trim($row[$colEmail] ?? '');
                $position = trim($row[$colPosition] ?? '');
                $phone = $colPhone !== -1 && isset($row[$colPhone]) ? trim($row[$colPhone]) : null;

                $rowNum = $i + 1;

                if (empty($name)) {
                    $errors[] = "Baris {$rowNum}: Nama Lengkap tidak boleh kosong.";
                    $failedCount++;
                    continue;
                }
                if (empty($email)) {
                    $errors[] = "Baris {$rowNum}: Email tidak boleh kosong.";
                    $failedCount++;
                    continue;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Baris {$rowNum}: Format email '{$email}' tidak valid.";
                    $failedCount++;
                    continue;
                }
                if (empty($position)) {
                    $errors[] = "Baris {$rowNum}: Jabatan tidak boleh kosong.";
                    $failedCount++;
                    continue;
                }

                if ($nip) {
                    if (in_array($nip, $nipList)) {
                        $errors[] = "Baris {$rowNum}: Duplikasi NIP '{$nip}' dalam file.";
                        $failedCount++;
                        continue;
                    }
                    $nipList[] = $nip;

                    $existsNip = Teacher::withTrashed()->where('nip', $nip)->exists();
                    if ($existsNip) {
                        $errors[] = "Baris {$rowNum}: NIP '{$nip}' sudah terdaftar di sistem.";
                        $failedCount++;
                        continue;
                    }
                }

                $existsEmail = Teacher::withTrashed()->where('email', $email)->exists();
                if ($existsEmail) {
                    $errors[] = "Baris {$rowNum}: Email '{$email}' sudah terdaftar di sistem.";
                    $failedCount++;
                    continue;
                }

                $teacher = new Teacher();
                $teacher->nip = $nip ?: null;
                $teacher->name = $name;
                $teacher->email = $email;
                $teacher->position = $position;
                $teacher->phone = $phone ?: null;
                $teacher->password = \Illuminate\Support\Facades\Hash::make('password');
                $teacher->status = 'active';
                $teacher->unit_id = $unitId; // Automatically force the admin's unit ID (Anti-IDOR)
                $teacher->save();

                $successCount++;
            }

            if (!empty($errors)) {
                \Illuminate\Support\Facades\DB::rollBack();
                return [
                    'success' => false,
                    'success_count' => 0,
                    'failed_count' => $failedCount,
                    'errors' => $errors
                ];
            }

            \Illuminate\Support\Facades\DB::commit();
            return [
                'success' => true,
                'success_count' => $successCount,
                'failed_count' => 0,
                'errors' => []
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw new \RuntimeException('Terjadi kegagalan saat menyimpan data: ' . $e->getMessage());
        }
    }

    protected function findHeaderIndex(array $headers, array $needles): int
    {
        foreach ($headers as $index => $header) {
            foreach ($needles as $needle) {
                if (str_contains($header, $needle)) {
                    return $index;
                }
            }
        }
        return -1;
    }
}
