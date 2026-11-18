import { useDeleteCompanyMutation } from '../../client/companyApi';
import { getErrorMessage } from '../../errors/apiError';
import type { Company } from '../../types/company';
import ConfirmDialog from '../SharedComponents/ConfirmDialog';

interface DeleteCompanyDialogProps {
  /** The company to delete; `null` keeps the dialog closed. */
  company: Company | null;
  onClose: () => void;
  onDeleted: () => void;
}

export default function DeleteCompanyDialog({ company, onClose, onDeleted }: DeleteCompanyDialogProps) {
  const [deleteCompany, { isLoading, error, reset }] = useDeleteCompanyMutation();

  function handleClose() {
    reset();
    onClose();
  }

  async function handleConfirm() {
    if (!company) {
      return;
    }

    // Fails with "has active edits" while any of its edits is still in progress
    const result = await deleteCompany(company.id);
    if (!result.error) {
      reset();
      onDeleted();
    }
  }

  return (
    <ConfirmDialog
      isOpen={company !== null}
      title="Delete company"
      message={`Delete ${company?.name}? This also deletes all of its projects, edits and attachments.`}
      isLoading={isLoading}
      error={error ? getErrorMessage(error) : undefined}
      onConfirm={handleConfirm}
      onCancel={handleClose}
    />
  );
}
