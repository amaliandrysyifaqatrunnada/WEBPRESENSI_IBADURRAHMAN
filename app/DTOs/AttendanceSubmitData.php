<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AttendanceSubmitData
{
    public function __construct(
        public int $teacher_id,
        public ?string $action_type,
        public float $latitude,
        public float $longitude,
        public float $accuracy,
        public string $method,
        public ?string $qr_token,
        public ?string $status,
        public ?string $date,
        public ?string $ip_address,
        public ?string $user_agent
    ) {}

    /**
     * Build DTO from request.
     */
    public static function fromRequest(Request $request, int $teacherId): self
    {
        return new self(
            teacher_id: $teacherId,
            action_type: $request->input('action_type'),
            latitude: (float) $request->input('latitude', 0.0),
            longitude: (float) $request->input('longitude', 0.0),
            accuracy: (float) $request->input('accuracy', 0.0),
            method: $request->input('method', 'gps'),
            qr_token: $request->input('qr_token'),
            status: $request->input('status'),
            date: $request->input('date'),
            ip_address: $request->ip(),
            user_agent: $request->header('User-Agent')
        );
    }

    /**
     * Cast DTO to array.
     */
    public function toArray(): array
    {
        return [
            'teacher_id' => $this->teacher_id,
            'action_type' => $this->action_type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
            'method' => $this->method,
            'qr_token' => $this->qr_token,
            'status' => $this->status,
            'date' => $this->date,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
        ];
    }
}
