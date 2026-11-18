<?php

namespace App\Features\Attachments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Maximum number of files accepted in a single upload request.
     */
    public const MAX_FILES = 10;

    /**
     * Maximum size of a single file, in kilobytes (10 MB).
     */
    public const MAX_FILE_SIZE_KB = 10240;

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
     * The `mimes` rule checks the type detected from the file's content, not
     * only its extension. SVG is intentionally not allowed because it can
     * carry embedded scripts.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'files.*' => [
                'required',
                'file',
                'max:'.self::MAX_FILE_SIZE_KB,
                'mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,gif,webp,zip',
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
