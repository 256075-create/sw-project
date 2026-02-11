import apiClient from './client';
import type { Course, Section } from '../types';

export const coursesApi = {
  list: async (params?: Record<string, string | number>) => {
    const response = await apiClient.get('/courses', { params });
    return response.data;
  },

  show: async (id: number): Promise<Course> => {
    const response = await apiClient.get(`/courses/${id}`);
    return response.data;
  },

  create: async (data: Partial<Course>): Promise<Course> => {
    const response = await apiClient.post('/courses', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Course>): Promise<Course> => {
    const response = await apiClient.put(`/courses/${id}`, data);
    return response.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/courses/${id}`);
  },
};

export const sectionsApi = {
  list: async (params?: Record<string, string | number>) => {
    const response = await apiClient.get('/sections', { params });
    return response.data;
  },

  show: async (id: number): Promise<Section> => {
    const response = await apiClient.get(`/sections/${id}`);
    return response.data;
  },

  create: async (data: Partial<Section>): Promise<Section> => {
    const response = await apiClient.post('/sections', data);
    return response.data;
  },

  update: async (id: number, data: Partial<Section>): Promise<Section> => {
    const response = await apiClient.put(`/sections/${id}`, data);
    return response.data;
  },

  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/sections/${id}`);
  },
};
