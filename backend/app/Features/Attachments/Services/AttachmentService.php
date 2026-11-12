<?php

namespace App\Features\Attachments\Services;

use App\Features\Attachments\DTOs\UploadAttachmentsDTO;
use App\Features\Attachments\Models\Attachment;
use App\Features\Edits\Models\Edit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentService
{
    public function __construct(
        protected AttachmentStorageService $attachmentStorageService,
    ) {
    }

    /**
     * Retrieve all attachments for an edit.
     *
     * @return Collection<int, Attachment>
     */
    public function getAttachmentsByEdit(Edit $edit): Collection
    {
        return $edit->attachments()->latest('id')->get();
    }

    /**
     * Upload one or multiple attachments to an edit.
     *
     * @return array<int, Attachment>
     */
    public function uploadAttachments(Edit $edit, UploadAttachmentsDTO $dto): array
    {
        $createdAttachments = [];
        $storedPaths = [];

        try {
            DB::transaction(function () use ($edit, $dto, &$createdAttachments, &$storedPaths) {
                foreach ($dto->files as $file) {
                    // Store file in private local disk under 'attachments/{year}/{month}'
                    $path = $file->store('attachments/'.date('Y/m'), 'local');
                    $storedPaths[] = $path;

                    $attachment = Attachment::create([
                        'edit_id'            => $edit->id,
                        'file_path'          => $path,
                        'original_file_name' => $file->getClientOriginalName(),
                        'description'        => $dto->description,
                    ]);

                    $createdAttachments[] = $attachment;
                }
            });
        } catch (\Throwable $e) {
            // Roll back any physical files written to disk if the DB transaction failed
            $this->attachmentStorageService->deleteFiles($storedPaths);
            throw $e;
        }

        return $createdAttachments;
    }

    /**
     * Download an attachment file securely.
     */
    public function downloadAttachment(Attachment $attachment): StreamedResponse
    {
        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Attachment file not found on storage.');
        }

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->original_file_name
        );
    }

    /**
     * Delete an individual attachment record and its physical file.
     */
    public function deleteAttachment(Attachment $attachment): void
    {
        $this->attachmentStorageService->deleteAttachmentRecordAndFile($attachment);
    }
}

