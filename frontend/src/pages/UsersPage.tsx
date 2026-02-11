import { useState, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { usersApi } from '../api/users';
import DataTable from '../components/common/DataTable';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { User } from '../types';

interface UserFormData {
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
}

const emptyFormData: UserFormData = {
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
};

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [editingUser, setEditingUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<UserFormData>(emptyFormData);
  const [formError, setFormError] = useState<string | null>(null);

  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['users', page],
    queryFn: () => usersApi.list({ page, per_page: 15 }),
  });

  const createMutation = useMutation({
    mutationFn: (data: Record<string, unknown>) => usersApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      closeAddModal();
    },
    onError: () => {
      setFormError('Failed to create user. Please check the form and try again.');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: string; data: Record<string, unknown> }) =>
      usersApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      closeEditModal();
    },
    onError: () => {
      setFormError('Failed to update user. Please check the form and try again.');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => usersApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
  });

  function openAddModal() {
    setFormData(emptyFormData);
    setFormError(null);
    setIsAddModalOpen(true);
  }

  function closeAddModal() {
    setIsAddModalOpen(false);
    setFormData(emptyFormData);
    setFormError(null);
  }

  function openEditModal(user: User) {
    setEditingUser(user);
    setFormData({
      username: user.username,
      email: user.email,
      password: '',
      password_confirmation: '',
    });
    setFormError(null);
    setIsEditModalOpen(true);
  }

  function closeEditModal() {
    setIsEditModalOpen(false);
    setEditingUser(null);
    setFormData(emptyFormData);
    setFormError(null);
  }

  function handleAddSubmit(e: FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (!formData.username.trim() || !formData.email.trim()) {
      setFormError('Username and email are required.');
      return;
    }
    if (!formData.password) {
      setFormError('Password is required.');
      return;
    }
    if (formData.password !== formData.password_confirmation) {
      setFormError('Passwords do not match.');
      return;
    }

    createMutation.mutate({
      username: formData.username.trim(),
      email: formData.email.trim(),
      password: formData.password,
      password_confirmation: formData.password_confirmation,
    });
  }

  function handleEditSubmit(e: FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (!editingUser) return;

    if (!formData.username.trim() || !formData.email.trim()) {
      setFormError('Username and email are required.');
      return;
    }

    updateMutation.mutate({
      id: editingUser.user_id,
      data: {
        username: formData.username.trim(),
        email: formData.email.trim(),
      },
    });
  }

  function handleDelete(user: User) {
    if (window.confirm(`Are you sure you want to delete user "${user.username}"? This action cannot be undone.`)) {
      deleteMutation.mutate(user.user_id);
    }
  }

  const columns = [
    { key: 'username', header: 'Username' },
    { key: 'email', header: 'Email' },
    {
      key: 'roles_display',
      header: 'Roles',
      render: (u: User) =>
        u.roles?.map((r) => (
          <span
            key={r.role_id}
            className="mr-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700"
          >
            {r.role_name}
          </span>
        )) ?? 'None',
    },
    {
      key: 'is_active',
      header: 'Status',
      render: (u: User) => (
        <span
          className={`rounded-full px-2 py-0.5 text-xs font-medium ${
            u.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }`}
        >
          {u.is_active ? 'Active' : 'Inactive'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (u: User) => (
        <div className="flex items-center gap-2">
          <button
            onClick={(e) => {
              e.stopPropagation();
              openEditModal(u);
            }}
            className="rounded bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-100"
          >
            Edit
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation();
              handleDelete(u);
            }}
            disabled={deleteMutation.isPending}
            className="rounded bg-red-50 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-100 disabled:opacity-50"
          >
            Delete
          </button>
        </div>
      ),
    },
  ];

  const inputClassName =
    'block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
  const labelClassName = 'mb-1 block text-sm font-medium text-gray-700';

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-800">Users</h1>
        <button
          onClick={openAddModal}
          className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Add User
        </button>
      </div>

      {error && <ErrorAlert message="Failed to load users" />}
      {isLoading ? (
        <LoadingSpinner />
      ) : (
        <>
          <DataTable columns={columns} data={data?.data ?? []} keyField="user_id" />
          {data?.meta && (
            <Pagination
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              onPageChange={setPage}
            />
          )}
        </>
      )}

      {/* Add User Modal */}
      <Modal isOpen={isAddModalOpen} onClose={closeAddModal} title="Add User">
        <form onSubmit={handleAddSubmit} className="space-y-4">
          {formError && (
            <div className="rounded-lg bg-red-50 p-3 text-sm text-red-700">{formError}</div>
          )}
          <div>
            <label htmlFor="add-username" className={labelClassName}>
              Username
            </label>
            <input
              id="add-username"
              type="text"
              required
              value={formData.username}
              onChange={(e) => setFormData((prev) => ({ ...prev, username: e.target.value }))}
              className={inputClassName}
              placeholder="Enter username"
            />
          </div>
          <div>
            <label htmlFor="add-email" className={labelClassName}>
              Email
            </label>
            <input
              id="add-email"
              type="email"
              required
              value={formData.email}
              onChange={(e) => setFormData((prev) => ({ ...prev, email: e.target.value }))}
              className={inputClassName}
              placeholder="Enter email address"
            />
          </div>
          <div>
            <label htmlFor="add-password" className={labelClassName}>
              Password
            </label>
            <input
              id="add-password"
              type="password"
              required
              value={formData.password}
              onChange={(e) => setFormData((prev) => ({ ...prev, password: e.target.value }))}
              className={inputClassName}
              placeholder="Enter password"
            />
          </div>
          <div>
            <label htmlFor="add-password-confirm" className={labelClassName}>
              Confirm Password
            </label>
            <input
              id="add-password-confirm"
              type="password"
              required
              value={formData.password_confirmation}
              onChange={(e) =>
                setFormData((prev) => ({ ...prev, password_confirmation: e.target.value }))
              }
              className={inputClassName}
              placeholder="Confirm password"
            />
          </div>
          <div className="flex items-center justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={closeAddModal}
              className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={createMutation.isPending}
              className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              {createMutation.isPending ? 'Creating...' : 'Create User'}
            </button>
          </div>
        </form>
      </Modal>

      {/* Edit User Modal */}
      <Modal isOpen={isEditModalOpen} onClose={closeEditModal} title="Edit User">
        <form onSubmit={handleEditSubmit} className="space-y-4">
          {formError && (
            <div className="rounded-lg bg-red-50 p-3 text-sm text-red-700">{formError}</div>
          )}
          <div>
            <label htmlFor="edit-username" className={labelClassName}>
              Username
            </label>
            <input
              id="edit-username"
              type="text"
              required
              value={formData.username}
              onChange={(e) => setFormData((prev) => ({ ...prev, username: e.target.value }))}
              className={inputClassName}
              placeholder="Enter username"
            />
          </div>
          <div>
            <label htmlFor="edit-email" className={labelClassName}>
              Email
            </label>
            <input
              id="edit-email"
              type="email"
              required
              value={formData.email}
              onChange={(e) => setFormData((prev) => ({ ...prev, email: e.target.value }))}
              className={inputClassName}
              placeholder="Enter email address"
            />
          </div>
          <div className="flex items-center justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={closeEditModal}
              className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={updateMutation.isPending}
              className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              {updateMutation.isPending ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
