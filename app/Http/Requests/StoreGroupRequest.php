<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'auto_approval' => [
                'required',
                'boolean',
            ],

            'visibility' => [
                'required',
                'string',
                'in:public,private',
            ],

            'about' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}