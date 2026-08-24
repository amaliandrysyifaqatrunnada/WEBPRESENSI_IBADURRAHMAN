<?php

namespace App\DTOs;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;

class TeacherData
{
    public function __construct(
        public ?string $nip,
        public string $name,
        public string $email,
        public ?string $password,
        public string $position,
        public ?string $phone,
        public $avatar, // UploadedFile or string
        public string $status,
        public ?int $unit_id = null
    ) {}

    /**
     * Build TeacherData DTO from request.
     */
    public static function fromRequest(Request $request): self
    {
        // Enforce admin's unit_id to prevent any manipulation (Anti-IDOR)
        $unitId = auth()->check() ? auth()->user()->unit_id : null;
        if (!$unitId) {
            // Fallback for command line or seeder if any
            $unitId = $request->input('unit_id');
        }

        return new self(
            nip: $request->input('nip'),
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password') ? $request->input('password') : null,
            position: $request->input('position'),
            phone: $request->input('phone'),
            avatar: $request->file('avatar'),
            status: $request->input('status', 'active'),
            unit_id: $unitId ? (int) $unitId : null
        );
    }

    /**
     * Convert DTO values to an array suitable for Eloquent.
     */
    public function toArray(): array
    {
        $data = [
            'nip' => $this->nip,
            'name' => $this->name,
            'email' => $this->email,
            'position' => $this->position,
            'phone' => $this->phone,
            'status' => $this->status,
        ];

        if ($this->unit_id !== null) {
            $data['unit_id'] = $this->unit_id;
        }

        if ($this->password) {
            $data['password'] = bcrypt($this->password); // encrypt PIN
        }

        if (is_string($this->avatar)) {
            $data['avatar'] = $this->avatar;
        }

        return $data;
    }
}
