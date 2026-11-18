// Same limits as the backend's StoreAttachmentRequest
const MAX_FILES = 10;
const MAX_FILE_SIZE_KB = 20480;
const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip'];

/** For the file input's `accept`, so the file picker only shows allowed types. */
export const ATTACHMENT_ACCEPT = ALLOWED_EXTENSIONS.map((extension) => `.${extension}`).join(',');

/** Checks the files queued for one upload; returns the first problem, or null when all are fine. */
export function validateAttachmentFiles(files: File[]): string | null {
  if (files.length > MAX_FILES) {
    return `You can upload at most ${MAX_FILES} files at once.`;
  }

  for (const file of files) {
    const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

    if (!ALLOWED_EXTENSIONS.includes(extension)) {
      return `"${file.name}" is not an allowed file type.`;
    }

    if (file.size > MAX_FILE_SIZE_KB * 1024) {
      return `"${file.name}" is larger than ${MAX_FILE_SIZE_KB / 1024} MB.`;
    }
  }

  return null;
}
