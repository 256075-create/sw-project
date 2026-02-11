import { useState, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { classroomsApi } from '../api/classrooms';
import DataTable from '../components/common/DataTable';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { Classroom } from '../types';

interface ClassroomFormData {
  room_number: string;
  building: string;
  capacity: number;
}

const emptyForm: ClassroomFormData = {
  room_number: '',
  building: '',
  capacity: 30,
};

export default function ClassroomsPage() {
  const [page, setPage] = useState(1);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingClassroom, setEditingClassroom] = useState<Classroom | null>(null);
  const [formData, setFormData] = useState<ClassroomFormData>(emptyForm);

  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['classrooms', page],
    queryFn: () => classroomsApi.list({ page, per_page: 15 }),
  });

  const createMutation = useMutation({
    mutationFn: (data: ClassroomFormData) => classroomsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['classrooms'] });
      closeModal();
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: ClassroomFormData }) =>
      classroomsApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['classrooms'] });
      closeModal();
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => classroomsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['classrooms'] });
    },
  });

  function openCreateModal() {
    setEditingClassroom(null);
    setFormData(emptyForm);
    setIsModalOpen(true);
  }

  function openEditModal(classroom: Classroom) {
    setEditingClassroom(classroom);
    setFormData({
      room_number: classroom.room_number,
      building: classroom.building,
      capacity: classroom.capacity,
    });
    setIsModalOpen(true);
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingClassroom(null);
    setFormData(emptyForm);
  }

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (editingClassroom) {
      updateMutation.mutate({ id: editingClassroom.classroom_id, data: formData });
    } else {
      createMutation.mutate(formData);
    }
  }

  function handleDelete(classroom: Classroom) {
    if (window.confirm(`Are you sure you want to delete room "${classroom.room_number}" in ${classroom.building}?`)) {
      deleteMutation.mutate(classroom.classroom_id);
    }
  }

  const isSaving = createMutation.isPending || updateMutation.isPending;

  const columns = [
    { key: 'room_number', header: 'Room Number' },
    { key: 'building', header: 'Building' },
    { key: 'capacity', header: 'Capacity' },
    {
      key: 'capacity_badge',
      header: 'Size',
      render: (c: Classroom) => (
        <span
          className={`rounded-full px-2 py-0.5 text-xs font-medium ${
            c.capacity >= 100
              ? 'bg-purple-100 text-purple-700'
              : c.capacity >= 50
                ? 'bg-blue-100 text-blue-700'
                : 'bg-gray-100 text-gray-700'
          }`}
        >
          {c.capacity >= 100 ? 'Large' : c.capacity >= 50 ? 'Medium' : 'Small'}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (c: Classroom) => (
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
        <h1 className="text-2xl font-bold text-gray-800">Classrooms</h1>
        <button
          onClick={openCreateModal}
          className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
        >
          Add Classroom
        </button>
      </div>

      {error && <ErrorAlert message="Failed to load classrooms" />}
      {(createMutation.isError || updateMutation.isError || deleteMutation.isError) && (
        <ErrorAlert message="An error occurred. Please try again." />
      )}
      {isLoading ? (
        <LoadingSpinner />
      ) : (
        <>
          <DataTable columns={columns} data={data?.data ?? []} keyField="classroom_id" />
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
        title={editingClassroom ? 'Edit Classroom' : 'Add Classroom'}
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="room_number" className="mb-1 block text-sm font-medium text-gray-700">
              Room Number
            </label>
            <input
              id="room_number"
              type="text"
              required
              value={formData.room_number}
              onChange={(e) => setFormData({ ...formData, room_number: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="e.g. 101"
            />
          </div>

          <div>
            <label htmlFor="building" className="mb-1 block text-sm font-medium text-gray-700">
              Building
            </label>
            <input
              id="building"
              type="text"
              required
              value={formData.building}
              onChange={(e) => setFormData({ ...formData, building: e.target.value })}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="e.g. Science Hall"
            />
          </div>

          <div>
            <label htmlFor="capacity" className="mb-1 block text-sm font-medium text-gray-700">
              Capacity
            </label>
            <input
              id="capacity"
              type="number"
              required
              min={1}
              value={formData.capacity}
              onChange={(e) =>
                setFormData({ ...formData, capacity: parseInt(e.target.value, 10) || 1 })
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
              {isSaving ? 'Saving...' : editingClassroom ? 'Update' : 'Create'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
