<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['created_by', 'direct_key'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function hasUser(int $userId): bool
    {
        return $this->users()->whereKey($userId)->exists();
    }

    public static function directKey(int $first, int $second): string
    {
        $ids = [$first, $second];
        sort($ids);

        return implode(':', $ids);
    }
}
