<?php

namespace App\Repositories\Contracts;

use App\Models\Teacher;
use Illuminate\Pagination\LengthAwarePaginator;

interface TeacherRepositoryInterface
{
    /**
     * Get filtered and paginated active teachers.
     */
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Get filtered and paginated trashed (soft-deleted) teachers.
     */
    public function getTrashedPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Find teacher by ID.
     */
    public function findById(int $id): ?Teacher;

    /**
     * Find teacher including trashed by ID.
     */
    public function findWithTrashed(int $id): ?Teacher;

    /**
     * Create a new teacher record.
     */
    public function create(array $data): Teacher;

    /**
     * Update an existing teacher record.
     */
    public function update(int $id, array $data): Teacher;

    /**
     * Soft delete a teacher record.
     */
    public function delete(int $id): bool;

    /**
     * Restore a soft-deleted teacher record.
     */
    public function restore(int $id): bool;
}
