import { useState } from 'react';
import { Plus } from 'lucide-react';
import { useNavigate, useParams } from 'react-router';
import { useGetProjectDetailsQuery } from '../../client/projectApi';
import EditForm from '../../components/EditComponents/EditForm';
import DeleteProjectDialog from '../../components/ProjectsComponents/DeleteProjectDialog';
import ProjectEditsList from '../../components/ProjectsComponents/ProjectEditsList';
import ProjectForm from '../../components/ProjectsComponents/ProjectForm';
import Breadcrumbs from '../../components/SharedComponents/Breadcrumbs';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import { getErrorMessage } from '../../errors/apiError';

export default function ProjectDetailsPage() {
  const projectId = Number(useParams().projectId);
  const navigate = useNavigate();
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isEditFormOpen, setIsEditFormOpen] = useState(false);
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);

  const { data: project, isLoading, error, refetch } = useGetProjectDetailsQuery(projectId);

  if (isLoading) {
    return <LoadingState />;
  }

  if (error || !project) {
    return <ErrorState message={getErrorMessage(error)} onRetry={refetch} />;
  }

  return (
    <div className="space-y-6">
      <Breadcrumbs
        items={[
          { label: 'Clients', to: '/clients' },
          { label: project.client?.name ?? 'Client', type: 'Client', to: `/clients/${project.client?.id}` },
          { label: project.company?.name ?? 'Company', type: 'Company', to: `/companies/${project.company_id}` },
          { label: project.name, type: 'Project' },
        ]}
      />

      <div className="flex flex-wrap items-start justify-between gap-4">
        <h1 className="text-2xl font-semibold">{project.name}</h1>

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
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h2 className="text-lg font-semibold">Edits</h2>
          <button type="button" onClick={() => setIsEditFormOpen(true)} className="btn-primary">
            <Plus className="size-4" />
            New edit
          </button>
        </div>
        <ProjectEditsList projectId={project.id} />
      </section>

      <Modal isOpen={isFormOpen} title="Edit project" onClose={() => setIsFormOpen(false)}>
        <ProjectForm project={project} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <Modal isOpen={isEditFormOpen} title="New edit" onClose={() => setIsEditFormOpen(false)}>
        <EditForm defaultProjectId={project.id} onSaved={() => setIsEditFormOpen(false)} />
      </Modal>

      <DeleteProjectDialog
        project={isDeleteOpen ? project : null}
        onClose={() => setIsDeleteOpen(false)}
        onDeleted={() => navigate('/projects', { replace: true })}
      />
    </div>
  );
}
