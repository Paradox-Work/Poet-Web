<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post && $post->user_id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => [
                'nullable',
                'string'
            ],

            'attachments' => [
                'nullable',
                'array',
                'max:10'
            ],

            'attachments.*' => [
                'file',

                File::types(
                    StorePostRequest::$extensions
                )->max('25mb')
            ],

            'deleted_file_ids' => [
                'nullable',
                'array'
            ],

            'deleted_file_ids.*' => [
                'integer'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.max' =>
                'You may upload at most 10 attachments.',

            'attachments.*.file' =>
                'The selected attachment is not a valid file.',

            'attachments.*.mimes' =>
                'This file type is not allowed.',

            'attachments.*.max' =>
                'Each attachment must be 25 MB or smaller.',
        ];
    }

}
