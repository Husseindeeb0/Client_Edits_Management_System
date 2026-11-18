/** AttachmentResource */
export interface Attachment {
  id: number;
  edit_id: number;
  original_file_name: string;
  description: string | null;
  /** ISO 8601 */
  created_at: string | null;
}

/** AttachmentListResource: GET /attachments */
export interface AttachmentListItem extends Attachment {
  edit_title: string | null;
}

/** StoreAttachmentRequest */
export interface UploadAttachmentsPayload {
  editId: number;
  files: File[];
  description?: string | null;
}
