<?php

namespace App\Modules\Registration\Services;

use App\Modules\Registration\Contracts\ISectionService;
use App\Modules\Registration\Models\Section;
use Illuminate\Pagination\LengthAwarePaginator;

class SectionService implements ISectionService
{
    /**
     * Get a paginated list of sections with optional filters.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Section::with(['course', 'classroom', 'schedules']);

        if (!empty($filters['course_id'])) {
            $query->where('course_id', (int) $filters['course_id']);
        }

        if (!empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        if (!empty($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (!empty($filters['instructor_name'])) {
            $query->where('instructor_name', 'like', "%{$filters['instructor_name']}%");
        }

        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', (int) $filters['classroom_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('department_id', (int) $filters['department_id']);
            });
        }

        if (isset($filters['has_availability']) && $filters['has_availability']) {
            $query->whereColumn('current_enrollment', '<', 'max_capacity');
        }

        $sortBy = $filters['sort_by'] ?? 'section_id';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Find a section by its ID.
     */
    public function findById(int $sectionId): ?Section
    {
        return Section::with(['course', 'classroom'])->find($sectionId);
    }

    /**
     * Get a section with its schedules loaded.
     */
    public function getSectionWithSchedules(int $sectionId): ?Section
    {
        return Section::with(['course', 'classroom', 'schedules'])->find($sectionId);
    }

    /**
     * Create a new section.
     */
    public function create(array $data): Section
    {
        $section = Section::create($data);

        return $section->load(['course', 'classroom']);
    }

    /**
     * Update an existing section.
     */
    public function update(int $sectionId, array $data): Section
    {
        $section = Section::findOrFail($sectionId);

        // Validate capacity change does not go below current enrollment
        if (isset($data['max_capacity']) && $data['max_capacity'] < $section->current_enrollment) {
            throw new \InvalidArgumentException(
                "Cannot set max capacity to {$data['max_capacity']} because current enrollment is {$section->current_enrollment}."
            );
        }

        $section->update($data);

        return $section->fresh(['course', 'classroom', 'schedules']);
    }

    /**
     * Delete a section.
     */
    public function delete(int $sectionId): bool
    {
        $section = Section::findOrFail($sectionId);

        if ($section->current_enrollment > 0) {
            throw new \InvalidArgumentException(
                "Cannot delete section '{$section->section_number}' because it has {$section->current_enrollment} enrolled student(s). " .
                "Remove all enrollments before deleting this section."
            );
        }

        return (bool) $section->delete();
    }

    /**
     * Increment the current enrollment count for a section.
     */
    public function incrementEnrollment(int $sectionId): Section
    {
        $section = Section::findOrFail($sectionId);

        if (!$section->hasAvailableCapacity()) {
            throw new \InvalidArgumentException(
                "Section '{$section->section_number}' has reached its maximum capacity of {$section->max_capacity}."
            );
        }

        $section->increment('current_enrollment');

        return $section->fresh();
    }

    /**
     * Decrement the current enrollment count for a section.
     */
    public function decrementEnrollment(int $sectionId): Section
    {
        $section = Section::findOrFail($sectionId);

        if ($section->current_enrollment <= 0) {
            throw new \InvalidArgumentException(
                "Section '{$section->section_number}' has no enrolled students to remove."
            );
        }

        $section->decrement('current_enrollment');

        return $section->fresh();
    }
}
