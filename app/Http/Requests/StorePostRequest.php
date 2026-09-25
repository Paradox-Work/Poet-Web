<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;
class StorePostRequest extends FormRequest
{
    public static array $extensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',

        'mp3',
        'wav',
        'mp4',

        'doc',
        'docx',
        'pdf',
        'csv',
        'xls',
        'xlsx',
        'zip',
    ];

    public function authorize(): bool
    {
        return true;
    }

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

                File::types(self::$extensions)
                    ->max('25mb')
            ],

            'user_id' => [
                'numeric'
            ]
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

    protected function prepareForValidation()
    {
        // Add your custom key to the request data
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
}