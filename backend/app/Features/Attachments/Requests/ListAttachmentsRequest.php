<?php

namespace App\Features\Attachments\Requests;

use App\Helpers\PaginationHelper;
use Illuminate\Foundation\Http\FormRequest;

class ListAttachmentsRequest extends FormRequest
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
        ]);
    }
}
