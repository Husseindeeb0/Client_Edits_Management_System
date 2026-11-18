import { useState } from 'react';
import type { ChangeEvent } from 'react';
import { useDeleteAttachmentMutation, useUploadAttachmentsMutation } from '../../client/attachmentApi';
import { getErrorMessage } from '../../errors/apiError';
import type { Attachment } from '../../types/attachment';
import { ATTACHMENT_ACCEPT, validateAttachmentFiles } from '../../validators/attachmentValidator';
import FormField from '../SharedComponents/FormField';

interface AttachmentsFormProps {
  editId: number;
  attachments: Attachment[];
  onClose: () => void;
}

/**
 * Queue new files and removals, then apply them on Save: upload first, then
 * delete, so a failed upload never leaves the edit with fewer files than before.
 */
export default function AttachmentsForm({ editId, attachments, onClose }: AttachmentsFormProps) {
  const [newFiles, setNewFiles] = useState<File[]>([]);
  const [removedIds, setRemovedIds] = useState<number[]>([]);
  const [description, setDescription] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);

  const [uploadAttachments] = useUploadAttachmentsMutation();
  const [deleteAttachment] = useDeleteAttachmentMutation();

  function handleFilesChosen(event: ChangeEvent<HTMLInputElement>) {
    const files = [...newFiles, ...Array.from(event.target.files ?? [])];
    event.target.value = ''; // lets the same file be chosen again after removing it

    const fileError = validateAttachmentFiles(files);
    setError(fileError);
    if (!fileError) {
      setNewFiles(files);
    }
  }

  function toggleRemoved(id: number) {
    setRemovedIds(removedIds.includes(id) ? removedIds.filter((removedId) => removedId !== id) : [...removedIds, id]);
  }

  async function handleSave() {
    setIsSaving(true);
    setError(null);

    if (newFiles.length > 0) {
      const result = await uploadAttachments({ editId, files: newFiles, description: description.trim() || null });
      if (result.error) {
        setError(getErrorMessage(result.error));
        setIsSaving(false);
        return;
      }
      setNewFiles([]); // uploaded; don't send them again if a delete below fails
    }

    for (const id of removedIds) {
      const result = await deleteAttachment({ id, editId });
      if (result.error) {
        setError(getErrorMessage(result.error));
        setIsSaving(false);
        return;
      }
    }

    onClose();
  }

  return (
    <div className="space-y-5">
      <section>
        <h3 className="mb-2 text-sm font-medium text-slate-300">Current files</h3>
        {attachments.length === 0 ? (
          <p className="text-sm text-slate-400">No files yet.</p>
        ) : (
          <ul className="divide-y divide-slate-800 rounded-md border border-slate-800">
            {attachments.map((attachment) => {
              const isRemoved = removedIds.includes(attachment.id);

              return (
                <li key={attachment.id} className="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                  <span className={`truncate ${isRemoved ? 'text-slate-500 line-through' : 'text-slate-300'}`}>
                    {attachment.original_file_name}
                  </span>
                  <button
                    type="button"
                    onClick={() => toggleRemoved(attachment.id)}
                    className={isRemoved ? 'text-slate-400 hover:text-white' : 'text-red-400 hover:text-red-300'}
                  >
                    {isRemoved ? 'Undo' : 'Remove'}
                  </button>
                </li>
              );
            })}
          </ul>
        )}
      </section>

      <section className="space-y-3">
        <FormField label="Add files" htmlFor="files">
          <input
            id="files"
            type="file"
            multiple
            accept={ATTACHMENT_ACCEPT}
            onChange={handleFilesChosen}
            className="block w-full text-sm text-slate-400"
          />
        </FormField>

        {newFiles.length > 0 && (
          <>
            <ul className="divide-y divide-slate-800 rounded-md border border-slate-800">
              {newFiles.map((file, index) => (
                <li key={`${file.name}-${index}`} className="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                  <span className="truncate text-slate-300">{file.name}</span>
                  <button
                    type="button"
                    onClick={() => setNewFiles(newFiles.filter((_, fileIndex) => fileIndex !== index))}
                    className="text-red-400 hover:text-red-300"
                  >
                    Remove
                  </button>
                </li>
              ))}
            </ul>

            <FormField label="Description for the new files (optional)" htmlFor="attachment-description">
              <textarea
                id="attachment-description"
                rows={2}
                value={description}
                onChange={(event) => setDescription(event.target.value)}
                className="form-input"
              />
            </FormField>
          </>
        )}
      </section>

      {error && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {error}
        </p>
      )}

      <div className="flex justify-end gap-2">
        <button type="button" onClick={onClose} className="btn-secondary">
          Cancel
        </button>
        <button
          type="button"
          onClick={handleSave}
          disabled={isSaving || (newFiles.length === 0 && removedIds.length === 0)}
          className="btn-primary"
        >
          Save
        </button>
      </div>
    </div>
  );
}
