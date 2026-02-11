import apiClient from './client';
import type { University, College, Department, Major } from '../types';

export const academicApi = {
  hierarchy: async (): Promise<University[]> => {
    const response = await apiClient.get('/academic/hierarchy');
    return response.data;
  },

  universities: {
    list: async (params?: Record<string, string | number>) => {
      const response = await apiClient.get('/universities', { params });
      return response.data;
    },
    show: async (id: number): Promise<University> => {
      const response = await apiClient.get(`/universities/${id}`);
      return response.data;
    },
    create: async (data: Partial<University>): Promise<University> => {
      const response = await apiClient.post('/universities', data);
      return response.data;
    },
    update: async (id: number, data: Partial<University>): Promise<University> => {
      const response = await apiClient.put(`/universities/${id}`, data);
      return response.data;
    },
    delete: async (id: number): Promise<void> => {
      await apiClient.delete(`/universities/${id}`);
    },
  },

  colleges: {
    list: async (params?: Record<string, string | number>) => {
      const response = await apiClient.get('/colleges', { params });
      return response.data;
    },
    create: async (data: Partial<College>): Promise<College> => {
      const response = await apiClient.post('/colleges', data);
      return response.data;
    },
    update: async (id: number, data: Partial<College>): Promise<College> => {
      const response = await apiClient.put(`/colleges/${id}`, data);
      return response.data;
    },
    delete: async (id: number): Promise<void> => {
      await apiClient.delete(`/colleges/${id}`);
    },
  },

  departments: {
    list: async (params?: Record<string, string | number>) => {
      const response = await apiClient.get('/departments', { params });
      return response.data;
    },
    create: async (data: Partial<Department>): Promise<Department> => {
      const response = await apiClient.post('/departments', data);
      return response.data;
    },
    update: async (id: number, data: Partial<Department>): Promise<Department> => {
      const response = await apiClient.put(`/departments/${id}`, data);
      return response.data;
    },
    delete: async (id: number): Promise<void> => {
      await apiClient.delete(`/departments/${id}`);
    },
  },

  majors: {
    list: async (params?: Record<string, string | number>) => {
      const response = await apiClient.get('/majors', { params });
      return response.data;
    },
    create: async (data: Partial<Major>): Promise<Major> => {
      const response = await apiClient.post('/majors', data);
      return response.data;
    },
    update: async (id: number, data: Partial<Major>): Promise<Major> => {
      const response = await apiClient.put(`/majors/${id}`, data);
      return response.data;
    },
    delete: async (id: number): Promise<void> => {
      await apiClient.delete(`/majors/${id}`);
    },
  },
};
