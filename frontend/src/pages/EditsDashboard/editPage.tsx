import { useState } from 'react';
import { Plus } from 'lucide-react';
import { Link } from 'react-router';
import { useGetPaginatedEditsQuery } from '../../client/editApi';
import DeleteEditDialog from '../../components/EditComponents/DeleteEditDialog';
import EditForm from '../../components/EditComponents/EditForm';
import EditPriorityBadge from '../../components/EditComponents/EditPriorityBadge';
import EditStatusBadge from '../../components/EditComponents/EditStatusBadge';
import Badge from '../../components/SharedComponents/Badge';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import { formatMoney } from '../../lib/format';
import { EDIT_PRIORITIES, EDIT_STATUSES } from '../../types/edit';
import type { EditListItem, EditPriority, EditStatus } from '../../types/edit';

export default function EditPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState<EditStatus | ''>('');
  const [priority, setPriority] = useState<EditPriority | ''>('');
  const [paid, setPaid] = useState<'' | 'paid' | 'unpaid'>('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [deletingEdit, setDeletingEdit] = useState<EditListItem | null>(null);

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
    { header: 'Status', render: (edit) => <EditStatusBadge status={edit.status} /> },
    { header: 'Priority', render: (edit) => <EditPriorityBadge priority={edit.priority} /> },
    { header: 'Due date', render: (edit) => edit.due_date ?? '–' },
    { header: 'Price', render: (edit) => formatMoney(edit.price) },
    { header: 'Paid', render: (edit) => <Badge label={edit.is_paid ? 'Paid' : 'Unpaid'} tone={edit.is_paid ? 'green' : 'gray'} /> },
    {
      // Editing happens on the details page: list rows don't include the description
      header: 'Actions',
      render: (edit) => (
        <button type="button" onClick={() => setDeletingEdit(edit)} className="text-red-400 hover:text-red-300">
          Delete
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold">Edits</h1>
        <button type="button" onClick={() => setIsFormOpen(true)} className="btn-primary">
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

      <Modal isOpen={isFormOpen} title="New edit" onClose={() => setIsFormOpen(false)}>
        <EditForm onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteEditDialog edit={deletingEdit} onClose={() => setDeletingEdit(null)} onDeleted={() => setDeletingEdit(null)} />
    </div>
  );
}
