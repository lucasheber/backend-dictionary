<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $fillable = [
        'word',
        'definition',
        'lang',
    ];

    protected $casts = [
        'word' => 'string',
        'definition' => 'string',
        'lang' => 'string',
    ];
}
