<?php

namespace App\Features\Attachments\Services;

use App\Features\Attachments\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentStorageService
{
    /**
     * Delete an array of attachment file paths from the private local disk.
     * Missing files are ignored.
     *
     * @param array<int, string|null> $filePaths
     */
    public function deleteFiles(array $filePaths): void
    {
        $filtered = array_values(array_filter($filePaths));

        if (! empty($filtered)) {
            Storage::disk('local')->delete($filtered);
        }
    }

    /**
     * Delete an attachment record, then its physical file. The row is removed
     * first so a storage failure can only leave an orphaned file, never a
     * record pointing to a missing file.
     */
    public function deleteAttachmentRecordAndFile(Attachment $attachment): void
    {
        $filePath = $attachment->file_path;

        $attachment->delete();

        $this->deleteFiles([$filePath]);
    }
}
