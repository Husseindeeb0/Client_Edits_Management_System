import { useDeleteClientMutation } from '../../client/clientApi';
import { getErrorMessage } from '../../errors/apiError';
import type { Client } from '../../types/client';
import ConfirmDialog from '../SharedComponents/ConfirmDialog';

interface DeleteClientDialogProps {
  /** The client to delete; `null` keeps the dialog closed. */
  client: Client | null;
  onClose: () => void;
  onDeleted: () => void;
}

export default function DeleteClientDialog({ client, onClose, onDeleted }: DeleteClientDialogProps) {
  const [deleteClient, { isLoading, error, reset }] = useDeleteClientMutation();

  function handleClose() {
    reset();
    onClose();
  }

  async function handleConfirm() {
    if (!client) {
      return;
    }

    // Fails with "has active edits" while any of its edits is still in progress
    const result = await deleteClient(client.id);
    if (!result.error) {
      reset();
      onDeleted();
    }
  }

  return (
    <ConfirmDialog
      isOpen={client !== null}
      title="Delete client"
      message={`Delete ${client?.name}? This also deletes all of its companies, projects, edits and attachments.`}
      isLoading={isLoading}
      error={error ? getErrorMessage(error) : undefined}
      onConfirm={handleConfirm}
      onCancel={handleClose}
    />
  );
}
