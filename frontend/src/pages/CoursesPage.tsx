import { useState, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { coursesApi } from '../api/courses';
import DataTable from '../components/common/DataTable';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { Course } from '../types';

interface CourseFormData {
  course_code: string;
  name: string;
  description: string;
  credit_hours: number;
}

const emptyForm: CourseFormData = {
  course_code: '',
  name: '',
  description: '',
  credit_hours: 3,
};

export default function CoursesPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingCourse, setEditingCourse] = useState<Course | null>(null);
  const [formData, setFormData] = useState<CourseFormData>(emptyForm);

  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['courses', page, search],
    queryFn: () => coursesApi.list({ page, per_page: 15, search }),
  });

  const createMutation = useMutation({
    mutationFn: (data: CourseFormData) => coursesApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      closeModal();
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: CourseFormData }) =>
      coursesApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      closeModal();
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => coursesApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
    },
  });

  function openCreateModal() {
    setEditingCourse(null);
    setFormData(emptyForm);
    setIsModalOpen(true);
  }

  function openEditModal(course: Course) {
    setEditingCourse(course);
    setFormData({
      course_code: course.course_code,
      name: course.name,
      description: course.description,
      credit_hours: course.credit_hours,
    });
    setIsModalOpen(true);
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingCourse(null);
    setFormData(emptyForm);
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (editingCourse) {
      updateMutation.mutate({ id: editingCourse.course_id, data: formData });
    } else {
      createMutation.mutate(formData);
    }
  }

  function handleDelete(course: Course) {
    if (window.confirm(`Are you sure you want to delete "${course.name}"?`)) {
      deleteMutation.mutate(course.course_id);
    }
  }

  const isSaving = createMutation.isPending || updateMutation.isPending;

  const columns = [
    { key: 'course_code', header: 'Code' },
    { key: 'name', header: 'Name' },
    { key: 'credit_hours', header: 'Credits' },
    {
      key: 'is_active',
      header: 'Status',
      render: (c: Course) => (
        <span
          className={`rounded-full px-2 py-0.5 text-xs font-medium ${
            c.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }`}
        >
          {c.is_active ? 'Active' : 'Inactive'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (c: Course) => (
        <div className="flex items-center gap-2">
          <button
            onClick={(e) => {
              e.stopPropagation();
              openEditModal(c);
            }}
            className="rounded bg-blue-50 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-100"
          >
            Edit
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation();
              handleDelete(c);
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
        <h1 className="text-2xl font-bold text-gray-800">Courses</h1>
        <div className="flex items-center gap-3">
          <input
            type="text"
            placeholder="Search courses..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
            className="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          />
          <button
            onClick={openCreateModal}
            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            Add Course
          </button>
        </div>
      </div>

      {error && <ErrorAlert message="Failed to load courses" />}
      {(createMutation.isError || updateMutation.isError || deleteMutation.isError) && (
        <ErrorAlert message="An error occurred. Please try again." />
      )}
      {isLoading ? (
        <LoadingSpinner />
      ) : (
        <>
          <DataTable columns={columns} data={data?.data ?? []} keyField="course_id" />
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
        title={editingCourse ? 'Edit Course' : 'Add Course'}
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="course_code" className="mb-1 block text-sm font-medium text-gray-700">
              Course Code
            </label>
            <input
              id="course_code"
              type="text"
              required
              value={formData.course_code}
              onChange={(e) => setFormData({ ...formData, course_code: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="e.g. CS101"
            />
          </div>

          <div>
            <label htmlFor="name" className="mb-1 block text-sm font-medium text-gray-700">
              Name
            </label>
            <input
              id="name"
              type="text"
              required
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="e.g. Introduction to Computer Science"
            />
          </div>

          <div>
            <label htmlFor="description" className="mb-1 block text-sm font-medium text-gray-700">
              Description
            </label>
            <textarea
              id="description"
              rows={3}
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="Course description..."
            />
          </div>

          <div>
            <label htmlFor="credit_hours" className="mb-1 block text-sm font-medium text-gray-700">
              Credit Hours
            </label>
            <input
              id="credit_hours"
              type="number"
              required
              min={1}
              max={12}
              value={formData.credit_hours}
              onChange={(e) =>
                setFormData({ ...formData, credit_hours: parseInt(e.target.value, 10) || 1 })
              }
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
              {isSaving ? 'Saving...' : editingCourse ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
