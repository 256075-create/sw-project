<?php

namespace App\Modules\Registration\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Registration\Models\Course;
use App\Modules\Registration\Models\Classroom;
use App\Modules\Registration\Models\Section;
use App\Modules\Registration\Models\Schedule;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        // Create classrooms
        $classrooms = [];
        $rooms = [
            ['room_number' => '101', 'building' => 'Engineering Hall', 'capacity' => 40],
            ['room_number' => '202', 'building' => 'Engineering Hall', 'capacity' => 60],
            ['room_number' => '301', 'building' => 'Science Center', 'capacity' => 30],
            ['room_number' => '105', 'building' => 'Main Building', 'capacity' => 100],
            ['room_number' => '210', 'building' => 'Main Building', 'capacity' => 50],
        ];

        foreach ($rooms as $room) {
            $classrooms[] = Classroom::create($room);
        }

        // Create courses
        $courses = [
            ['course_code' => 'CS101', 'name' => 'Introduction to Programming', 'credit_hours' => 3, 'is_active' => true],
            ['course_code' => 'CS201', 'name' => 'Data Structures', 'credit_hours' => 3, 'is_active' => true],
            ['course_code' => 'CS301', 'name' => 'Database Systems', 'credit_hours' => 3, 'is_active' => true],
            ['course_code' => 'MATH101', 'name' => 'Calculus I', 'credit_hours' => 4, 'is_active' => true],
            ['course_code' => 'MATH201', 'name' => 'Linear Algebra', 'credit_hours' => 3, 'is_active' => true],
            ['course_code' => 'ENG101', 'name' => 'English Composition', 'credit_hours' => 3, 'is_active' => true],
        ];

        $createdCourses = [];
        foreach ($courses as $course) {
            $createdCourses[] = Course::create($course);
        }

        // Create sections for the first semester
        $sections = [
            ['course_id' => $createdCourses[0]->course_id, 'classroom_id' => $classrooms[0]->classroom_id, 'section_number' => 'SEC-001', 'instructor_name' => 'Dr. Smith', 'max_capacity' => 40, 'current_enrollment' => 0, 'semester' => 'Fall', 'academic_year' => '2024-2025'],
            ['course_id' => $createdCourses[1]->course_id, 'classroom_id' => $classrooms[1]->classroom_id, 'section_number' => 'SEC-002', 'instructor_name' => 'Dr. Johnson', 'max_capacity' => 35, 'current_enrollment' => 0, 'semester' => 'Fall', 'academic_year' => '2024-2025'],
            ['course_id' => $createdCourses[2]->course_id, 'classroom_id' => $classrooms[2]->classroom_id, 'section_number' => 'SEC-003', 'instructor_name' => 'Dr. Williams', 'max_capacity' => 30, 'current_enrollment' => 0, 'semester' => 'Fall', 'academic_year' => '2024-2025'],
            ['course_id' => $createdCourses[3]->course_id, 'classroom_id' => $classrooms[3]->classroom_id, 'section_number' => 'SEC-004', 'instructor_name' => 'Dr. Brown', 'max_capacity' => 50, 'current_enrollment' => 0, 'semester' => 'Fall', 'academic_year' => '2024-2025'],
        ];

        $createdSections = [];
        foreach ($sections as $section) {
            $createdSections[] = Section::create($section);
        }

        // Create schedules
        $schedules = [
            ['section_id' => $createdSections[0]->section_id, 'day_of_week' => 'Monday', 'start_time' => '09:00', 'end_time' => '10:30'],
            ['section_id' => $createdSections[0]->section_id, 'day_of_week' => 'Wednesday', 'start_time' => '09:00', 'end_time' => '10:30'],
            ['section_id' => $createdSections[1]->section_id, 'day_of_week' => 'Tuesday', 'start_time' => '11:00', 'end_time' => '12:30'],
            ['section_id' => $createdSections[1]->section_id, 'day_of_week' => 'Thursday', 'start_time' => '11:00', 'end_time' => '12:30'],
            ['section_id' => $createdSections[2]->section_id, 'day_of_week' => 'Monday', 'start_time' => '14:00', 'end_time' => '15:30'],
            ['section_id' => $createdSections[2]->section_id, 'day_of_week' => 'Wednesday', 'start_time' => '14:00', 'end_time' => '15:30'],
            ['section_id' => $createdSections[3]->section_id, 'day_of_week' => 'Tuesday', 'start_time' => '09:00', 'end_time' => '11:00'],
            ['section_id' => $createdSections[3]->section_id, 'day_of_week' => 'Thursday', 'start_time' => '09:00', 'end_time' => '11:00'],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
