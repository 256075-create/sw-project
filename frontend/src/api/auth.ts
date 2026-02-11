import apiClient from './client';
import type { AuthResponse, User } from '../types';

export const authApi = {
  login: async (username: string, password: string): Promise<AuthResponse> => {
    const response = await apiClient.post('/auth/login', { username, password });
    return response.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/auth/logout');
  },

  refresh: async (refreshToken: string): Promise<{ access_token: string }> => {
    const response = await apiClient.post('/auth/refresh', { refresh_token: refreshToken });
    return response.data;
  },

  me: async (): Promise<User> => {
    const response = await apiClient.get('/me');
    return response.data;
  },
};
