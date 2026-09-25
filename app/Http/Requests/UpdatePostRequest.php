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

            'deleted_file_ids' => [
                'nullable',
                'array'
            ],

            'deleted_file_ids.*' => [
                'integer'
            ],
        ];
    }
}
