<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingApplication extends Model
{
    protected $fillable = [
        'user_id',
        'work_id',
        'start_time',
        'end_time',
        'remarks',
        'status',
    ];
}
