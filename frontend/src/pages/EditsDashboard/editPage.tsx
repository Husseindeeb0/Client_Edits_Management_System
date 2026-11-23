import { useState } from 'react';
import { Plus } from 'lucide-react';
import { Link } from 'react-router';
import { toast } from 'sonner';
import { useGetEditDetailsQuery, useGetPaginatedEditsQuery, useUpdateEditMutation } from '../../client/editApi';
import DeleteEditDialog from '../../components/EditComponents/DeleteEditDialog';
import EditForm from '../../components/EditComponents/EditForm';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import InlineDateInput from '../../components/SharedComponents/InlineDateInput';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import { formatMoney } from '../../lib/format';
import { EDIT_PRIORITIES, EDIT_STATUSES } from '../../types/edit';
import type { EditListItem, EditPriority, EditStatus, UpdateEditPayload } from '../../types/edit';

export default function EditPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<EditStatus | ''>('');
  const [priority, setPriority] = useState<EditPriority | ''>('');
  const [paid, setPaid] = useState<'' | 'paid' | 'unpaid'>('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingEditId, setEditingEditId] = useState<number | null>(null);
  const [deletingEdit, setDeletingEdit] = useState<EditListItem | null>(null);
  const [updateEdit] = useUpdateEditMutation();

  // List rows have no description, so the form gets the full edit before it opens.
  // `currentData` is empty while another edit loads, so the form never shows the previous one.
  const editing = useGetEditDetailsQuery(editingEditId ?? 0, { skip: editingEditId === null });

  const { data, isLoading, error, refetch } = useGetPaginatedEditsQuery({
    page,
    search,
    status: status || undefined,
    priority: priority || undefined,
    is_paid: paid === '' ? undefined : paid === 'paid',
  });

  // Every filter change starts again from the first page
  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  // No id = create a new edit
  function openForm(editId: number | null = null) {
    setEditingEditId(editId);
    setIsFormOpen(true);
  }

  function closeForm() {
    setIsFormOpen(false);
    setEditingEditId(null);
  }

  /** Saves one field straight from the table and reports the result in a toast. */
  async function quickUpdate(edit: EditListItem, changes: UpdateEditPayload, successMessage: string) {
    const result = await updateEdit({ id: edit.id, changes });

    if (result.error) {
      toast.error(getErrorMessage(result.error));
      return false;
    }

    toast.success(successMessage);
    return true;
  }

  const columns: Column<EditListItem>[] = [
    {
      header: 'Title',
      render: (edit) => (
        <Link to={`/edits/${edit.id}`} className="font-medium text-slate-100 hover:underline">
          {edit.title}
        </Link>
      ),
    },
    {
      header: 'Project',
      render: (edit) => (
        <Link to={`/projects/${edit.project_id}`} className="hover:underline">
          {edit.project_name}
        </Link>
      ),
    },
    // Quick edits: each cell saves its own field as soon as it changes.
    // `key` includes the saved value, so a cell starts fresh once the list reloads with it.
    {
      header: 'Status',
      render: (edit) => (
        <select
          key={`${edit.id}-${edit.status}`}
          aria-label={`Status of ${edit.title}`}
          defaultValue={edit.status}
          onChange={async (event) => {
            const select = event.currentTarget;
            const status = select.value as EditStatus;

            if (!(await quickUpdate(edit, { status }, `Status of "${edit.title}" changed to ${status}.`))) {
              select.value = edit.status;
            }
          }}
          className="form-input w-auto py-1"
        >
          {EDIT_STATUSES.map((value) => (
            <option key={value} value={value}>
              {value}
            </option>
          ))}
        </select>
      ),
    },
    {
      header: 'Priority',
      render: (edit) => (
        <select
          key={`${edit.id}-${edit.priority}`}
          aria-label={`Priority of ${edit.title}`}
          defaultValue={edit.priority}
          onChange={async (event) => {
            const select = event.currentTarget;
            const priority = select.value as EditPriority;

            if (!(await quickUpdate(edit, { priority }, `Priority of "${edit.title}" changed to ${priority}.`))) {
              select.value = edit.priority;
            }
          }}
          className="form-input w-auto py-1"
        >
          {EDIT_PRIORITIES.map((value) => (
            <option key={value} value={value}>
              {value}
            </option>
          ))}
        </select>
      ),
    },
    {
      header: 'Due date',
      render: (edit) => (
        <InlineDateInput
          key={`${edit.id}-${edit.due_date}`}
          value={edit.due_date}
          label={`Due date of ${edit.title}`}
          onSave={(dueDate) =>
            quickUpdate(
              edit,
              { due_date: dueDate },
              dueDate ? `Due date of "${edit.title}" changed to ${dueDate}.` : `Due date of "${edit.title}" removed.`,
            )
          }
        />
      ),
    },
    { header: 'Price', render: (edit) => formatMoney(edit.price) },
    {
      header: 'Paid',
      render: (edit) => (
        <label className="flex cursor-pointer items-center gap-2">
          <input
            key={`${edit.id}-${edit.is_paid}`}
            type="checkbox"
            defaultChecked={edit.is_paid}
            onChange={async (event) => {
              const checkbox = event.currentTarget;
              const isPaid = checkbox.checked;

              if (!(await quickUpdate(edit, { is_paid: isPaid }, `"${edit.title}" marked as ${isPaid ? 'paid' : 'unpaid'}.`))) {
                checkbox.checked = edit.is_paid;
              }
            }}
            className="size-4 cursor-pointer accent-teal-500"
          />
          {edit.is_paid ? 'Paid' : 'Unpaid'}
        </label>
      ),
    },
    {
      header: 'Actions',
      render: (edit) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(edit.id)} className="text-slate-400 hover:text-white">
            Edit
          </button>
          <button type="button" onClick={() => setDeletingEdit(edit)} className="text-red-400 hover:text-red-300">
            Delete
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold">Edits</h1>
        <button type="button" onClick={() => openForm()} className="btn-primary">
          <Plus className="size-4" />
          New edit
        </button>
      </div>

      <div className="flex flex-wrap gap-3">
        <SearchInput onSearch={handleSearch} placeholder="Search edits by title" />

        <select
          aria-label="Status"
          value={status}
          onChange={(event) => {
            setStatus(event.target.value as EditStatus | '');
            setPage(1);
          }}
          className="form-input w-auto"
        >
          <option value="">All statuses</option>
          {EDIT_STATUSES.map((value) => (
            <option key={value} value={value}>
              {value}
            </option>
          ))}
        </select>

        <select
          aria-label="Priority"
          value={priority}
          onChange={(event) => {
            setPriority(event.target.value as EditPriority | '');
            setPage(1);
          }}
          className="form-input w-auto"
        >
          <option value="">All priorities</option>
          {EDIT_PRIORITIES.map((value) => (
            <option key={value} value={value}>
              {value}
            </option>
          ))}
        </select>

        <select
          aria-label="Paid"
          value={paid}
          onChange={(event) => {
            setPaid(event.target.value as '' | 'paid' | 'unpaid');
            setPage(1);
          }}
          className="form-input w-auto"
        >
          <option value="">Paid and unpaid</option>
          <option value="paid">Paid</option>
          <option value="unpaid">Unpaid</option>
        </select>
      </div>

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message="No edits match these filters." />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}

      <Modal isOpen={isFormOpen} title={editingEditId === null ? 'New edit' : 'Update edit'} onClose={closeForm}>
        {editingEditId === null ? (
          <EditForm onSaved={closeForm} />
        ) : editing.error ? (
          <ErrorState message={getErrorMessage(editing.error)} onRetry={editing.refetch} />
        ) : !editing.currentData ? (
          <LoadingState />
        ) : (
          <EditForm edit={editing.currentData} onSaved={closeForm} />
        )}
      </Modal>

      <DeleteEditDialog edit={deletingEdit} onClose={() => setDeletingEdit(null)} onDeleted={() => setDeletingEdit(null)} />    </div>
  );
}
