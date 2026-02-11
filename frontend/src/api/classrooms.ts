import apiClient from './client';
import type { Classroom } from '../types';

export const classroomsApi = {
  list: async (params?: Record<string, string | number>) => {
    const response = await apiClient.get('/classrooms', { params });
    return response.data;
  },

  show: async (id: number): Promise<Classroom> => {
    const response = await apiClient.get(`/classrooms/${id}`);
    return response.data;
  },

  create: async (data: Partial<Classroom>): Promise<Classroom> => {
    const response = await apiClient.post('/classrooms', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Classroom>): Promise<Classroom> => {
    const response = await apiClient.put(`/classrooms/${id}`, data);
    return response.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/classrooms/${id}`);
  },
};
