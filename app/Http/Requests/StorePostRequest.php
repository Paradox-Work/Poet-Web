<?php

namespace App\Http\Requests;

use App\Enums\GroupUserStatus;
use App\Models\GroupUser;
use Closure;
use App\Rules\TotalAttachmentSize;
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

                File::types(self::$extensions)
                    ->max('25mb')
            ],

            'user_id' => [
                'numeric'
            ],

            'group_id' => [
                'nullable',
                'integer',
                'exists:groups,id',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {
                    if ($value === null) {
                        return;
                    }

                    $user = $this->user();

                    $approvedMember =
                        $user &&
                        GroupUser::query()
                            ->where(
                                'user_id',
                                $user->id
                            )
                            ->where(
                                'group_id',
                                $value
                            )
                            ->where(
                                'status',
                                GroupUserStatus::APPROVED->value
                            )
                            ->exists();

                    if (!$approvedMember) {
                        $fail(
                            "You don't have permission to create posts in this group."
                        );
                    }
                },
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

    protected function prepareForValidation()
    {
        // Add your custom key to the request data
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
}