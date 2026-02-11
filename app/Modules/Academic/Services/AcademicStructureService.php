<?php

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Contracts\IAcademicStructureService;
use App\Modules\Academic\Models\University;
use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;
use App\Modules\Academic\Repositories\AcademicRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AcademicStructureService implements IAcademicStructureService
{
    public function __construct(
        protected AcademicRepository $repository
    ) {}

    // ─── University ──────────────────────────────────────────────────────

    public function createUniversity(array $data): University
    {
        return University::create($data);
    }

    public function updateUniversity(int $universityId, array $data): University
    {
        $university = $this->findUniversityById($universityId);

        if (!$university) {
            throw new \InvalidArgumentException('University not found');
        }

        $university->update($data);

        return $university->fresh();
    }

    public function deleteUniversity(int $universityId): bool
    {
        $university = $this->findUniversityById($universityId);

        if (!$university) {
            throw new \InvalidArgumentException('University not found');
        }

        return $university->delete();
    }

    public function findUniversityById(int $universityId): ?University
    {
        return $this->repository->findUniversityById($universityId);
    }

    public function listUniversities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->listUniversities($filters, $perPage);
    }

    // ─── College ─────────────────────────────────────────────────────────

    public function createCollege(array $data): College
    {
        return College::create($data);
    }

    public function updateCollege(int $collegeId, array $data): College
    {
        $college = $this->findCollegeById($collegeId);

        if (!$college) {
            throw new \InvalidArgumentException('College not found');
        }

        $college->update($data);

        return $college->fresh();
    }

    public function deleteCollege(int $collegeId): bool
    {
        $college = $this->findCollegeById($collegeId);

        if (!$college) {
            throw new \InvalidArgumentException('College not found');
        }

        return $college->delete();
    }

    public function findCollegeById(int $collegeId): ?College
    {
        return $this->repository->findCollegeById($collegeId);
    }

    public function listColleges(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->listColleges($filters, $perPage);
    }

    // ─── Department ──────────────────────────────────────────────────────

    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    public function updateDepartment(int $departmentId, array $data): Department
    {
        $department = $this->findDepartmentById($departmentId);

        if (!$department) {
            throw new \InvalidArgumentException('Department not found');
        }

        $department->update($data);

        return $department->fresh();
    }

    public function deleteDepartment(int $departmentId): bool
    {
        $department = $this->findDepartmentById($departmentId);

        if (!$department) {
            throw new \InvalidArgumentException('Department not found');
        }

        return $department->delete();
    }

    public function findDepartmentById(int $departmentId): ?Department
    {
        return $this->repository->findDepartmentById($departmentId);
    }

    public function listDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->listDepartments($filters, $perPage);
    }

    // ─── Major ───────────────────────────────────────────────────────────

    public function createMajor(array $data): Major
    {
        $department = Department::find($data['department_id']);

        if (!$department) {
            throw new \InvalidArgumentException('Department not found');
        }

        return Major::create($data);
    }

    public function updateMajor(int $majorId, array $data): Major
    {
        $major = $this->findMajorById($majorId);

        if (!$major) {
            throw new \InvalidArgumentException('Major not found');
        }

        if (isset($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if (!$department) {
                throw new \InvalidArgumentException('Department not found');
            }
        }

        $major->update($data);

        return $major->fresh();
    }

    public function deleteMajor(int $majorId): bool
    {
        $major = $this->findMajorById($majorId);

        if (!$major) {
            throw new \InvalidArgumentException('Major not found');
        }

        return $major->delete();
    }

    public function findMajorById(int $majorId): ?Major
    {
        return $this->repository->findMajorById($majorId);
    }

    public function listMajors(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->listMajors($filters, $perPage);
    }

    // ─── Hierarchy ───────────────────────────────────────────────────────

    public function getHierarchy(): Collection
    {
        return $this->repository->getFullHierarchy();
    }
}
