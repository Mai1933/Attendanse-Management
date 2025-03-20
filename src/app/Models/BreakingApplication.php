<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakingApplication extends Model
{
    protected $fillable = [
        'user_id',
        'work_id',
        'start_time',
        'end_time',
    ];
}
