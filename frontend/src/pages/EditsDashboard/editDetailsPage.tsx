import { useState } from 'react';
import { Paperclip } from 'lucide-react';
import { useNavigate, useParams } from 'react-router';
import { getAttachmentDownloadUrl } from '../../client/attachmentApi';
import { useGetEditDetailsQuery } from '../../client/editApi';
import AttachmentsForm from '../../components/AttachmentsComponents/AttachmentsForm';
import DeleteEditDialog from '../../components/EditComponents/DeleteEditDialog';
import EditForm from '../../components/EditComponents/EditForm';
import EditPriorityBadge from '../../components/EditComponents/EditPriorityBadge';
import EditStatusBadge from '../../components/EditComponents/EditStatusBadge';
import Badge from '../../components/SharedComponents/Badge';
import Breadcrumbs from '../../components/SharedComponents/Breadcrumbs';
import EmptyState from '../../components/SharedComponents/EmptyState';
import ErrorState from '../../components/SharedComponents/ErrorState';
import LoadingState from '../../components/SharedComponents/LoadingState';
import Modal from '../../components/SharedComponents/Modal';
import { getErrorMessage } from '../../errors/apiError';
import { formatDate, formatMoney } from '../../lib/format';

export default function EditDetailsPage() {
  const editId = Number(useParams().editId);
  const navigate = useNavigate();
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [isAttachmentsOpen, setIsAttachmentsOpen] = useState(false);
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);

  const { data: edit, isLoading, error, refetch } = useGetEditDetailsQuery(editId);

  if (isLoading) {
    return <LoadingState />;
  }

  if (error || !edit) {
    return <ErrorState message={getErrorMessage(error)} onRetry={refetch} />;
  }

  const attachments = edit.attachments ?? [];

  const details = [
    { label: 'Price', value: formatMoney(edit.price) },
    { label: 'Paid', value: <Badge label={edit.is_paid ? 'Paid' : 'Unpaid'} tone={edit.is_paid ? 'green' : 'gray'} /> },
    { label: 'Due date', value: edit.due_date ?? '–' },
  ];

  return (
    <div className="space-y-6">
      <Breadcrumbs
        items={[
          { label: 'Clients', to: '/clients' },
          { label: edit.client?.name ?? 'Client', to: `/clients/${edit.client?.id}` },
          { label: edit.company?.name ?? 'Company', to: `/companies/${edit.company?.id}` },
          { label: edit.project?.name ?? 'Project', to: `/projects/${edit.project_id}` },
          { label: edit.title },
        ]}
      />

      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">{edit.title}</h1>
          <div className="mt-2 flex gap-2">
            <EditStatusBadge status={edit.status} />
            <EditPriorityBadge priority={edit.priority} />
          </div>
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

      <dl className="grid grid-cols-1 gap-3 sm:grid-cols-3">
        {details.map((detail) => (
          <div key={detail.label} className="rounded-lg border border-slate-800 bg-slate-900 p-4">
            <dt className="text-sm text-slate-400">{detail.label}</dt>
            <dd className="mt-1 font-medium text-slate-100">{detail.value}</dd>
          </div>
        ))}
      </dl>

      {edit.description && (
        <section>
          <h2 className="mb-2 text-lg font-semibold">Description</h2>
          <p className="text-sm whitespace-pre-line text-slate-300">{edit.description}</p>
        </section>
      )}

      <section className="space-y-4">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h2 className="text-lg font-semibold">Attachments</h2>
          <button type="button" onClick={() => setIsAttachmentsOpen(true)} className="btn-secondary">
            <Paperclip className="size-4" />
            Manage attachments
          </button>
        </div>

        {attachments.length === 0 ? (
          <EmptyState message="No attachments yet." />
        ) : (
          <ul className="divide-y divide-slate-800 rounded-lg border border-slate-800 bg-slate-900">
            {attachments.map((attachment) => (
              <li key={attachment.id} className="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                <div className="min-w-0">
                  <a
                    href={getAttachmentDownloadUrl(attachment.id)}
                    className="font-medium break-all text-slate-100 hover:underline"
                  >
                    {attachment.original_file_name}
                  </a>
                  {attachment.description && <p className="text-slate-400">{attachment.description}</p>}
                </div>
                <span className="text-slate-400">{formatDate(attachment.created_at)}</span>
              </li>
            ))}
          </ul>
        )}
      </section>

      <Modal isOpen={isFormOpen} title="Update edit" onClose={() => setIsFormOpen(false)}>
        <EditForm edit={edit} onSaved={() => setIsFormOpen(false)} />
      </Modal>

      <Modal isOpen={isAttachmentsOpen} title="Attachments" onClose={() => setIsAttachmentsOpen(false)}>
        <AttachmentsForm editId={edit.id} attachments={attachments} onClose={() => setIsAttachmentsOpen(false)} />
      </Modal>

      <DeleteEditDialog
        edit={isDeleteOpen ? edit : null}
        onClose={() => setIsDeleteOpen(false)}
        onDeleted={() => navigate(`/projects/${edit.project_id}`, { replace: true })}
      />
    </div>
  );
}
