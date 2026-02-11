import { useState, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { studentsApi } from '../api/students';
import { sectionsApi } from '../api/courses';
import DataTable from '../components/common/DataTable';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import { useAuthStore } from '../store/authStore';
import type { Enrollment, Section } from '../types';
import type { AxiosError } from 'axios';

function getErrorMessage(err: unknown): string {
  const axiosErr = err as AxiosError<{ message?: string; error?: string }>;
  return (
    axiosErr.response?.data?.message ||
    axiosErr.response?.data?.error ||
    (err instanceof Error ? err.message : 'An unexpected error occurred')
  );
}

export default function EnrollmentsPage() {
  const user = useAuthStore((s) => s.user);
  const queryClient = useQueryClient();

  const [studentId, setStudentId] = useState('');
  const [searchId, setSearchId] = useState('');
  const [isEnrollModalOpen, setIsEnrollModalOpen] = useState(false);
  const [enrollStudentId, setEnrollStudentId] = useState('');
  const [enrollSectionId, setEnrollSectionId] = useState('');
  const [enrollError, setEnrollError] = useState<string | null>(null);
  const [dropError, setDropError] = useState<string | null>(null);

  const hasStudentRole = user?.roles?.some((r) => r.role_name === 'Student') ?? false;

  // For students: fetch their student profile to get the numeric student_id
  const { data: studentProfile } = useQuery({
    queryKey: ['student-profile'],
    queryFn: () => studentsApi.profile(),
    enabled: hasStudentRole,
  });

  const myStudentId = hasStudentRole ? studentProfile?.student_id : null;
  const effectiveId = hasStudentRole ? myStudentId : searchId ? Number(searchId) : null;

  const { data, isLoading, error } = useQuery({
    queryKey: ['enrollments', effectiveId],
    queryFn: () => studentsApi.enrollments(Number(effectiveId)),
    enabled: !!effectiveId,
  });

  const { data: sectionsData } = useQuery({
    queryKey: ['sections', 'all'],
    queryFn: () => sectionsApi.list({ per_page: 100 }),
    enabled: isEnrollModalOpen,
  });

  const sections: Section[] = sectionsData?.data ?? [];

  const enrollMutation = useMutation({
    mutationFn: ({ sid, secId }: { sid: number; secId: number }) =>
      studentsApi.enroll(sid, secId),
    onSuccess: (_data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['enrollments'] });
      queryClient.invalidateQueries({ queryKey: ['sections'] });
      closeEnrollModal();
      const enrolledStudentId = String(variables.sid);
      if (!hasStudentRole) {
        setStudentId(enrolledStudentId);
        setSearchId(enrolledStudentId);
      }
    },
    onError: (err: unknown) => {
      setEnrollError(getErrorMessage(err));
    },
  });

  const dropMutation = useMutation({
    mutationFn: (enrollmentId: number) => studentsApi.dropEnrollment(enrollmentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['enrollments'] });
      queryClient.invalidateQueries({ queryKey: ['sections'] });
      setDropError(null);
    },
    onError: (err: unknown) => {
      setDropError(getErrorMessage(err));
    },
  });

  function openEnrollModal() {
    setEnrollStudentId(effectiveId ? String(effectiveId) : '');
    setEnrollSectionId('');
    setEnrollError(null);
    setIsEnrollModalOpen(true);
  }

  function closeEnrollModal() {
    setIsEnrollModalOpen(false);
    setEnrollStudentId('');
    setEnrollSectionId('');
    setEnrollError(null);
  }

  function handleEnrollSubmit(e: FormEvent) {
    e.preventDefault();
    setEnrollError(null);

    const sid = Number(enrollStudentId);
    const secId = Number(enrollSectionId);

    if (!sid || sid <= 0) {
      setEnrollError('Please enter a valid student ID.');
      return;
    }
    if (!secId) {
      setEnrollError('Please select a section.');
      return;
    }

    enrollMutation.mutate({ sid, secId });
  }

  function handleDrop(enrollment: Enrollment) {
    const courseName = enrollment.section?.course
      ? `${enrollment.section.course.course_code} ${enrollment.section.course.name}`
      : `Section ${enrollment.section_id}`;

    if (
      window.confirm(
        `Are you sure you want to drop "${courseName}"? This action cannot be undone.`,
      )
    ) {
      setDropError(null);
      dropMutation.mutate(enrollment.enrollment_id);
    }
  }

  function formatSectionLabel(section: Section): string {
    const courseInfo = section.course
      ? `${section.course.course_code} ${section.course.name}`
      : `Course ${section.course_id}`;
    return `${section.section_number} - ${courseInfo}`;
  }

  const columns = [
    {
      key: 'course',
      header: 'Course',
      render: (e: Enrollment) =>
        `${e.section?.course?.course_code ?? ''} - ${e.section?.course?.name ?? 'N/A'}`,
    },
    {
      key: 'section',
      header: 'Section',
      render: (e: Enrollment) => e.section?.section_number ?? 'N/A',
    },
    {
      key: 'instructor',
      header: 'Instructor',
      render: (e: Enrollment) => e.section?.instructor_name ?? 'N/A',
    },
    {
      key: 'classroom',
      header: 'Classroom',
      render: (e: Enrollment) =>
        e.section?.classroom
          ? `${e.section.classroom.building} ${e.section.classroom.room_number}`
          : 'N/A',
    },
    { key: 'enrollment_date', header: 'Enrolled' },
    {
      key: 'status',
      header: 'Status',
      render: (e: Enrollment) => (
        <span
          className={`rounded-full px-2 py-0.5 text-xs font-medium ${
            e.status === 'enrolled'
              ? 'bg-green-100 text-green-700'
              : e.status === 'dropped'
                ? 'bg-red-100 text-red-700'
                : 'bg-blue-100 text-blue-700'
          }`}
        >
          {e.status}
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Actions',
      render: (e: Enrollment) =>
        e.status === 'enrolled' ? (
          <button
            onClick={() => handleDrop(e)}
            disabled={dropMutation.isPending}
            className="rounded-lg bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700 disabled:opacity-50"
          >
            {dropMutation.isPending ? 'Dropping...' : 'Drop'}
          </button>
        ) : null,
    },
  ];

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-800">Enrollments</h1>
        <div className="flex gap-2">
          {!hasStudentRole && (
            <>
              <input
                type="text"
                placeholder="Student ID..."
                value={studentId}
                onChange={(e) => setStudentId(e.target.value)}
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <button
                onClick={() => setSearchId(studentId)}
                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
              >
                Search
              </button>
            </>
          )}
          <button
            onClick={openEnrollModal}
            className="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
          >
            {hasStudentRole ? 'Enroll in Course' : 'Enroll Student'}
          </button>
        </div>
      </div>

      {hasStudentRole && studentProfile && (
        <div className="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-800">
          Welcome, <strong>{studentProfile.first_name} {studentProfile.last_name}</strong> ({studentProfile.student_number})
        </div>
      )}

      {dropError && (
        <div className="mb-4">
          <ErrorAlert message={dropError} onDismiss={() => setDropError(null)} />
        </div>
      )}

      {!hasStudentRole && !effectiveId && (
        <p className="py-8 text-center text-gray-500">
          Enter a student ID to view enrollments.
        </p>
      )}

      {error && <ErrorAlert message="Failed to load enrollments" />}
      {isLoading ? (
        <LoadingSpinner />
      ) : (
        effectiveId && (
          <DataTable columns={columns} data={(data as Enrollment[]) ?? []} keyField="enrollment_id" />
        )
      )}

      {/* Enroll Modal */}
      <Modal isOpen={isEnrollModalOpen} onClose={closeEnrollModal} title={hasStudentRole ? 'Enroll in Course' : 'Enroll Student'}>
        <form onSubmit={handleEnrollSubmit} className="space-y-4">
          {enrollError && (
            <ErrorAlert message={enrollError} onDismiss={() => setEnrollError(null)} />
          )}

          {hasStudentRole ? (
            <input type="hidden" value={enrollStudentId} />
          ) : (
            <div>
              <label htmlFor="enroll-student-id" className="mb-1 block text-sm font-medium text-gray-700">
                Student ID
              </label>
              <input
                id="enroll-student-id"
                type="number"
                min="1"
                value={enrollStudentId}
                onChange={(e) => setEnrollStudentId(e.target.value)}
                placeholder="Enter student ID"
                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                required
              />
            </div>
          )}

          <div>
            <label htmlFor="enroll-section-id" className="mb-1 block text-sm font-medium text-gray-700">
              Section
            </label>
            <select
              id="enroll-section-id"
              value={enrollSectionId}
              onChange={(e) => setEnrollSectionId(e.target.value)}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              required
            >
              <option value="">Select a section...</option>
              {sections.map((section) => (
                <option key={section.section_id} value={section.section_id}>
                  {formatSectionLabel(section)}
                  {section.remaining_capacity !== undefined
                    ? ` (${section.remaining_capacity} seats left)`
                    : ''}
                </option>
              ))}
            </select>
          </div>

          <div className="flex justify-end gap-2 pt-2">
            <button
              type="button"
              onClick={closeEnrollModal}
              className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={enrollMutation.isPending}
              className="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
            >
              {enrollMutation.isPending ? 'Enrolling...' : 'Enroll'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
