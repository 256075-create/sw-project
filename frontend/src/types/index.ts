export interface User {
  user_id: string;
  username: string;
  email: string;
  is_active: boolean;
  roles: Role[];
  permissions: string[];
}

export interface Role {
  role_id: number;
  role_name: string;
  description: string;
}

export interface AuthResponse {
  access_token: string;
  refresh_token: string;
  token_type: string;
  expires_in: number;
  user?: User;
}

export interface University {
  university_id: number;
  name: string;
  code: string;
  colleges?: College[];
}

export interface College {
  college_id: number;
  university_id: number;
  name: string;
  code: string;
  university?: University;
  departments?: Department[];
}

export interface Department {
  department_id: number;
  college_id: number;
  name: string;
  code: string;
  college?: College;
  majors?: Major[];
}

export interface Major {
  major_id: number;
  department_id: number;
  name: string;
  code: string;
  total_credits: number;
  department?: Department;
}

export interface Course {
  course_id: number;
  course_code: string;
  name: string;
  description: string;
  credit_hours: number;
  is_active: boolean;
}

export interface Section {
  section_id: number;
  course_id: number;
  classroom_id: number;
  section_number: string;
  instructor_name: string;
  max_capacity: number;
  current_enrollment: number;
  remaining_capacity: number;
  semester: string;
  academic_year: string;
  course?: Course;
  classroom?: Classroom;
  schedules?: Schedule[];
}

export interface Classroom {
  classroom_id: number;
  room_number: string;
  building: string;
  capacity: number;
}

export interface Schedule {
  schedule_id: number;
  section_id: number;
  day_of_week: string;
  start_time: string;
  end_time: string;
}

export interface Student {
  student_id: number;
  student_number: string;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  major_id: number;
  user_id: string | null;
  enrollment_date: string;
  status: string;
  major?: {
    major_id: number;
    name: string;
    code: string;
    department?: {
      department_id: number;
      name: string;
      college?: {
        college_id: number;
        name: string;
      };
    };
  };
}

export interface Enrollment {
  enrollment_id: number;
  student_id: number;
  section_id: number;
  enrollment_date: string;
  status: string;
  section?: {
    section_id: number;
    section_number: string;
    instructor_name: string;
    max_capacity: number;
    current_enrollment: number;
    semester: string;
    academic_year: string;
    course?: {
      course_id: number;
      course_code: string;
      name: string;
      credit_hours: number;
    };
    classroom?: {
      classroom_id: number;
      room_number: string;
      building: string;
    };
    schedules?: {
      day_of_week: string;
      start_time: string;
      end_time: string;
    }[];
  };
}

export interface TimetableSlot {
  enrollment_id: number;
  course_name: string;
  course_code: string;
  section_number: string;
  instructor_name: string;
  classroom: string;
  start_time: string;
  end_time: string;
}

export type Timetable = Record<string, TimetableSlot[]>;

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

export interface ApiError {
  error: string;
  message?: string;
}
