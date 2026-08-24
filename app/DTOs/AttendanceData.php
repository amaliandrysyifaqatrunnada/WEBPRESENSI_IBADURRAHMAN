<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AttendanceData
{
    public function __construct(
        public int $teacher_id,
        public float $latitude,
        public float $longitude,
        public float $accuracy,
        public string $method,
        public ?string $qr_token,
        public ?string $ip_address,
        public ?string $user_agent
    ) {}

    /**
     * Build AttendanceData DTO from request.
     */
    public static function fromRequest(Request $request, int $teacherId): self
    {
        return new self(
            teacher_id: $teacherId,
            latitude: (float) $request->input('latitude', 0.0),
            longitude: (float) $request->input('longitude', 0.0),
            accuracy: (float) $request->input('accuracy', 0.0),
            method: $request->input('method', 'gps'),
            qr_token: $request->input('qr_token'),
            ip_address: $request->ip(),
            user_agent: $request->header('User-Agent')
        );
    }
}
