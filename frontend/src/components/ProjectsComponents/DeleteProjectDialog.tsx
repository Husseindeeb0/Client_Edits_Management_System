import { useDeleteProjectMutation } from '../../client/projectApi';
import { getErrorMessage } from '../../errors/apiError';
import type { Project } from '../../types/project';
import ConfirmDialog from '../SharedComponents/ConfirmDialog';

interface DeleteProjectDialogProps {
  /** The project to delete; `null` keeps the dialog closed. */
  project: Project | null;
  onClose: () => void;
  onDeleted: () => void;
}

export default function DeleteProjectDialog({ project, onClose, onDeleted }: DeleteProjectDialogProps) {
  const [deleteProject, { isLoading, error, reset }] = useDeleteProjectMutation();

  function handleClose() {
    reset();
    onClose();
  }

  async function handleConfirm() {
    if (!project) {
      return;
    }

    // Fails with "has active edits" while any of its edits is still in progress
    const result = await deleteProject(project.id);
    if (!result.error) {
      reset();
      onDeleted();
    }
  }

  return (
    <ConfirmDialog
      isOpen={project !== null}
      title="Delete project"
      message={`Delete ${project?.name}? This also deletes all of its edits and attachments.`}
      isLoading={isLoading}
      error={error ? getErrorMessage(error) : undefined}
      onConfirm={handleConfirm}
      onCancel={handleClose}
    />
  );
}
