import axios from 'axios';
import { useAuthStore } from '../store/authStore';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

apiClient.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore.getState();
      if (authStore.refreshToken) {
        try {
          const response = await axios.post('/api/auth/refresh', {
            refresh_token: authStore.refreshToken,
          });
          authStore.setAuth(response.data.access_token, authStore.refreshToken, authStore.user);
          error.config.headers.Authorization = `Bearer ${response.data.access_token}`;
          return axios(error.config);
        } catch {
          authStore.logout();
          window.location.href = '/login';
        }
      } else {
        authStore.logout();
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default apiClient;
