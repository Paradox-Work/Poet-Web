<?php

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');

        return $this->user() !== null
            && $group->isAdmin(
                $this->user()->id
            );
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

            'about' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}