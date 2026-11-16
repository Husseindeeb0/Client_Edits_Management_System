<?php

namespace App\Features\Edits\Controllers;

use App\Features\Edits\DTOs\CreateEditDTO;
use App\Features\Edits\DTOs\UpdateEditDTO;
use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use App\Features\Edits\Models\Edit;
use App\Features\Edits\Requests\ListEditsRequest;
use App\Features\Edits\Requests\StoreEditRequest;
use App\Features\Edits\Requests\UpdateEditRequest;
use App\Features\Edits\Resources\EditDetailsResource;
use App\Features\Edits\Resources\EditListResource;
use App\Features\Edits\Resources\EditResource;
use App\Features\Edits\Services\EditService;
use App\Helpers\PaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class EditController extends Controller
{
    public function __construct(
        protected EditService $editService,
    ) {
    }

    /**
     * Retrieve a paginated list of edits with optional filtering.
     */
    public function getPaginatedEdits(ListEditsRequest $request): AnonymousResourceCollection
    {
        $perPage = PaginationHelper::resolvePerPage($request->validated('per_page'));

        $edits = $this->editService->getPaginatedEdits($request->filters(), $perPage);

        return EditListResource::collection($edits);
    }

    /**
     * Store a newly created edit.
     */
    public function createEdit(StoreEditRequest $request): JsonResponse
    {
        $dto = new CreateEditDTO(
            projectId: (int) $request->validated('project_id'),
            title: $request->validated('title'),
            description: $request->validated('description'),
            priority: EditPriority::from($request->validated('priority')),
            status: EditStatus::from($request->validated('status')),
            price: $request->validated('price') !== null ? (float) $request->validated('price') : null,
            isPaid: (bool) $request->validated('is_paid', false),
            dueDate: $request->validated('due_date'),
        );

        $edit = $this->editService->createEdit($dto);

        return (new EditResource($edit))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display edit details including project and attachments.
     */
    public function getEditDetails(Edit $edit): EditDetailsResource
    {
        $editWithDetails = $this->editService->getEditDetails($edit);

        return new EditDetailsResource($editWithDetails);
    }

    /**
     * Update an edit partially.
     */
    public function updateEdit(UpdateEditRequest $request, Edit $edit): EditResource
    {
        $dto = new UpdateEditDTO($request->validated());
        $updatedEdit = $this->editService->updateEdit($edit, $dto);

        return new EditResource($updatedEdit);
    }

    /**
     * Remove an edit.
     */
    public function deleteEdit(Edit $edit): JsonResponse
    {
        $this->editService->deleteEdit($edit);

        return response()->json([
            'message' => 'Edit deleted successfully.',
        ]);
    }
}
