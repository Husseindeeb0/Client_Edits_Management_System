<?php

namespace App\Features\Edits\Requests;

use App\Features\Edits\DTOs\EditFiltersDTO;
use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use App\Helpers\PaginationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEditsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(PaginationHelper::rules(), [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::enum(EditStatus::class)],
            'priority' => ['nullable', 'string', Rule::enum(EditPriority::class)],
            'is_paid' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0'])],
            'project_id' => ['nullable', 'integer', 'min:1'],
            'client_id' => ['nullable', 'integer', 'min:1'],
            // Only edits that are still in progress (see EditStatus::active())
            'active' => ['nullable', 'string', Rule::in(['true', 'false', '1', '0'])],
        ]);
    }

    /**
     * Build the edit filters from the validated query parameters.
     */
    public function filters(): EditFiltersDTO
    {
        return new EditFiltersDTO(
            search: $this->validated('search'),
            status: $this->filled('status') ? EditStatus::from($this->validated('status')) : null,
            priority: $this->filled('priority') ? EditPriority::from($this->validated('priority')) : null,
            isPaid: $this->filled('is_paid') ? $this->boolean('is_paid') : null,
            projectId: $this->filled('project_id') ? (int) $this->validated('project_id') : null,
            clientId: $this->filled('client_id') ? (int) $this->validated('client_id') : null,
            activeOnly: $this->boolean('active'),
        );
    }
}
