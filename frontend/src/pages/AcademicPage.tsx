import { useState, useEffect, type FormEvent } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { academicApi } from '../api/academic';
import LoadingSpinner from '../components/common/LoadingSpinner';
import ErrorAlert from '../components/common/ErrorAlert';
import Modal from '../components/common/Modal';
import type { University, College, Department, Major } from '../types';

// ---------------------------------------------------------------------------
// Types for the unified modal
// ---------------------------------------------------------------------------

type EntityType = 'university' | 'college' | 'department' | 'major';

interface ModalState {
  open: boolean;
  mode: 'add' | 'edit';
  entityType: EntityType;
  parentId?: number;
  existing?: University | College | Department | Major;
}

const INITIAL_MODAL: ModalState = {
  open: false,
  mode: 'add',
  entityType: 'university',
};

// ---------------------------------------------------------------------------
// Main page component
// ---------------------------------------------------------------------------

export default function AcademicPage() {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['academic-hierarchy'],
    queryFn: () => academicApi.hierarchy(),
  });

  // --- unified modal state ---
  const [modal, setModal] = useState<ModalState>(INITIAL_MODAL);

  const closeModal = () => setModal(INITIAL_MODAL);

  const openAdd = (entityType: EntityType, parentId?: number) =>
    setModal({ open: true, mode: 'add', entityType, parentId });

  const openEdit = (entityType: EntityType, existing: University | College | Department | Major) =>
    setModal({ open: true, mode: 'edit', entityType, existing });

  // --- mutations ---

  const deleteMutation = useMutation({
    mutationFn: async ({ entityType, id }: { entityType: EntityType; id: number }) => {
      switch (entityType) {
        case 'university':
          return academicApi.universities.delete(id);
        case 'college':
          return academicApi.colleges.delete(id);
        case 'department':
          return academicApi.departments.delete(id);
        case 'major':
          return academicApi.majors.delete(id);
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['academic-hierarchy'] });
    },
  });

  const handleDelete = (entityType: EntityType, id: number, name: string) => {
    if (!window.confirm(`Are you sure you want to delete "${name}"?`)) return;
    deleteMutation.mutate({ entityType, id });
  };

  // --- render ---

  if (isLoading) return <LoadingSpinner />;
  if (error) return <ErrorAlert message="Failed to load academic hierarchy" />;

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-800">Academic Structure</h1>
        <button
          onClick={() => openAdd('university')}
          className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700"
        >
          Add University
        </button>
      </div>

      <div className="space-y-4">
        {(data ?? []).map((university: University) => (
          <UniversityCard
            key={university.university_id}
            university={university}
            onEdit={openEdit}
            onDelete={handleDelete}
            onAdd={openAdd}
          />
        ))}
        {(!data || data.length === 0) && (
          <p className="text-center text-gray-500">No universities found.</p>
        )}
      </div>

      {/* Single adaptive modal */}
      <EntityModal modal={modal} onClose={closeModal} />
    </div>
  );
}

// ---------------------------------------------------------------------------
// Action button helpers
// ---------------------------------------------------------------------------

function EditButton({ onClick }: { onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      className="rounded px-2 py-0.5 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700"
    >
      Edit
    </button>
  );
}

function DeleteButton({ onClick }: { onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      className="rounded px-2 py-0.5 text-xs font-medium text-red-400 hover:bg-red-50 hover:text-red-600"
    >
      Delete
    </button>
  );
}

function AddChildButton({ label, onClick }: { label: string; onClick: () => void }) {
  return (
    <button
      onClick={onClick}
      className="rounded px-2 py-0.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
    >
      {label}
    </button>
  );
}

// ---------------------------------------------------------------------------
// Card components
// ---------------------------------------------------------------------------

interface CardActions {
  onEdit: (entityType: EntityType, existing: University | College | Department | Major) => void;
  onDelete: (entityType: EntityType, id: number, name: string) => void;
  onAdd: (entityType: EntityType, parentId?: number) => void;
}

function UniversityCard({ university, onEdit, onDelete, onAdd }: { university: University } & CardActions) {
  return (
    <div className="rounded-lg border border-gray-200 bg-white p-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold text-blue-700">
          {university.name} <span className="text-sm text-gray-500">({university.code})</span>
        </h2>
        <div className="flex items-center gap-1">
          <AddChildButton label="Add College" onClick={() => onAdd('college', university.university_id)} />
          <EditButton onClick={() => onEdit('university', university)} />
          <DeleteButton onClick={() => onDelete('university', university.university_id, university.name)} />
        </div>
      </div>

      {university.colleges && university.colleges.length > 0 && (
        <div className="ml-4 mt-3 space-y-3">
          {university.colleges.map((college: College) => (
            <CollegeCard
              key={college.college_id}
              college={college}
              onEdit={onEdit}
              onDelete={onDelete}
              onAdd={onAdd}
            />
          ))}
        </div>
      )}
    </div>
  );
}

function CollegeCard({ college, onEdit, onDelete, onAdd }: { college: College } & CardActions) {
  return (
    <div className="rounded border-l-4 border-green-400 bg-green-50 p-3">
      <div className="flex items-center justify-between">
        <h3 className="font-medium text-green-700">
          {college.name} <span className="text-sm text-gray-500">({college.code})</span>
        </h3>
        <div className="flex items-center gap-1">
          <AddChildButton label="Add Department" onClick={() => onAdd('department', college.college_id)} />
          <EditButton onClick={() => onEdit('college', college)} />
          <DeleteButton onClick={() => onDelete('college', college.college_id, college.name)} />
        </div>
      </div>

      {college.departments && college.departments.length > 0 && (
        <div className="ml-4 mt-2 space-y-2">
          {college.departments.map((dept: Department) => (
            <DepartmentCard
              key={dept.department_id}
              department={dept}
              onEdit={onEdit}
              onDelete={onDelete}
              onAdd={onAdd}
            />
          ))}
        </div>
      )}
    </div>
  );
}

function DepartmentCard({ department, onEdit, onDelete, onAdd }: { department: Department } & CardActions) {
  return (
    <div className="rounded border-l-4 border-orange-300 bg-orange-50 p-2">
      <div className="flex items-center justify-between">
        <h4 className="text-sm font-medium text-orange-700">
          {department.name} <span className="text-xs text-gray-500">({department.code})</span>
        </h4>
        <div className="flex items-center gap-1">
          <AddChildButton label="Add Major" onClick={() => onAdd('major', department.department_id)} />
          <EditButton onClick={() => onEdit('department', department)} />
          <DeleteButton onClick={() => onDelete('department', department.department_id, department.name)} />
        </div>
      </div>

      {department.majors && department.majors.length > 0 && (
        <div className="ml-3 mt-1 space-y-0.5">
          {department.majors.map((major: Major) => (
            <div
              key={major.major_id}
              className="flex items-center justify-between rounded px-1 py-0.5 text-xs text-gray-600 hover:bg-orange-100"
            >
              <span>
                {major.name} ({major.code}) - {major.total_credits} credits
              </span>
              <div className="flex items-center gap-1">
                <EditButton onClick={() => onEdit('major', major)} />
                <DeleteButton onClick={() => onDelete('major', major.major_id, major.name)} />
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

// ---------------------------------------------------------------------------
// Unified entity modal
// ---------------------------------------------------------------------------

function EntityModal({ modal, onClose }: { modal: ModalState; onClose: () => void }) {
  const queryClient = useQueryClient();

  // --- local form state ---
  const [name, setName] = useState('');
  const [code, setCode] = useState('');
  const [totalCredits, setTotalCredits] = useState<number>(0);
  const [formError, setFormError] = useState('');

  // Reset form fields when the modal opens with new data.
  // The key prop on EntityModal forces a remount for each distinct entity,
  // so this effect runs once per mount with the correct modal values.
  useEffect(() => {
    if (modal.mode === 'edit' && modal.existing) {
      setName(modal.existing.name);
      setCode(modal.existing.code);
      if (modal.entityType === 'major') {
        setTotalCredits((modal.existing as Major).total_credits ?? 0);
      }
    } else {
      setName('');
      setCode('');
      setTotalCredits(0);
    }
    setFormError('');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Create / Update mutations
  const saveMutation = useMutation({
    mutationFn: async () => {
      const { entityType, mode, parentId, existing } = modal;

      switch (entityType) {
        case 'university': {
          const payload = { name, code };
          return mode === 'add'
            ? academicApi.universities.create(payload)
            : academicApi.universities.update((existing as University).university_id, payload);
        }
        case 'college': {
          const payload = {
            name,
            code,
            university_id: mode === 'add' ? parentId! : (existing as College).university_id,
          };
          return mode === 'add'
            ? academicApi.colleges.create(payload)
            : academicApi.colleges.update((existing as College).college_id, payload);
        }
        case 'department': {
          const payload = {
            name,
            code,
            college_id: mode === 'add' ? parentId! : (existing as Department).college_id,
          };
          return mode === 'add'
            ? academicApi.departments.create(payload)
            : academicApi.departments.update((existing as Department).department_id, payload);
        }
        case 'major': {
          const payload = {
            name,
            code,
            total_credits: totalCredits,
            department_id: mode === 'add' ? parentId! : (existing as Major).department_id,
          };
          return mode === 'add'
            ? academicApi.majors.create(payload)
            : academicApi.majors.update((existing as Major).major_id, payload);
        }
      }
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['academic-hierarchy'] });
      onClose();
    },
    onError: (err: unknown) => {
      const message =
        err instanceof Error ? err.message : 'An unexpected error occurred. Please try again.';
      setFormError(message);
    },
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !code.trim()) {
      setFormError('Name and code are required.');
      return;
    }
    if (modal.entityType === 'major' && totalCredits < 0) {
      setFormError('Total credits must be a non-negative number.');
      return;
    }
    setFormError('');
    saveMutation.mutate();
  };

  // Build modal title
  const entityLabel = modal.entityType.charAt(0).toUpperCase() + modal.entityType.slice(1);
  const title = modal.mode === 'add' ? `Add ${entityLabel}` : `Edit ${entityLabel}`;

  // We key the Modal on the stringified modal identity so React re-mounts and
  // resets internal state each time it opens for a different entity.
  const modalKey = modal.open
    ? `${modal.mode}-${modal.entityType}-${modal.parentId ?? ''}-${
        modal.existing
          ? (modal.existing as unknown as Record<string, unknown>)[`${modal.entityType}_id`]
          : 'new'
      }`
    : 'closed';

  return (
    <Modal key={modalKey} isOpen={modal.open} onClose={onClose} title={title}>
      <form onSubmit={handleSubmit} className="space-y-4">
        {formError && (
          <div className="rounded bg-red-50 p-2 text-sm text-red-600">{formError}</div>
        )}

        <div>
          <label htmlFor="entity-name" className="mb-1 block text-sm font-medium text-gray-700">
            Name
          </label>
          <input
            id="entity-name"
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            required
          />
        </div>

        <div>
          <label htmlFor="entity-code" className="mb-1 block text-sm font-medium text-gray-700">
            Code
          </label>
          <input
            id="entity-code"
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value)}
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            required
          />
        </div>

        {modal.entityType === 'major' && (
          <div>
            <label
              htmlFor="entity-credits"
              className="mb-1 block text-sm font-medium text-gray-700"
            >
              Total Credits
            </label>
            <input
              id="entity-credits"
              type="number"
              min={0}
              value={totalCredits}
              onChange={(e) => setTotalCredits(Number(e.target.value))}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              required
            />
          </div>
        )}

        <div className="flex justify-end gap-2 pt-2">
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={saveMutation.isPending}
            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 disabled:opacity-50"
          >
            {saveMutation.isPending ? 'Saving...' : modal.mode === 'add' ? 'Create' : 'Update'}
          </button>
        </div>
      </form>
    </Modal>
  );
}

