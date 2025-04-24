<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $casts = [
        'word' => 'string',
        'definition' => 'string',
        'lang' => 'string',
    ];
}
