import { useState } from 'react';
import { Link } from 'react-router';
import { useGetPaginatedEditsQuery } from '../../client/editApi';
import { getErrorMessage } from '../../errors/apiError';
import { formatMoney } from '../../lib/format';
import type { EditListItem } from '../../types/edit';
import EditPriorityBadge from '../EditComponents/EditPriorityBadge';
import EditStatusBadge from '../EditComponents/EditStatusBadge';
import DataTable from '../SharedComponents/DataTable';
import type { Column } from '../SharedComponents/DataTable';
import EmptyState from '../SharedComponents/EmptyState';
import ErrorState from '../SharedComponents/ErrorState';
import LoadingState from '../SharedComponents/LoadingState';
import Pagination from '../SharedComponents/Pagination';
import SearchInput from '../SharedComponents/SearchInput';

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
];

/** The client's Pending, Confirmed and In Progress edits. */
export default function ClientActiveEditsTab({ clientId }: { clientId: number }) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const { data, isLoading, error, refetch } = useGetPaginatedEditsQuery({ client_id: clientId, active: true, page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  return (
    <div className="space-y-4">
      <SearchInput onSearch={handleSearch} placeholder="Search edits by title" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message="No active edits." />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}
    </div>
  );
}
