<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ReadingList extends Model
{
    protected $fillable = ['user_id', 'name', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'reading_list_post')
            ->withTimestamps();
    }
}
