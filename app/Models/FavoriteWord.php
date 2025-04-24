<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteWord extends Model
{
    /**
     * User that owns the favorite word.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Dictionary that owns the favorite word.
     */
    public function dictionary(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class, 'dictionary_id');
    }
}
