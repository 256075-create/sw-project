import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { sectionsApi, coursesApi } from '../api/courses';
import { classroomsApi } from '../api/classrooms';
import { studentsApi } from '../api/students';
import { useAuthStore } from '../store/authStore';
import DataTable from '../components/common/DataTable';
import Pagination from '../components/common/Pagination';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { Section, Course, Classroom } from '../types';

interface SectionFormData {
  section_number: string;
  course_id: number | '';
  classroom_id: number | '';
  instructor_name: string;
  max_capacity: number | '';
  semester: string;
  academic_year: string;
}

const initialFormData: SectionFormData = {
  section_number: '',
  course_id: '',
  classroom_id: '',
  instructor_name: '',
  max_capacity: '',
  semester: '',
  academic_year: '',
};

const SEMESTERS = ['Fall', 'Spring', 'Summer'];

export default function SectionsPage() {
  const [page, setPage] = useState(1);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingSection, setEditingSection] = useState<Section | null>(null);
  const [formData, setFormData] = useState<SectionFormData>(initialFormData);
  const [formError, setFormError] = useState('');

  const user = useAuthStore((s) => s.user);
  const isStudent = user?.roles?.some((r) => r.role_name === 'Student') ?? false;

  const queryClient = useQueryClient();

  // For students: fetch profile to get department_id
  const { data: studentProfile } = useQuery({
    queryKey: ['student-profile'],
    queryFn: () => studentsApi.profile(),
    enabled: isStudent,
  });

  const departmentId = isStudent ? studentProfile?.major?.department?.department_id : undefined;

  const { data, isLoading, error } = useQuery({
    queryKey: ['sections', page, departmentId],
    queryFn: () =>
      sectionsApi.list({
        page,
        per_page: 15,
        ...(departmentId ? { department_id: departmentId } : {}),
      }),
    enabled: !isStudent || !!departmentId,
  });

  const { data: coursesData } = useQuery({
    queryKey: ['courses-dropdown'],
    queryFn: () => coursesApi.list({ per_page: 1000 }),
    enabled: isModalOpen && !isStudent,
  });

  const { data: classroomsData } = useQuery({
    queryKey: ['classrooms-dropdown'],
    queryFn: () => classroomsApi.list({ per_page: 1000 }),
    enabled: isModalOpen && !isStudent,
  });

  const courses: Course[] = coursesData?.data ?? [];
  const classrooms: Classroom[] = classroomsData?.data ?? [];

  const createMutation = useMutation({
    mutationFn: (data: Partial<Section>) => sectionsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sections'] });
      closeModal();
    },
    onError: () => {
      setFormError('Failed to create section. Please check your input and try again.');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<Section> }) =>
      sectionsApi.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sections'] });
      closeModal();
    },
    onError: () => {
      setFormError('Failed to update section. Please check your input and try again.');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => sectionsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sections'] });
    },
  });

  function openAddModal() {
    setEditingSection(null);
    setFormData(initialFormData);
    setFormError('');
    setIsModalOpen(true);
  }

  function openEditModal(section: Section) {
    setEditingSection(section);
    setFormData({
      section_number: section.section_number,
      course_id: section.course_id,
      classroom_id: section.classroom_id,
      instructor_name: section.instructor_name,
      max_capacity: section.max_capacity,
      semester: section.semester,
      academic_year: section.academic_year,
    });
    setFormError('');
    setIsModalOpen(true);
  }

  function closeModal() {
    setIsModalOpen(false);
    setEditingSection(null);
    setFormData(initialFormData);
    setFormError('');
  }

  function handleDelete(section: Section) {
    if (window.confirm(`Are you sure you want to delete section "${section.section_number}"?`)) {
      deleteMutation.mutate(section.section_id);
    }
  }

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setFormError('');

    if (
      !formData.section_number.trim() ||
      formData.course_id === '' ||
      formData.classroom_id === '' ||
      !formData.instructor_name.trim() ||
      formData.max_capacity === '' ||
      !formData.semester ||
      !formData.academic_year.trim()
    ) {
      setFormError('All fields are required.');
      return;
    }

    const payload: Partial<Section> = {
      section_number: formData.section_number.trim(),
      course_id: Number(formData.course_id),
      classroom_id: Number(formData.classroom_id),
      instructor_name: formData.instructor_name.trim(),
      max_capacity: Number(formData.max_capacity),
      semester: formData.semester,
      academic_year: formData.academic_year.trim(),
    };

    if (editingSection) {
      updateMutation.mutate({ id: editingSection.section_id, data: payload });
    } else {
      createMutation.mutate(payload);
    }
  }

  const isSaving = createMutation.isPending || updateMutation.isPending;

  const columns = [
    { key: 'section_number', header: 'Section #' },
    {
      key: 'course',
      header: 'Course',
      render: (s: Section) =>
        s.course ? `${s.course.course_code} - ${s.course.name}` : 'N/A',
    },
    { key: 'instructor_name', header: 'Instructor' },
    {
      key: 'enrollment',
      header: 'Enrollment',
      render: (s: Section) => `${s.current_enrollment}/${s.max_capacity}`,
    },
    { key: 'semester', header: 'Semester' },
    { key: 'academic_year', header: 'Year' },
    ...(!isStudent
      ? [
          {
            key: 'actions',
            header: 'Actions',
            render: (s: Section) => (
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
        ]
      : []),
  ];

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Sections</h1>
          {isStudent && studentProfile?.major && (
            <p className="mt-1 text-sm text-gray-500">
              Showing sections for {studentProfile.major.department?.name ?? 'your department'}
            </p>
          )}
        </div>
        {!isStudent && (
          <button
            onClick={openAddModal}
            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Add Section
          </button>
        )}
      </div>

      {error && <ErrorAlert message="Failed to load sections" />}
      {isLoading || (isStudent && !departmentId) ? (
        <LoadingSpinner />
      ) : (
        <>
          <DataTable columns={columns} data={data?.data ?? []} keyField="section_id" />
          {data?.meta && (
            <Pagination
              currentPage={data.meta.current_page}
              lastPage={data.meta.last_page}
              onPageChange={setPage}
            />
          )}
        </>
      )}

      {!isStudent && (
        <Modal
          isOpen={isModalOpen}
          onClose={closeModal}
          title={editingSection ? 'Edit Section' : 'Add Section'}
        >
          <form onSubmit={handleSubmit} className="space-y-4">
            {formError && (
              <div className="rounded-lg bg-red-50 p-3 text-sm text-red-600">{formError}</div>
            )}

            <div>
              <label htmlFor="section_number" className="mb-1 block text-sm font-medium text-gray-700">
                Section Number
              </label>
              <input
                id="section_number"
                type="text"
                value={formData.section_number}
                onChange={(e) => setFormData({ ...formData, section_number: e.target.value })}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="e.g. 001"
              />
            </div>

            <div>
              <label htmlFor="course_id" className="mb-1 block text-sm font-medium text-gray-700">
                Course
              </label>
              <select
                id="course_id"
                value={formData.course_id}
                onChange={(e) =>
                  setFormData({ ...formData, course_id: e.target.value ? Number(e.target.value) : '' })
                }
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Select a course</option>
                {courses.map((c) => (
                  <option key={c.course_id} value={c.course_id}>
                    {c.course_code} - {c.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label htmlFor="classroom_id" className="mb-1 block text-sm font-medium text-gray-700">
                Classroom
              </label>
              <select
                id="classroom_id"
                value={formData.classroom_id}
                onChange={(e) =>
                  setFormData({
                    ...formData,
                    classroom_id: e.target.value ? Number(e.target.value) : '',
                  })
                }
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Select a classroom</option>
                {classrooms.map((c) => (
                  <option key={c.classroom_id} value={c.classroom_id}>
                    {c.building} - {c.room_number} (Capacity: {c.capacity})
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label htmlFor="instructor_name" className="mb-1 block text-sm font-medium text-gray-700">
                Instructor Name
              </label>
              <input
                id="instructor_name"
                type="text"
                value={formData.instructor_name}
                onChange={(e) => setFormData({ ...formData, instructor_name: e.target.value })}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="e.g. Dr. Smith"
              />
            </div>

            <div>
              <label htmlFor="max_capacity" className="mb-1 block text-sm font-medium text-gray-700">
                Max Capacity
              </label>
              <input
                id="max_capacity"
                type="number"
                min={1}
                value={formData.max_capacity}
                onChange={(e) =>
                  setFormData({
                    ...formData,
                    max_capacity: e.target.value ? Number(e.target.value) : '',
                  })
                }
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="e.g. 30"
              />
            </div>

            <div>
              <label htmlFor="semester" className="mb-1 block text-sm font-medium text-gray-700">
                Semester
              </label>
              <select
                id="semester"
                value={formData.semester}
                onChange={(e) => setFormData({ ...formData, semester: e.target.value })}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="">Select a semester</option>
                {SEMESTERS.map((s) => (
                  <option key={s} value={s}>
                    {s}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label htmlFor="academic_year" className="mb-1 block text-sm font-medium text-gray-700">
                Academic Year
              </label>
              <input
                id="academic_year"
                type="text"
                value={formData.academic_year}
                onChange={(e) => setFormData({ ...formData, academic_year: e.target.value })}
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="e.g. 2025-2026"
              />
            </div>

            <div className="flex justify-end gap-3 pt-2">
              <button
                type="button"
                onClick={closeModal}
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={isSaving}
                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
              >
                {isSaving ? 'Saving...' : editingSection ? 'Update' : 'Create'}
              </button>
            </div>
          </form>
        </Modal>
      )}
    </div>
  );
}
