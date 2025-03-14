<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Work extends Model
{

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'fixed_start_time',
        'fixed_end_time',
        'remarks',
    ];
}
