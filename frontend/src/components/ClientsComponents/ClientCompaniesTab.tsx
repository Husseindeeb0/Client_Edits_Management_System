import { useState } from 'react';
import { Link } from 'react-router';
import { useGetPaginatedCompaniesQuery } from '../../client/companyApi';
import { getErrorMessage } from '../../errors/apiError';
import type { CompanyListItem } from '../../types/company';
import DataTable from '../SharedComponents/DataTable';
import type { Column } from '../SharedComponents/DataTable';
import EmptyState from '../SharedComponents/EmptyState';
import ErrorState from '../SharedComponents/ErrorState';
import LoadingState from '../SharedComponents/LoadingState';
import Pagination from '../SharedComponents/Pagination';
import SearchInput from '../SharedComponents/SearchInput';

const columns: Column<CompanyListItem>[] = [
  {
    header: 'Name',
    render: (company) => (
      <Link to={`/companies/${company.id}`} className="font-medium text-slate-100 hover:underline">
        {company.name}
      </Link>
    ),
  },
];

export default function ClientCompaniesTab({ clientId }: { clientId: number }) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const { data, isLoading, error, refetch } = useGetPaginatedCompaniesQuery({ client_id: clientId, page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  return (
    <div className="space-y-4">
      <SearchInput onSearch={handleSearch} placeholder="Search companies by name" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message="No companies yet." />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}
    </div>
  );
}
