<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\GroupUserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'user_id',
        'body',
        'group_id',
        'type',
        'title',
        'caption',
        'hashtags',
    ];

    protected function casts(): array
    {
        return [
            'hashtags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(
            Reaction::class,
            'object'
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->latest();
    }

    public static function postsForTimeline(
        int $userId
    ): Builder {
        return static::query()

            /*
            * Normal posts are visible.
            *
            * Group posts are only visible if
            * the current user is an approved
            * member of that group.
            */
            ->where(
                function (Builder $query) use (
                    $userId
                ) {
                    $query
                        ->whereNull('group_id')

                        ->orWhereHas(
                            'group.groupUsers',
                            function (
                                Builder $membershipQuery
                            ) use ($userId) {
                                $membershipQuery
                                    ->where(
                                        'user_id',
                                        $userId
                                    )
                                    ->where(
                                        'status',
                                        GroupUserStatus::APPROVED->value
                                    );
                            }
                        );
                }
            )

            ->with([
                'user',
                'group',
                'group.currentUserGroup',
                'attachments',
            ])

            ->withCount('reactions')

            ->with([
                'comments' =>
                    function ($query) use (
                        $userId
                    ) {
                        $query
                            ->with('user')
                            ->withCount(
                                'reactions'
                            )
                            ->with([
                                'reactions' =>
                                    function (
                                        $query
                                    ) use (
                                        $userId
                                    ) {
                                        $query->where(
                                            'user_id',
                                            $userId
                                        );
                                    },
                            ]);
                    },

                'reactions' =>
                    function ($query) use (
                        $userId
                    ) {
                        $query->where(
                            'user_id',
                            $userId
                        );
                    },
            ])

            ->latest();
    }
}