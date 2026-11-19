<?php

namespace App\Features\Edits\Requests;

use App\Features\Edits\Enums\EditPriority;
use App\Features\Edits\Enums\EditStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEditRequest extends FormRequest
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
            'project_id' => ['sometimes', 'required', 'integer', 'exists:projects,project_id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'required', 'string', Rule::enum(EditPriority::class)],
            'status' => ['sometimes', 'required', 'string', Rule::enum(EditStatus::class)],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'is_paid' => ['sometimes', 'required', 'boolean'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $intersect = array_intersect_key($this->all(), $this->rules());

            if (empty($intersect)) {
                $validator->errors()->add('payload', 'At least one field must be provided for update.');
            }
        });
    }
}

