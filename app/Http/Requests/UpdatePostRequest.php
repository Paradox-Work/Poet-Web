<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\TotalAttachmentSize;

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
            'type' => [
                'required',
                'string',
                'in:post,poem',
            ],

            'content_rating' => [
                'required',
                'string',
                'in:general,mature',
            ],

            'poem_form' => [
                'nullable',
                'string',
                'in:free_verse,haiku,tanka,shakespearean_sonnet,petrarchan_sonnet,limerick,villanelle,sestina,ballad,ode,elegy,acrostic,cinquain,ghazal,pantoum,rondeau,blank_verse,prose_poem',
            ],

            'poem_genres' => [
                'nullable',
                'array',
                'max:8',
            ],

            'poem_genres.*' => [
                'string',
                'max:50',
            ],

            'title' => [
                'nullable',
                'string',
                'max:160',
            ],

            'caption' => [
                'nullable',
                'string',
                'max:500',
            ],

            'hashtags' => [
                'nullable',
                'array',
                'max:10',
            ],

            'hashtags.*' => [
                'string',
                'max:50',
                'regex:/^[\\pL\\pN_]+$/u',
            ],

            'body' => [
                'nullable',
                'string'
            ],
            
            'attachments' => [
                'nullable',
                'array',
                'max:10',
                new TotalAttachmentSize(90),
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
