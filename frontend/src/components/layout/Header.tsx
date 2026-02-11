import { useAuthStore } from '../../store/authStore';
import { useLogout } from '../../hooks/useAuth';

export default function Header() {
  const user = useAuthStore((s) => s.user);
  const { mutate: logout } = useLogout();

  return (
    <header className="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6">
      <div>
        <h2 className="text-lg font-semibold text-gray-800">University Management System</h2>
      </div>
      <div className="flex items-center gap-4">
        <span className="text-sm text-gray-600">
          {user?.username ?? 'Guest'}
          {user?.roles?.[0] && (
            <span className="ml-2 rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">
              {user.roles[0].role_name}
            </span>
          )}
        </span>
        <button
          onClick={() => logout()}
          className="rounded-lg bg-red-50 px-3 py-1.5 text-sm text-red-600 hover:bg-red-100"
        >
          Logout
        </button>
      </div>
    </header>
  );
}
