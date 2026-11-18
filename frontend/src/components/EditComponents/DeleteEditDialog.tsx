import { useDeleteEditMutation } from '../../client/editApi';
import { getErrorMessage } from '../../errors/apiError';
import ConfirmDialog from '../SharedComponents/ConfirmDialog';

interface DeleteEditDialogProps {
  /** The edit to delete; `null` keeps the dialog closed. */
  edit: { id: number; title: string } | null;
  onClose: () => void;
  onDeleted: () => void;
}

export default function DeleteEditDialog({ edit, onClose, onDeleted }: DeleteEditDialogProps) {
  const [deleteEdit, { isLoading, error, reset }] = useDeleteEditMutation();

  function handleClose() {
    reset();
    onClose();
  }

  async function handleConfirm() {
    if (!edit) {
      return;
    }

    const result = await deleteEdit(edit.id);
    if (!result.error) {
      reset();
      onDeleted();
    }
  }

  return (
    <ConfirmDialog
      isOpen={edit !== null}
      title="Delete edit"
      message={`Delete "${edit?.title}"? This also deletes its attachments.`}
      isLoading={isLoading}
      error={error ? getErrorMessage(error) : undefined}
      onConfirm={handleConfirm}
      onCancel={handleClose}
    />
  );
}
