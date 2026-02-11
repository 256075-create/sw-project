<?php

namespace App\Modules\Registration\Contracts;

use App\Modules\Registration\Models\Section;
use Illuminate\Pagination\LengthAwarePaginator;

interface ISectionService
{
    /**
     * Get a paginated list of sections with optional filters.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a section by its ID.
     */
    public function findById(int $sectionId): ?Section;

    /**
     * Get a section with its schedules loaded.
     */
    public function getSectionWithSchedules(int $sectionId): ?Section;

    /**
     * Create a new section.
     */
    public function create(array $data): Section;

    /**
     * Update an existing section.
     */
    public function update(int $sectionId, array $data): Section;

    /**
     * Delete a section.
     */
    public function delete(int $sectionId): bool;

    /**
     * Increment the current enrollment count for a section.
     */
    public function incrementEnrollment(int $sectionId): Section;

    /**
     * Decrement the current enrollment count for a section.
     */
    public function decrementEnrollment(int $sectionId): Section;
}
