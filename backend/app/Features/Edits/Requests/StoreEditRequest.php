<?php

namespace App\Features\Edits\Requests;

use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEditRequest extends FormRequest
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
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', Rule::enum(EditPriority::class)],
            'status' => ['required', 'string', Rule::enum(EditStatus::class)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'is_paid' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}

