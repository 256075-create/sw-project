<?php

namespace App\Modules\Academic\Services;

use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;
use App\Modules\Academic\Repositories\AcademicRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class MajorService
{
    public function __construct(
        protected AcademicRepository $repository
    ) {}

    public function create(array $data): Major
    {
        $this->validateDepartmentExists($data['department_id']);

        return Major::create($data);
    }

    public function update(int $majorId, array $data): Major
    {
        $major = $this->repository->findMajorById($majorId);

        if (!$major) {
            throw new \InvalidArgumentException('Major not found');
        }

        if (isset($data['department_id'])) {
            $this->validateDepartmentExists($data['department_id']);
        }

        $major->update($data);

        return $major->fresh();
    }

    public function delete(int $majorId): bool
    {
        $major = $this->repository->findMajorById($majorId);

        if (!$major) {
            throw new \InvalidArgumentException('Major not found');
        }

        return $major->delete();
    }

    public function findById(int $majorId): ?Major
    {
        return $this->repository->findMajorById($majorId);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->listMajors($filters, $perPage);
    }

    public function getByDepartment(int $departmentId): \Illuminate\Database\Eloquent\Collection
    {
        $this->validateDepartmentExists($departmentId);

        return Major::where('department_id', $departmentId)->get();
    }

    protected function validateDepartmentExists(int $departmentId): void
    {
        $department = Department::find($departmentId);

        if (!$department) {
            throw new \InvalidArgumentException('Department not found. Cannot assign major to a non-existent department.');
        }
    }
}
