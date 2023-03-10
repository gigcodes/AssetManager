<?php

namespace Gigcodes\AssetManager\Http\Requests;

use Gigcodes\AssetManager\Rules\DisabledMimetypes;
use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'filename' => 'required|string',
            'parent_id' => 'nullable',
            'collection_name' => 'required|exists:media_collections,name',
            'path' => 'required|string',
            'extension' => 'sometimes|string|nullable',
            'file' => ['required', 'file', new DisabledMimetypes],
        ];
    }
}
