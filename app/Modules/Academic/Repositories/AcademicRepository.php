<?php

namespace App\Modules\Academic\Repositories;

use App\Modules\Academic\Models\University;
use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AcademicRepository
{
    // ─── University Queries ──────────────────────────────────────────────

    public function findUniversityById(int $universityId): ?University
    {
        return University::with('colleges')->find($universityId);
    }

    public function listUniversities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = University::query();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function getAllUniversities(): Collection
    {
        return University::orderBy('name', 'asc')->get();
    }

    // ─── College Queries ─────────────────────────────────────────────────

    public function findCollegeById(int $collegeId): ?College
    {
        return College::with(['university', 'departments'])->find($collegeId);
    }

    public function listColleges(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = College::with('university');

        if (isset($filters['university_id'])) {
            $query->where('university_id', $filters['university_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function getCollegesByUniversity(int $universityId): Collection
    {
        return College::where('university_id', $universityId)
            ->orderBy('name', 'asc')
            ->get();
    }

    // ─── Department Queries ──────────────────────────────────────────────

    public function findDepartmentById(int $departmentId): ?Department
    {
        return Department::with(['college', 'majors'])->find($departmentId);
    }

    public function listDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::with('college');

        if (isset($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function getDepartmentsByCollege(int $collegeId): Collection
    {
        return Department::where('college_id', $collegeId)
            ->orderBy('name', 'asc')
            ->get();
    }

    // ─── Major Queries ───────────────────────────────────────────────────

    public function findMajorById(int $majorId): ?Major
    {
        return Major::with('department')->find($majorId);
    }

    public function listMajors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Major::with('department');

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function getMajorsByDepartment(int $departmentId): Collection
    {
        return Major::where('department_id', $departmentId)
            ->orderBy('name', 'asc')
            ->get();
    }

    // ─── Hierarchy ───────────────────────────────────────────────────────

    public function getFullHierarchy(): Collection
    {
        return University::with([
            'colleges' => function ($query) {
                $query->orderBy('name', 'asc');
            },
            'colleges.departments' => function ($query) {
                $query->orderBy('name', 'asc');
            },
            'colleges.departments.majors' => function ($query) {
                $query->orderBy('name', 'asc');
            },
        ])->orderBy('name', 'asc')->get();
    }
}
