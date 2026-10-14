<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $comment = $this->route('comment');
        $userId = $this->user()?->id;

        return $comment
            && $userId
            && $comment->user_id === $userId
            && $comment->post?->isVisibleTo($userId);
    }

    public function rules(): array
    {
        return [
            'comment' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }
}
