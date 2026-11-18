import { useState } from 'react';
import { Plus } from 'lucide-react';
import { Link } from 'react-router';
import { useGetPaginatedClientsQuery } from '../../client/clientApi';
import ClientForm from '../../components/ClientsComponents/ClientForm';
import DeleteClientDialog from '../../components/ClientsComponents/DeleteClientDialog';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import type { Client } from '../../types/client';

export default function ClientPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingClient, setEditingClient] = useState<Client | undefined>(undefined);
  const [deletingClient, setDeletingClient] = useState<Client | null>(null);

  const { data, isLoading, error, refetch } = useGetPaginatedClientsQuery({ page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  function openForm(client?: Client) {
    setEditingClient(client);
    setIsFormOpen(true);
  }

  const columns: Column<Client>[] = [
    {
      header: 'Name',
      render: (client) => (
        <Link to={`/clients/${client.id}`} className="font-medium text-slate-100 hover:underline">
          {client.name}
        </Link>
      ),
    },
    { header: 'Phone', render: (client) => client.phone_number },
    { header: 'Email', render: (client) => client.email ?? '–' },
    { header: 'Last follow-up', render: (client) => client.last_follow_up_date ?? '–' },
    {
      header: 'Actions',
      render: (client) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(client)} className="text-slate-400 hover:text-white">
            Edit
          </button>
          <button type="button" onClick={() => setDeletingClient(client)} className="text-red-400 hover:text-red-300">
            Delete
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold">Clients</h1>
        <button type="button" onClick={() => openForm()} className="btn-primary">
          <Plus className="size-4" />
          New client
        </button>
      </div>

      <SearchInput onSearch={handleSearch} placeholder="Search by name, phone or email" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message={search ? 'No clients match your search.' : 'No clients yet.'} />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}

      <Modal isOpen={isFormOpen} title={editingClient ? 'Edit client' : 'New client'} onClose={() => setIsFormOpen(false)}>
        <ClientForm client={editingClient} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteClientDialog
        client={deletingClient}
        onClose={() => setDeletingClient(null)}
        onDeleted={() => setDeletingClient(null)}
      />
    </div>
  );
}
