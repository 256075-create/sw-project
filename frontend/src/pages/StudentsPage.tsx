import { useState, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { studentsApi } from '../api/students';
import { academicApi } from '../api/academic';
import DataTable from '../components/common/DataTable';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { Student, Major } from '../types';

interface StudentFormData {
  first_name: string;
  last_name: string;
  email: string;
  major_id: number | '';
  enrollment_date: string;
}

const emptyForm: StudentFormData = {
  first_name: '',
  last_name: '',
  email: '',
  major_id: '',
  enrollment_date: '',
};

export default function StudentsPage() {
  const queryClient = useQueryClient();

  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingStudent, setEditingStudent] = useState<Student | null>(null);
  const [formData, setFormData] = useState<StudentFormData>(emptyForm);
  const [mutationError, setMutationError] = useState<string | null>(null);

  const { data, isLoading, error } = useQuery({
    queryKey: ['students', page, search],
    queryFn: () => studentsApi.list({ page, per_page: 15, search }),
  });

  const { data: majorsData } = useQuery({
    queryKey: ['majors'],
    queryFn: () => academicApi.majors.list({ per_page: 100 }),
  });

  const majors: Major[] = majorsData?.data ?? majorsData ?? [];

  const createMutation = useMutation({
    mutationFn: (data: Partial<Student>) => studentsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['students'] });
      closeModal();
    },
    onError: (err: Error) => {
      setMutationError(err.message || 'Failed to create student');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Student> }) =>
      studentsApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['students'] });
      closeModal();
    },
    onError: (err: Error) => {
      setMutationError(err.message || 'Failed to update student');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => studentsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['students'] });
    },
    onError: (err: Error) => {
      setMutationError(err.message || 'Failed to delete student');
    },
  });

  function openAddModal() {
    setEditingStudent(null);
    setFormData(emptyForm);
    setMutationError(null);
    setIsModalOpen(true);
  }

  function openEditModal(student: Student) {
    setEditingStudent(student);
    setFormData({
      first_name: student.first_name,
      last_name: student.last_name,
      email: student.email,
      major_id: student.major_id,
      enrollment_date: student.enrollment_date,
    });
    setMutationError(null);
    setIsModalOpen(true);
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingStudent(null);
    setFormData(emptyForm);
    setMutationError(null);
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setMutationError(null);

    const payload: Partial<Student> = {
      first_name: formData.first_name,
      last_name: formData.last_name,
      email: formData.email,
      major_id: Number(formData.major_id),
      enrollment_date: formData.enrollment_date,
    };

    if (editingStudent) {
      updateMutation.mutate({ id: editingStudent.student_id, data: payload });
    } else {
      createMutation.mutate(payload);
    }
  }

  function handleDelete(student: Student) {
    if (
      window.confirm(
        `Are you sure you want to delete student "${student.first_name} ${student.last_name}"?`,
      )
    ) {
      setMutationError(null);
      deleteMutation.mutate(student.student_id);
    }
  }

  const isSaving = createMutation.isPending || updateMutation.isPending;

  const columns = [
    { key: 'student_number', header: 'Student #' },
    {
      key: 'full_name',
      header: 'Name',
      render: (s: Student) => `${s.first_name} ${s.last_name}`,
    },
    { key: 'email', header: 'Email' },
    {
      key: 'major',
      header: 'Major',
      render: (s: Student) => s.major?.name ?? 'N/A',
    },
    {
      key: 'status',
      header: 'Status',
      render: (s: Student) => (
        <span
          className={`rounded-full px-2 py-0.5 text-xs font-medium ${
            s.status === 'active'
              ? 'bg-green-100 text-green-700'
              : s.status === 'graduated'
                ? 'bg-blue-100 text-blue-700'
                : 'bg-gray-100 text-gray-700'
          }`}
        >
          {s.status}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (s: Student) => (
        <div className="flex items-center gap-2">
          <button
            onClick={(e) => {
              e.stopPropagation();
              openEditModal(s);
            }}
            className="rounded bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-100"
          >
            Edit
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation();
              handleDelete(s);
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

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-800">Students</h1>
        <div className="flex items-center gap-3">
          <input
            type="text"
            placeholder="Search students..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            className="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          />
          <button
            onClick={openAddModal}
            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            Add Student
          </button>
        </div>
      </div>

      {error && <ErrorAlert message="Failed to load students" />}
      {mutationError && !isModalOpen && (
        <div className="mb-4">
          <ErrorAlert message={mutationError} />
        </div>
      )}
      {isLoading ? (
        <LoadingSpinner />
      ) : (
        <>
          <DataTable columns={columns} data={data?.data ?? []} keyField="student_id" />
          {data?.meta && (
            <Pagination
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              onPageChange={setPage}
            />
          )}
        </>
      )}

      <Modal
        isOpen={isModalOpen}
        onClose={closeModal}
        title={editingStudent ? 'Edit Student' : 'Add Student'}
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          {mutationError && (
            <ErrorAlert message={mutationError} />
          )}

          <div>
            <label htmlFor="first_name" className="mb-1 block text-sm font-medium text-gray-700">
              First Name
            </label>
            <input
              id="first_name"
              type="text"
              required
              value={formData.first_name}
              onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
          </div>

          <div>
            <label htmlFor="last_name" className="mb-1 block text-sm font-medium text-gray-700">
              Last Name
            </label>
            <input
              id="last_name"
              type="text"
              required
              value={formData.last_name}
              onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
          </div>

          <div>
            <label htmlFor="email" className="mb-1 block text-sm font-medium text-gray-700">
              Email
            </label>
            <input
              id="email"
              type="email"
              required
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
          </div>

          <div>
            <label htmlFor="major_id" className="mb-1 block text-sm font-medium text-gray-700">
              Major
            </label>
            <select
              id="major_id"
              required
              value={formData.major_id}
              onChange={(e) => setFormData({ ...formData, major_id: Number(e.target.value) })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            >
              <option value="">Select a major</option>
              {majors.map((major) => (
                <option key={major.major_id} value={major.major_id}>
                  {major.name} ({major.code})
                </option>
              ))}
            </select>
          </div>

          <div>
            <label
              htmlFor="enrollment_date"
              className="mb-1 block text-sm font-medium text-gray-700"
            >
              Enrollment Date
            </label>
            <input
              id="enrollment_date"
              type="date"
              required
              value={formData.enrollment_date}
              onChange={(e) => setFormData({ ...formData, enrollment_date: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={closeModal}
              className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSaving}
              className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {isSaving ? 'Saving...' : editingStudent ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
