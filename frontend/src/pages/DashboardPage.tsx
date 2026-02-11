import { useAuthStore } from '../store/authStore';

export default function DashboardPage() {
  const user = useAuthStore((s) => s.user);

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold text-gray-800">Dashboard</h1>
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <DashboardCard title="Welcome" value={user?.username ?? 'User'} color="blue" />
        <DashboardCard
          title="Role"
          value={user?.roles?.[0]?.role_name ?? 'N/A'}
          color="green"
        />
        <DashboardCard
          title="Permissions"
          value={String(user?.permissions?.length ?? 0)}
          color="purple"
        />
        <DashboardCard title="Status" value="Active" color="emerald" />
      </div>
    </div>
  );
}

function DashboardCard({
  title,
  value,
  color,
}: {
  title: string;
  value: string;
  color: string;
}) {
  const colorClasses: Record<string, string> = {
    blue: 'bg-blue-50 text-blue-700',
    green: 'bg-green-50 text-green-700',
    purple: 'bg-purple-50 text-purple-700',
    emerald: 'bg-emerald-50 text-emerald-700',
  };

  return (
    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <p className="text-sm text-gray-500">{title}</p>
      <p className={`mt-2 text-xl font-semibold ${colorClasses[color] ?? 'text-gray-800'}`}>
        {value}
      </p>
    </div>
  );
}
