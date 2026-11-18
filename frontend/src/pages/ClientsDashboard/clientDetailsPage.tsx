import { useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { useGetClientDetailsQuery } from '../../client/clientApi';
import ClientActiveEditsTab from '../../components/ClientsComponents/ClientActiveEditsTab';
import ClientCompaniesTab from '../../components/ClientsComponents/ClientCompaniesTab';
import ClientForm from '../../components/ClientsComponents/ClientForm';
import ClientProjectsTab from '../../components/ClientsComponents/ClientProjectsTab';
import ClientSummaryCards from '../../components/ClientsComponents/ClientSummaryCards';
import DeleteClientDialog from '../../components/ClientsComponents/DeleteClientDialog';
import Breadcrumbs from '../../components/SharedComponents/Breadcrumbs';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Tabs from '../../components/SharedComponents/Tabs';
import { getErrorMessage } from '../../errors/apiError';

const TABS = [
  { id: 'active-edits', label: 'Active edits' },
  { id: 'projects', label: 'Projects' },
  { id: 'companies', label: 'Companies' },
];

export default function ClientDetailsPage() {
  const clientId = Number(useParams().clientId);
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('active-edits');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);

  const { data: client, isLoading, error, refetch } = useGetClientDetailsQuery(clientId);

  if (isLoading) {
    return <LoadingState />;
  }

  if (error || !client) {
    return <ErrorState message={getErrorMessage(error)} onRetry={refetch} />;
  }

  return (
    <div className="space-y-6">
      <Breadcrumbs items={[{ label: 'Clients', to: '/clients' }, { label: client.name }]} />

      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">{client.name}</h1>
          <p className="mt-1 text-sm text-slate-400">
            {client.phone_number}
            {client.email && ` · ${client.email}`}
          </p>
          <p className="mt-1 text-sm text-slate-400">Last follow-up: {client.last_follow_up_date ?? '–'}</p>
        </div>

        <div className="flex gap-2">
          <button type="button" onClick={() => setIsFormOpen(true)} className="btn-secondary">
            Edit
          </button>
          <button type="button" onClick={() => setIsDeleteOpen(true)} className="btn-danger">
            Delete
          </button>
        </div>
      </div>

      <ClientSummaryCards clientId={client.id} />

      <div className="space-y-4">
        <Tabs tabs={TABS} activeTab={activeTab} onChange={setActiveTab} />

        {/* Only the open tab is rendered, so each tab loads its list when opened */}
        {activeTab === 'active-edits' && <ClientActiveEditsTab clientId={client.id} />}
        {activeTab === 'projects' && <ClientProjectsTab clientId={client.id} />}
        {activeTab === 'companies' && <ClientCompaniesTab clientId={client.id} />}
      </div>

      <Modal isOpen={isFormOpen} title="Edit client" onClose={() => setIsFormOpen(false)}>
        <ClientForm client={client} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteClientDialog
        client={isDeleteOpen ? client : null}
        onClose={() => setIsDeleteOpen(false)}
        onDeleted={() => navigate('/clients', { replace: true })}
      />
    </div>
  );
}
