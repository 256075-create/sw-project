import { useState } from 'react';
import { Navigate } from 'react-router-dom';
import { useLogin } from '../hooks/useAuth';
import { useAuthStore } from '../store/authStore';
import ErrorAlert from '../components/common/ErrorAlert';

export default function LoginPage() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const isAuthenticated = useAuthStore((s) => s.isAuthenticated);
  const { mutate: login, isPending, error } = useLogin();

  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />;
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    login({ username, password });
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <div className="w-full max-w-md rounded-xl bg-white p-8 shadow-lg">
        <div className="mb-8 text-center">
          <h1 className="text-3xl font-bold text-blue-600">UMS</h1>
          <p className="mt-2 text-gray-600">University Management System</p>
        </div>

        {error && (
          <ErrorAlert
            message={
              (error as { response?: { data?: { error?: string } } })?.response?.data?.error ??
              'Login failed. Please try again.'
            }
          />
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="username" className="mb-1 block text-sm font-medium text-gray-700">
              Username
            </label>
            <input
              id="username"
              type="text"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              placeholder="Enter your username"
            />
          </div>
          <div>
            <label htmlFor="password" className="mb-1 block text-sm font-medium text-gray-700">
              Password
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              placeholder="Enter your password"
            />
          </div>
          <button
            type="submit"
            disabled={isPending}
            className="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {isPending ? 'Signing in...' : 'Sign In'}
          </button>
        </form>
      </div>
    </div>
  );
}
