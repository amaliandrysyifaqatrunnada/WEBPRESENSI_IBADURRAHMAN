<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TeacherRepository implements TeacherRepositoryInterface
{
    /**
     * Helper to apply unit scope based on authenticated admin.
     */
    protected function applyUnitScope($query)
    {
        if (auth()->guard('web')->check()) {
            $unitId = auth()->guard('web')->user()->unit_id;
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
        }
        return $query;
    }

    /**
     * Get filtered and paginated active teachers.
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Teacher::query();
        $this->applyUnitScope($query);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'All Status') {
            $status = strtolower($filters['status']) === 'active' ? 'active' : 'inactive';
            $query->where('status', $status);
        }

        if (!empty($filters['position']) && $filters['position'] !== 'All Positions') {
            $query->where('position', $filters['position']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get filtered and paginated trashed (soft-deleted) teachers.
     */
    public function getTrashedPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Teacher::onlyTrashed();
        $this->applyUnitScope($query);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Find teacher by ID.
     */
    public function findById(int $id): ?Teacher
    {
        $query = Teacher::query();
        $this->applyUnitScope($query);
        return $query->find($id);
    }

    /**
     * Find teacher including trashed by ID.
     */
    public function findWithTrashed(int $id): ?Teacher
    {
        $query = Teacher::withTrashed();
        $this->applyUnitScope($query);
        return $query->find($id);
    }

    /**
     * Create a new teacher record.
     */
    public function create(array $data): Teacher
    {
        // Enforce unit_id from admin if not set
        if (auth()->guard('web')->check() && !isset($data['unit_id'])) {
            $data['unit_id'] = auth()->guard('web')->user()->unit_id;
        }
        return Teacher::create($data);
    }

    /**
     * Update an existing teacher record.
     */
    public function update(int $id, array $data): Teacher
    {
        // findById will naturally enforce unit scoping (Anti-IDOR)
        $teacher = $this->findById($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data pendidik ini.');
        }
        
        $teacher->update($data);
        return $teacher;
    }

    /**
     * Soft delete a teacher record.
     */
    public function delete(int $id): bool
    {
        $teacher = $this->findById($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus data pendidik ini.');
        }
        return $teacher->delete();
    }

    /**
     * Restore a soft-deleted teacher record.
     */
    public function restore(int $id): bool
    {
        $teacher = $this->findWithTrashed($id);
        if (!$teacher) {
            abort(403, 'Anda tidak memiliki hak akses untuk memulihkan data pendidik ini.');
        }
        return $teacher->restore();
    }
}
