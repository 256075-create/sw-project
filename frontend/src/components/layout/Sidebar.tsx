import { NavLink } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';

const navItems = [
  { path: '/dashboard', label: 'Dashboard', permission: null },
  { path: '/students', label: 'Students', permission: 'students.read' },
  { path: '/courses', label: 'Courses', permission: 'courses.read' },
  { path: '/sections', label: 'Sections', permission: 'sections.read' },
  { path: '/enrollments', label: 'Enrollments', permission: 'enrollments.read' },
  { path: '/academic', label: 'Academic Structure', permission: 'academic.read' },
  { path: '/classrooms', label: 'Classrooms', permission: 'classrooms.read' },
  { path: '/users', label: 'Users', permission: 'users.read' },
];

export default function Sidebar() {
  const user = useAuthStore((s) => s.user);
  const permissions = user?.permissions ?? [];

  return (
    <aside className="flex h-full w-64 flex-col border-r border-gray-200 bg-white">
      <div className="border-b border-gray-200 p-4">
        <h1 className="text-xl font-bold text-blue-600">UMS</h1>
        <p className="text-xs text-gray-500">University Management System</p>
      </div>
      <nav className="flex-1 overflow-y-auto p-4">
        <ul className="space-y-1">
          {navItems
            .filter((item) => !item.permission || permissions.includes(item.permission))
            .map((item) => (
              <li key={item.path}>
                <NavLink
                  to={item.path}
                  className={({ isActive }) =>
                    `block rounded-lg px-4 py-2 text-sm ${
                      isActive
                        ? 'bg-blue-50 font-medium text-blue-700'
                        : 'text-gray-700 hover:bg-gray-100'
                    }`
                  }
                >
                  {item.label}
                </NavLink>
              </li>
            ))}
        </ul>
      </nav>
    </aside>
  );
}
