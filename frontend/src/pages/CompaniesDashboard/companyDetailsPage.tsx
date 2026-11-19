import { useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router';
import { useGetCompanyDetailsQuery } from '../../client/companyApi';
import CompanyForm from '../../components/CompaniesComponents/CompanyForm';
import CompanyProjectsList from '../../components/CompaniesComponents/CompanyProjectsList';
import DeleteCompanyDialog from '../../components/CompaniesComponents/DeleteCompanyDialog';
import Breadcrumbs from '../../components/SharedComponents/Breadcrumbs';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import { getErrorMessage } from '../../errors/apiError';

export default function CompanyDetailsPage() {
  const companyId = Number(useParams().companyId);
  const navigate = useNavigate();
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);

  const { data: company, isLoading, error, refetch } = useGetCompanyDetailsQuery(companyId);

  if (isLoading) {
    return <LoadingState />;
  }

  if (error || !company) {
    return <ErrorState message={getErrorMessage(error)} onRetry={refetch} />;
  }

  return (
    <div className="space-y-6">
      <Breadcrumbs
        items={[
          { label: 'Clients', to: '/clients' },
          { label: company.client?.name ?? 'Client', type: 'Client', to: `/clients/${company.client_id}` },
          { label: company.name, type: 'Company' },
        ]}
      />

      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">{company.name}</h1>
          {company.client && (
            <p className="mt-1 text-sm text-slate-400">
              Client:{' '}
              <Link to={`/clients/${company.client.id}`} className="font-medium text-slate-100 hover:underline">
                {company.client.name}
              </Link>
              {` · ${company.client.phone_number}`}
              {company.client.email && ` · ${company.client.email}`}
            </p>
          )}
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

      <section className="space-y-4">
        <h2 className="text-lg font-semibold">Projects</h2>
        <CompanyProjectsList companyId={company.id} />
      </section>

      <Modal isOpen={isFormOpen} title="Edit company" onClose={() => setIsFormOpen(false)}>
        <CompanyForm company={company} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteCompanyDialog
        company={isDeleteOpen ? company : null}
        onClose={() => setIsDeleteOpen(false)}
        onDeleted={() => navigate('/companies', { replace: true })}
      />
    </div>
  );
}
