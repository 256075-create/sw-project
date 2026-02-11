<?php

namespace App\Modules\Academic\Contracts;

use App\Modules\Academic\Models\University;
use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface IAcademicStructureService
{
    // University CRUD
    public function createUniversity(array $data): University;
    public function updateUniversity(int $universityId, array $data): University;
    public function deleteUniversity(int $universityId): bool;
    public function findUniversityById(int $universityId): ?University;
    public function listUniversities(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // College CRUD
    public function createCollege(array $data): College;
    public function updateCollege(int $collegeId, array $data): College;
    public function deleteCollege(int $collegeId): bool;
    public function findCollegeById(int $collegeId): ?College;
    public function listColleges(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // Department CRUD
    public function createDepartment(array $data): Department;
    public function updateDepartment(int $departmentId, array $data): Department;
    public function deleteDepartment(int $departmentId): bool;
    public function findDepartmentById(int $departmentId): ?Department;
    public function listDepartments(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // Major CRUD
    public function createMajor(array $data): Major;
    public function updateMajor(int $majorId, array $data): Major;
    public function deleteMajor(int $majorId): bool;
    public function findMajorById(int $majorId): ?Major;
    public function listMajors(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    // Hierarchy
    public function getHierarchy(): Collection;
}
