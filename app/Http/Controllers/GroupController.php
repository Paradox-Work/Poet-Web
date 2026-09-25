<?php

namespace App\Http\Controllers;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\GroupUser;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        $group = DB::transaction(
            function () use ($data, $user) {

                $group = Group::create([
                    ...$data,
                    'user_id' => $user->id,
                ]);

                GroupUser::create([
                    'status' =>
                        GroupUserStatus::APPROVED->value,

                    'role' =>
                        GroupUserRole::ADMIN->value,

                    'user_id' =>
                        $user->id,

                    'group_id' =>
                        $group->id,

                    'created_by' =>
                        $user->id,
                ]);

                $group->status =
                    GroupUserStatus::APPROVED->value;

                $group->role =
                    GroupUserRole::ADMIN->value;

                return $group;
            }
        );

        return (
            new GroupResource($group)
        )
            ->response()
            ->setStatusCode(201);
    }
}