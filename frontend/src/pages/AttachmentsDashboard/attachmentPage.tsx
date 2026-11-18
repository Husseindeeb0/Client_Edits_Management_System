import { useState } from 'react';
import { Link } from 'react-router';
import {
  getAttachmentDownloadUrl,
  useDeleteAttachmentMutation,
  useGetPaginatedAttachmentsQuery,
} from '../../client/attachmentApi';
import ConfirmDialog from '../../components/SharedComponents/ConfirmDialog';
import DataTable from '../../components/SharedComponents/DataTable';
import type { Column } from '../../components/SharedComponents/DataTable';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Pagination from '../../components/SharedComponents/Pagination';
import SearchInput from '../../components/SharedComponents/SearchInput';
import { getErrorMessage } from '../../errors/apiError';
import { formatDate } from '../../lib/format';
import type { AttachmentListItem } from '../../types/attachment';

export default function AttachmentPage() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [deletingAttachment, setDeletingAttachment] = useState<AttachmentListItem | null>(null);

  const { data, isLoading, error, refetch } = useGetPaginatedAttachmentsQuery({ page, search });
  const [deleteAttachment, deleteState] = useDeleteAttachmentMutation();

  function handleSearch(term: string) {
    setSearch(term);
    setPage(1);
  }

  function closeDeleteDialog() {
    deleteState.reset();
    setDeletingAttachment(null);
  }

  async function handleDelete() {
    if (!deletingAttachment) {
      return;
    }

    const result = await deleteAttachment({ id: deletingAttachment.id, editId: deletingAttachment.edit_id });
    if (!result.error) {
      closeDeleteDialog();
    }
  }

  const columns: Column<AttachmentListItem>[] = [
    {
      header: 'File',
      render: (attachment) => (
        <a href={getAttachmentDownloadUrl(attachment.id)} className="font-medium break-all text-slate-100 hover:underline">
          {attachment.original_file_name}
        </a>
      ),
    },
    {
      header: 'Edit',
      render: (attachment) => (
        <Link to={`/edits/${attachment.edit_id}`} className="hover:underline">
          {attachment.edit_title}
        </Link>
      ),
    },
    { header: 'Description', render: (attachment) => attachment.description ?? '–' },
    { header: 'Uploaded', render: (attachment) => formatDate(attachment.created_at) },
    {
      header: 'Actions',
      render: (attachment) => (
        <button type="button" onClick={() => setDeletingAttachment(attachment)} className="text-red-400 hover:text-red-300">
          Delete
        </button>
      ),
    },
  ];

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-semibold">Attachments</h1>

      <SearchInput onSearch={handleSearch} placeholder="Search by file name" />

      {isLoading ? (
        <LoadingState />
      ) : error ? (
        <ErrorState message={getErrorMessage(error)} onRetry={refetch} />
      ) : !data || data.data.length === 0 ? (
        <EmptyState message={search ? 'No files match your search.' : 'No attachments yet.'} />
      ) : (
        <>
          <DataTable columns={columns} rows={data.data} />
          <Pagination meta={data.meta} onPageChange={setPage} />
        </>
      )}

      <ConfirmDialog
        isOpen={deletingAttachment !== null}
        title="Delete attachment"
        message={`Delete "${deletingAttachment?.original_file_name}"? The file is removed for good.`}
        isLoading={deleteState.isLoading}
        error={deleteState.error ? getErrorMessage(deleteState.error) : undefined}
        onConfirm={handleDelete}
        onCancel={closeDeleteDialog}
      />
    </div>
  );
}
