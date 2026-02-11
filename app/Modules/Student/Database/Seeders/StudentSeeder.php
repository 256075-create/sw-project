<?php

namespace App\Modules\Student\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Student\Models\Student;
use App\Modules\Academic\Models\Major;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $majors = Major::all();

        if ($majors->isEmpty()) {
            return;
        }

        $students = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@university.edu'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane.smith@university.edu'],
            ['first_name' => 'Ahmed', 'last_name' => 'Ali', 'email' => 'ahmed.ali@university.edu'],
            ['first_name' => 'Sarah', 'last_name' => 'Johnson', 'email' => 'sarah.johnson@university.edu'],
            ['first_name' => 'Omar', 'last_name' => 'Hassan', 'email' => 'omar.hassan@university.edu'],
        ];

        $year = date('Y');
        $counter = 1;

        foreach ($students as $data) {
            Student::create([
                'major_id' => $majors->random()->major_id,
                'student_number' => sprintf("STU-%s-%04d", $year, $counter++),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }
    }
}
