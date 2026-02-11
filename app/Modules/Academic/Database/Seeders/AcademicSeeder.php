<?php

namespace App\Modules\Academic\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Academic\Models\University;
use App\Modules\Academic\Models\College;
use App\Modules\Academic\Models\Department;
use App\Modules\Academic\Models\Major;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $university = University::create([
            'name' => 'Default University',
            'code' => 'DFU',
        ]);

        $engineeringCollege = College::create([
            'university_id' => $university->university_id,
            'name' => 'College of Engineering',
            'code' => 'ENG',
        ]);

        $scienceCollege = College::create([
            'university_id' => $university->university_id,
            'name' => 'College of Science',
            'code' => 'SCI',
        ]);

        $csDept = Department::create([
            'college_id' => $engineeringCollege->college_id,
            'name' => 'Computer Science',
            'code' => 'CS',
        ]);

        $eeDept = Department::create([
            'college_id' => $engineeringCollege->college_id,
            'name' => 'Electrical Engineering',
            'code' => 'EE',
        ]);

        $mathDept = Department::create([
            'college_id' => $scienceCollege->college_id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);

        Major::create([
            'department_id' => $csDept->department_id,
            'name' => 'Software Engineering',
            'code' => 'SE',
            'total_credits' => 136,
        ]);

        Major::create([
            'department_id' => $csDept->department_id,
            'name' => 'Data Science',
            'code' => 'DS',
            'total_credits' => 132,
        ]);

        Major::create([
            'department_id' => $eeDept->department_id,
            'name' => 'Power Systems',
            'code' => 'PS',
            'total_credits' => 140,
        ]);

        Major::create([
            'department_id' => $mathDept->department_id,
            'name' => 'Applied Mathematics',
            'code' => 'AM',
            'total_credits' => 128,
        ]);
    }
}
