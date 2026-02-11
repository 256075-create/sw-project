import { Navigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import type { ReactNode } from 'react';

interface ProtectedRouteProps {
  children: ReactNode;
  permission?: string;
}

export default function ProtectedRoute({ children, permission }: ProtectedRouteProps) {
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const hasPermission = useAuthStore((s) => s.hasPermission);

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (permission && !hasPermission(permission)) {
    return (
      <div className="flex h-full items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-800">Access Denied</h2>
          <p className="mt-2 text-gray-600">You do not have permission to view this page.</p>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}
