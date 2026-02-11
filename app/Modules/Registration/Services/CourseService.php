<?php

namespace App\Modules\Registration\Services;

use App\Modules\Registration\Contracts\ICourseService;
use App\Modules\Registration\Models\Course;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService implements ICourseService
{
    /**
     * Get a paginated list of courses with optional filters.
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Course::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('course_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['course_code'])) {
            $query->where('course_code', $filters['course_code']);
        }

        $sortBy = $filters['sort_by'] ?? 'course_code';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Find a course by its ID.
     */
    public function findById(int $courseId): ?Course
    {
        return Course::find($courseId);
    }

    /**
     * Find a course by its course code.
     */
    public function findByCode(string $courseCode): ?Course
    {
        return Course::where('course_code', $courseCode)->first();
    }

    /**
     * Create a new course.
     */
    public function create(array $data): Course
    {
        return Course::create($data);
    }

    /**
     * Update an existing course.
     */
    public function update(int $courseId, array $data): Course
    {
        $course = Course::findOrFail($courseId);
        $course->update($data);

        return $course->fresh();
    }

    /**
     * Delete a course (only if no active sections exist).
     */
    public function delete(int $courseId): bool
    {
        $course = Course::findOrFail($courseId);

        // Check if there are active sections referencing this course
        $activeSections = $course->sections()->count();

        if ($activeSections > 0) {
            throw new \InvalidArgumentException(
                "Cannot delete course '{$course->course_code}' because it has {$activeSections} associated section(s). " .
                "Remove all sections before deleting this course."
            );
        }

        return (bool) $course->delete();
    }

    /**
     * Activate a course.
     */
    public function activate(int $courseId): Course
    {
        $course = Course::findOrFail($courseId);
        $course->update(['is_active' => true]);

        return $course->fresh();
    }

    /**
     * Deactivate a course.
     */
    public function deactivate(int $courseId): Course
    {
        $course = Course::findOrFail($courseId);
        $course->update(['is_active' => false]);

        return $course->fresh();
    }
}
