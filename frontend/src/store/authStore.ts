import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { User } from '../types';

interface AuthState {
  token: string | null;
  refreshToken: string | null;
  user: User | null;
  isAuthenticated: boolean;
  setAuth: (token: string, refreshToken: string, user: User | null) => void;
  setUser: (user: User) => void;
  logout: () => void;
  hasPermission: (permission: string) => boolean;
  hasRole: (roleName: string) => boolean;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      token: null,
      refreshToken: null,
      user: null,
      isAuthenticated: false,

      setAuth: (token, refreshToken, user) =>
        set({ token, refreshToken, user, isAuthenticated: true }),

      setUser: (user) => set({ user }),

      logout: () =>
        set({ token: null, refreshToken: null, user: null, isAuthenticated: false }),

      hasPermission: (permission: string) => {
        const { user } = get();
        return user?.permissions?.includes(permission) ?? false;
      },

      hasRole: (roleName: string) => {
        const { user } = get();
        return user?.roles?.some((r) => r.role_name === roleName) ?? false;
      },
    }),
    {
      name: 'ums-auth',
      partialize: (state) => ({
        token: state.token,
        refreshToken: state.refreshToken,
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);
