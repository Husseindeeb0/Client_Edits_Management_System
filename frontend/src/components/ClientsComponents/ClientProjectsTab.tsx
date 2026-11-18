import { useState } from 'react';
import { Link } from 'react-router';
import { useGetClientProjectsQuery } from '../../client/projectApi';
import { getErrorMessage } from '../../errors/apiError';
import type { ClientProject } from '../../types/project';
import DataTable from '../SharedComponents/DataTable';
import type { Column } from '../SharedComponents/DataTable';
import EmptyState from '../SharedComponents/EmptyState';
import ErrorState from '../SharedComponents/ErrorState';
import LoadingState from '../SharedComponents/LoadingState';
import Pagination from '../SharedComponents/Pagination';
import SearchInput from '../SharedComponents/SearchInput';

const columns: Column<ClientProject>[] = [
  {
    header: 'Name',
    render: (project) => (
      <Link to={`/projects/${project.id}`} className="font-medium text-slate-100 hover:underline">
        {project.name}
      </Link>
    ),
  },
  {
    header: 'Company',
    render: (project) => (
      <Link to={`/companies/${project.company_id}`} className="hover:underline">
        {project.company_name}
      </Link>
    ),
  },
];

/** All projects across the client's companies. */
export default function ClientProjectsTab({ clientId }: { clientId: number }) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const { data, isLoading, error, refetch } = useGetClientProjectsQuery({ clientId, page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  return (
    <div className="space-y-4">
      <SearchInput onSearch={handleSearch} placeholder="Search projects by name" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message="No projects yet." />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}
    </div>
  );
}
