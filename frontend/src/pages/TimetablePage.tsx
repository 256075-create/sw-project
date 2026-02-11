import { useQuery } from '@tanstack/react-query';
import { studentsApi } from '../api/students';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import type { TimetableSlot } from '../types';

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

interface TimetablePageProps {
  studentId: number;
}

export default function TimetablePage({ studentId }: TimetablePageProps) {
  const { data, isLoading, error } = useQuery({
    queryKey: ['timetable', studentId],
    queryFn: () => studentsApi.timetable(studentId),
    enabled: !!studentId,
  });

  if (isLoading) return <LoadingSpinner />;
  if (error) return <ErrorAlert message="Failed to load timetable" />;

  const timetable = data?.data ?? {};

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold text-gray-800">Weekly Timetable</h1>
      <div className="grid grid-cols-5 gap-4">
        {DAYS.map((day) => (
          <div key={day} className="rounded-lg border border-gray-200 bg-white">
            <div className="border-b bg-blue-50 px-4 py-2 text-center font-medium text-blue-700">
              {day}
            </div>
            <div className="space-y-2 p-2">
              {(timetable[day] ?? []).map((slot: TimetableSlot, idx: number) => (
                <div
                  key={idx}
                  className="rounded-lg border border-blue-100 bg-blue-50 p-2 text-xs"
                >
                  <div className="font-semibold text-blue-800">{slot.course_code}</div>
                  <div className="text-gray-700">{slot.course_name}</div>
                  <div className="mt-1 text-gray-500">
                    {slot.start_time} - {slot.end_time}
                  </div>
                  <div className="text-gray-500">{slot.classroom}</div>
                  <div className="text-gray-500">{slot.instructor_name}</div>
                </div>
              ))}
              {(!timetable[day] || timetable[day].length === 0) && (
                <p className="py-4 text-center text-xs text-gray-400">No classes</p>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
