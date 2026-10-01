<?php

namespace App\Http\Requests;

use App\Enums\GroupUserStatus;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class InviteUsersRequest extends FormRequest
{
    private ?User $invitedUser = null;

    public function authorize(): bool
    {
        /** @var Group $group */
        $group = $this->route('group');

        return $this->user() !== null
            && $group->isAdmin($this->user()->id);
    }

    public function rules(): array
    {
        return [
            'identifier' => [
                'required',
                'string',
                'max:255',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {
                    $this->invitedUser =
                        User::query()
                            ->where('email', $value)
                            ->orWhere('username', $value)
                            ->first();

                    if (!$this->invitedUser) {
                        $fail(
                            'No user with that username or email exists.'
                        );

                        return;
                    }

                    if (
                        $this->invitedUser->id ===
                        $this->user()->id
                    ) {
                        $fail(
                            'You cannot invite yourself.'
                        );

                        return;
                    }

                    $membership =
                        GroupUser::query()
                            ->where(
                                'user_id',
                                $this->invitedUser->id
                            )
                            ->where(
                                'group_id',
                                $this->route('group')->id
                            )
                            ->first();

                    if (
                        $membership?->status ===
                        GroupUserStatus::APPROVED->value
                    ) {
                        $fail(
                            'This user is already a member of the group.'
                        );
                    }
                },
            ],
        ];
    }

    public function invitedUser(): User
    {
        return $this->invitedUser;
    }
}