<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'         => ['required', 'string', 'min:1', 'max:10000'],
            'user_id'      => ['numeric'],
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'max:255'],
            'published_at' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation()
    {
        $body = $this->input('body', '');
        $title = Str::limit($body, 50);
        $slug = Str::slug($title) . '-' . uniqid();

        $this->merge([
            'user_id'      => auth()->id(),
            'title'        => $title,
            'slug'         => $slug,
            'published_at' => now(),
        ]);
    }
}