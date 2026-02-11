import { useMutation } from '@tanstack/react-query';
import { authApi } from '../api/auth';
import { useAuthStore } from '../store/authStore';
import { useNavigate } from 'react-router-dom';

export function useLogin() {
  const setAuth = useAuthStore((s) => s.setAuth);
  const navigate = useNavigate();

  return useMutation({
    mutationFn: async ({ username, password }: { username: string; password: string }) => {
      const loginData = await authApi.login(username, password);
      // Store tokens first so the /me request is authenticated
      setAuth(loginData.access_token, loginData.refresh_token ?? '', null);
      // Fetch full user profile
      const user = await authApi.me();
      return { ...loginData, user };
    },
    onSuccess: (data) => {
      setAuth(data.access_token, data.refresh_token ?? '', data.user);
      navigate('/dashboard');
    },
  });
}

export function useLogout() {
  const logout = useAuthStore((s) => s.logout);
  const navigate = useNavigate();

  return useMutation({
    mutationFn: () => authApi.logout(),
    onSuccess: () => {
      logout();
      navigate('/login');
    },
    onError: () => {
      logout();
      navigate('/login');
    },
  });
}
