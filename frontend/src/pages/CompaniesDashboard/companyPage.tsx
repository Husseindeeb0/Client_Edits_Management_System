import { useState } from 'react';
import { Plus } from 'lucide-react';
import { Link } from 'react-router';
import { useGetPaginatedCompaniesQuery } from '../../client/companyApi';
import CompanyForm from '../../components/CompaniesComponents/CompanyForm';
import DeleteCompanyDialog from '../../components/CompaniesComponents/DeleteCompanyDialog';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import type { CompanyListItem } from '../../types/company';

export default function CompanyPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingCompany, setEditingCompany] = useState<CompanyListItem | undefined>(undefined);
  const [deletingCompany, setDeletingCompany] = useState<CompanyListItem | null>(null);

  const { data, isLoading, error, refetch } = useGetPaginatedCompaniesQuery({ page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  function openForm(company?: CompanyListItem) {
    setEditingCompany(company);
    setIsFormOpen(true);
  }

  const columns: Column<CompanyListItem>[] = [
    {
      header: 'Name',
      render: (company) => (
        <Link to={`/companies/${company.id}`} className="font-medium text-slate-100 hover:underline">
          {company.name}
        </Link>
      ),
    },
    {
      header: 'Client',
      render: (company) => (
        <Link to={`/clients/${company.client_id}`} className="hover:underline">
          {company.client_name}
        </Link>
      ),
    },
    {
      header: 'Actions',
      render: (company) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(company)} className="text-slate-400 hover:text-white">
            Edit
          </button>
          <button type="button" onClick={() => setDeletingCompany(company)} className="text-red-400 hover:text-red-300">
            Delete
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold">Companies</h1>
        <button type="button" onClick={() => openForm()} className="btn-primary">
          <Plus className="size-4" />
          New company
        </button>
      </div>

      <SearchInput onSearch={handleSearch} placeholder="Search by company or client name" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message={search ? 'No companies match your search.' : 'No companies yet.'} />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}

      <Modal isOpen={isFormOpen} title={editingCompany ? 'Edit company' : 'New company'} onClose={() => setIsFormOpen(false)}>
        <CompanyForm company={editingCompany} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteCompanyDialog
        company={deletingCompany}
        onClose={() => setDeletingCompany(null)}
        onDeleted={() => setDeletingCompany(null)}
      />
    </div>
  );
}
