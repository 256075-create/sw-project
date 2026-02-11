<?php

namespace App\Modules\Student\Services;

use App\Modules\Student\Contracts\IStudentService;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Repositories\StudentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentService implements IStudentService
{
    public function __construct(
        protected StudentRepository $studentRepository
    ) {}

    public function create(array $data): Student
    {
        if (empty($data['student_number'])) {
            $data['student_number'] = $this->generateStudentNumber();
        }

        if (empty($data['enrollment_date'])) {
            $data['enrollment_date'] = now();
        }

        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->studentRepository->create($data);
    }

    public function update(int $studentId, array $data): Student
    {
        return $this->studentRepository->update($studentId, $data);
    }

    public function findById(int $studentId): ?Student
    {
        return $this->studentRepository->findById($studentId);
    }

    public function findByStudentNumber(string $studentNumber): ?Student
    {
        return $this->studentRepository->findByStudentNumber($studentNumber);
    }

    public function findByUserId(string $userId): ?Student
    {
        return $this->studentRepository->findByUserId($userId);
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentRepository->list($filters, $perPage);
    }

    public function delete(int $studentId): bool
    {
        return $this->studentRepository->delete($studentId);
    }

    protected function generateStudentNumber(): string
    {
        $year = date('Y');
        $lastStudent = Student::where('student_number', 'like', "STU-{$year}-%")
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNum = (int) substr($lastStudent->student_number, -4);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf("STU-%s-%04d", $year, $nextNum);
    }
}
