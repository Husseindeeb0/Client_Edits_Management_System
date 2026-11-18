import { useState } from 'react';
import { Plus } from 'lucide-react';
import { Link } from 'react-router';
import { useGetPaginatedProjectsQuery } from '../../client/projectApi';
import DeleteProjectDialog from '../../components/ProjectsComponents/DeleteProjectDialog';
import ProjectForm from '../../components/ProjectsComponents/ProjectForm';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import type { ProjectListItem } from '../../types/project';

export default function ProjectPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [editingProject, setEditingProject] = useState<ProjectListItem | undefined>(undefined);
  const [deletingProject, setDeletingProject] = useState<ProjectListItem | null>(null);

  const { data, isLoading, error, refetch } = useGetPaginatedProjectsQuery({ page, search });

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  function openForm(project?: ProjectListItem) {
    setEditingProject(project);
    setIsFormOpen(true);
  }

  const columns: Column<ProjectListItem>[] = [
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
    {
      header: 'Client',
      render: (project) => (
        <Link to={`/clients/${project.client_id}`} className="hover:underline">
          {project.client_name}
        </Link>
      ),
    },
    {
      header: 'Actions',
      render: (project) => (
        <div className="flex gap-3">
          <button type="button" onClick={() => openForm(project)} className="text-slate-400 hover:text-white">
            Edit
          </button>
          <button type="button" onClick={() => setDeletingProject(project)} className="text-red-400 hover:text-red-300">
            Delete
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-semibold">Projects</h1>
        <button type="button" onClick={() => openForm()} className="btn-primary">
          <Plus className="size-4" />
          New project
        </button>
      </div>

      <SearchInput onSearch={handleSearch} placeholder="Search by project, company or client" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message={search ? 'No projects match your search.' : 'No projects yet.'} />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}

      <Modal isOpen={isFormOpen} title={editingProject ? 'Edit project' : 'New project'} onClose={() => setIsFormOpen(false)}>
        <ProjectForm project={editingProject} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <DeleteProjectDialog
        project={deletingProject}
        onClose={() => setDeletingProject(null)}
        onDeleted={() => setDeletingProject(null)}
      />
    </div>
  );
}
