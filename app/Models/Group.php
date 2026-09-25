<?php

namespace App\Models;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasSlug;

    protected $fillable = [
        'name',
        'user_id',
        'auto_approval',
        'about',
        'cover_path',
        'thumbnail_path',
    ];

    protected function casts(): array
    {
        return [
            'auto_approval' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function groupUsers(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    public function isAdmin(int $userId): bool
    {
        return $this->groupUsers()
            ->where('user_id', $userId)
            ->where(
                'role',
                GroupUserRole::ADMIN->value
            )
            ->where(
                'status',
                GroupUserStatus::APPROVED->value
            )
            ->exists();
    }
}