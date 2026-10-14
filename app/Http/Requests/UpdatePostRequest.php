<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\TotalAttachmentSize;
use App\Models\PostCollaborator;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        if (!$post || !Auth::id()) {
            return false;
        }

        if ($post->user_id === Auth::id()) {
            return true;
        }

        return $post->type === 'poem'
            && PostCollaborator::query()
                ->where('post_id', $post->id)
                ->where('user_id', Auth::id())
                ->where('status', 'accepted')
                ->exists();
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

    public function after(): array
    {
        return [
            function ($validator) {
                $post = $this->route('post');

                if (!$post) {
                    return;
                }

                $deletedIds = collect($this->input('deleted_file_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $remaining = $post->attachments()
                    ->whereNotIn('id', $deletedIds)
                    ->get(['id', 'size']);

                $newFiles = collect($this->file('attachments', []));

                if ($remaining->count() + $newFiles->count() > 10) {
                    $validator->errors()->add(
                        'attachments',
                        'A publication may have at most 10 attachments in total.'
                    );
                }

                $remainingBytes = (int) $remaining->sum('size');
                $newBytes = (int) $newFiles->sum(
                    fn ($file) => $file?->getSize() ?? 0
                );

                if ($remainingBytes + $newBytes > 90 * 1024 * 1024) {
                    $validator->errors()->add(
                        'attachments',
                        'The total stored attachment size must not exceed 90 MB.'
                    );
                }

                $plainBody = trim(strip_tags((string) $this->input('body', '')));

                if ($plainBody === '' && $remaining->isEmpty() && $newFiles->isEmpty()) {
                    $validator->errors()->add(
                        'body',
                        'Add some text or at least one attachment before publishing.'
                    );
                }
            },
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
