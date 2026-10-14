<?php

namespace App\Models;

use App\Enums\GroupUserStatus;
use App\Support\RichTextSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'content_rating',
        'poem_form',
        'poem_genres',
        'title',
        'caption',
        'hashtags',
        'status',
        'published_at',
        'draft_saved_at',
    ];

    protected function casts(): array
    {
        return [
            'hashtags' => 'array',
            'poem_genres' => 'array',
            'published_at' => 'datetime',
            'draft_saved_at' => 'datetime',
        ];
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => RichTextSanitizer::sanitize($value),
            set: fn ($value) => RichTextSanitizer::sanitize($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
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
        return $this->morphMany(Reaction::class, 'object');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(PostCollaborator::class);
    }

    public function isVisibleTo(?int $userId): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->group_id === null) {
            return true;
        }

        $group = $this->group()->first();

        if (!$group) {
            return false;
        }

        if ($group->visibility === 'public') {
            return true;
        }

        if (!$userId) {
            return false;
        }

        return $group->hasApprovedUser($userId);
    }

    public static function postsForTimeline(?int $userId): Builder
    {
        return static::query()
            ->where('posts.status', 'published')
            ->where(function (Builder $query) use ($userId) {
                $query
                    ->whereNull('posts.group_id')
                    ->orWhereHas(
                        'group',
                        fn (Builder $groupQuery) =>
                            $groupQuery->where(
                                'visibility',
                                'public'
                            )
                    );

                if ($userId) {
                    $query->orWhereHas(
                        'group.groupUsers',
                        fn (Builder $membershipQuery) =>
                            $membershipQuery
                                ->where('user_id', $userId)
                                ->where(
                                    'status',
                                    GroupUserStatus::APPROVED->value
                                )
                    );
                }
            })
            ->with([
                'user',
                'group',
                'group.currentUserGroup',
                'attachments',
                'bookmarks' => function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
                'collaborators' => function ($query) use ($userId) {
                    if ($userId) {
                        $query
                            ->where('user_id', $userId)
                            ->where('status', 'accepted');
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
            ])
            ->withCount('reactions')
            ->with([
                'comments' => function ($query) use ($userId) {
                    $query
                        ->with('user')
                        ->withCount('reactions')
                        ->with([
                            'reactions' => function ($query) use ($userId) {
                                if ($userId) {
                                    $query->where('user_id', $userId);
                                } else {
                                    $query->whereRaw('1 = 0');
                                }
                            },
                        ]);
                },
                'reactions' => function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
            ])
            ->latest();
    }
}
