import apiClient from './client';
import type { Student, Enrollment, Timetable } from '../types';

export const studentsApi = {
  list: async (params?: Record<string, string | number>) => {
    const response = await apiClient.get('/students', { params });
    return response.data;
  },

  show: async (id: number): Promise<Student> => {
    const response = await apiClient.get(`/students/${id}`);
    return response.data;
  },

  create: async (data: Partial<Student>): Promise<Student> => {
    const response = await apiClient.post('/students', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Student>): Promise<Student> => {
    const response = await apiClient.put(`/students/${id}`, data);
    return response.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/students/${id}`);
  },

  enrollments: async (studentId: number, params?: Record<string, string>): Promise<Enrollment[]> => {
    const response = await apiClient.get(`/students/${studentId}/enrollments`, { params });
    return response.data;
  },

  timetable: async (studentId: number): Promise<{ data: Timetable }> => {
    const response = await apiClient.get(`/students/${studentId}/timetable`);
    return response.data;
  },

  enroll: async (studentId: number, sectionId: number): Promise<Enrollment> => {
    const response = await apiClient.post('/enrollments', {
      student_id: studentId,
      section_id: sectionId,
    });
    return response.data;
  },

  dropEnrollment: async (enrollmentId: number): Promise<Enrollment> => {
    const response = await apiClient.post(`/enrollments/${enrollmentId}/drop`);
    return response.data;
  },

  profile: async (): Promise<Student> => {
    const response = await apiClient.get('/student/profile');
    return response.data;
  },
};
