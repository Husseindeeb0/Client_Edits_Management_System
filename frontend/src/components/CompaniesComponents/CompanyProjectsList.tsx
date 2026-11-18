import { useState } from 'react';
import { Link } from 'react-router';
import { useGetCompanyProjectsQuery } from '../../client/projectApi';
import { getErrorMessage } from '../../errors/apiError';
import type { CompanyProject } from '../../types/project';
import DataTable from '../SharedComponents/DataTable';
import type { Column } from '../SharedComponents/DataTable';
import EmptyState from '../SharedComponents/EmptyState';
import ErrorState from '../SharedComponents/ErrorState';
import LoadingState from '../SharedComponents/LoadingState';
import Pagination from '../SharedComponents/Pagination';
import SearchInput from '../SharedComponents/SearchInput';

const columns: Column<CompanyProject>[] = [
  {
    header: 'Name',
    render: (project) => (
      <Link to={`/projects/${project.id}`} className="font-medium text-slate-100 hover:underline">
        {project.name}
      </Link>
    ),
  },
];

export default function CompanyProjectsList({ companyId }: { companyId: number }) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const { data, isLoading, error, refetch } = useGetCompanyProjectsQuery({ companyId, page, search });

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
