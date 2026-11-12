<?php

namespace App\Features\Attachments\Services;

use App\Features\Attachments\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentStorageService
{
    /**
     * Delete an array of attachment file paths from the private local disk.
     *
     * @param array<int, string> $filePaths
     */
    public function deleteFiles(array $filePaths): void
    {
        $filtered = array_filter($filePaths);
        if (! empty($filtered)) {
            Storage::disk('local')->delete($filtered);
        }
    }

    /**
     * Delete an attachment model record and its physical file safely.
     */
    public function deleteAttachmentRecordAndFile(Attachment $attachment): void
    {
        $filePath = $attachment->file_path;

        $attachment->delete();

        if ($filePath && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }
    }
}

