<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breaking extends Model
{
    protected $fillable = [
        'user_id',
        'work_id',
        'start_time',
        'end_time',
        'fixed_start_time',
        'fixed_end_time',
    ];
}
