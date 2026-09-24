<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;
class StorePostRequest extends FormRequest
{
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

                File::types([
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
                ])->max('25mb')
            ],

            'user_id' => [
                'numeric'
            ]
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