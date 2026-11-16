<?php

namespace App\Features\Attachments\Controllers;

use App\Features\Attachments\DTOs\UploadAttachmentsDTO;
use App\Features\Attachments\Models\Attachment;
use App\Features\Attachments\Requests\StoreAttachmentRequest;
use App\Features\Attachments\Resources\AttachmentResource;
use App\Features\Attachments\Services\AttachmentService;
use App\Features\Edits\Models\Edit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(
        protected AttachmentService $attachmentService,
    ) {
    }

    /**
     * Retrieve all attachments for a specific edit.
     */
    public function getAttachmentsByEdit(Edit $edit): AnonymousResourceCollection
    {
        $attachments = $this->attachmentService->getAttachmentsByEdit($edit);

        return AttachmentResource::collection($attachments);
    }

    /**
     * Upload one or more attachments to an edit.
     */
    public function uploadAttachments(StoreAttachmentRequest $request, Edit $edit): JsonResponse
    {
        $dto = new UploadAttachmentsDTO(
            files: $request->file('files'),
            description: $request->validated('description'),
        );

        $attachments = $this->attachmentService->uploadAttachments($edit, $dto);

        return AttachmentResource::collection($attachments)
            ->additional(['message' => 'Attachments uploaded successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Download an attachment file.
     */
    public function downloadAttachment(Attachment $attachment): StreamedResponse
    {
        return $this->attachmentService->downloadAttachment($attachment);
    }

    /**
     * Delete an individual attachment.
     */
    public function deleteAttachment(Attachment $attachment): JsonResponse
    {
        $this->attachmentService->deleteAttachment($attachment);

        return response()->json([
            'message' => 'Attachment deleted successfully.',
        ]);
    }
}

