import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import AppLayout from './components/layout/AppLayout';
import ProtectedRoute from './components/auth/ProtectedRoute';
import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import StudentsPage from './pages/StudentsPage';
import CoursesPage from './pages/CoursesPage';
import SectionsPage from './pages/SectionsPage';
import AcademicPage from './pages/AcademicPage';
import EnrollmentsPage from './pages/EnrollmentsPage';
import ClassroomsPage from './pages/ClassroomsPage';
import UsersPage from './pages/UsersPage';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30000,
    },
  },
});

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route
            element={
              <ProtectedRoute>
                <AppLayout />
              </ProtectedRoute>
            }
          >
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/students" element={<StudentsPage />} />
            <Route path="/courses" element={<CoursesPage />} />
            <Route path="/sections" element={<SectionsPage />} />
            <Route path="/enrollments" element={<EnrollmentsPage />} />
            <Route path="/academic" element={<AcademicPage />} />
            <Route path="/classrooms" element={<ClassroomsPage />} />
            <Route path="/users" element={<UsersPage />} />
          </Route>
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
