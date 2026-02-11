import apiClient from './client';
import type { User } from '../types';

export const usersApi = {
  list: async (params?: Record<string, string | number>) => {
    const response = await apiClient.get('/users', { params });
    return response.data;
  },

  show: async (id: string): Promise<User> => {
    const response = await apiClient.get(`/users/${id}`);
    return response.data;
  },

  create: async (data: Record<string, unknown>): Promise<User> => {
    const response = await apiClient.post('/users', data);
    return response.data;
  },

  update: async (id: string, data: Record<string, unknown>): Promise<User> => {
    const response = await apiClient.put(`/users/${id}`, data);
    return response.data;
  },

  delete: async (id: string): Promise<void> => {
    await apiClient.delete(`/users/${id}`);
  },

  activate: async (id: string): Promise<User> => {
    const response = await apiClient.post(`/users/${id}/activate`);
    return response.data;
  },

  deactivate: async (id: string): Promise<User> => {
    const response = await apiClient.post(`/users/${id}/deactivate`);
    return response.data;
  },
};
