<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'log_activity';

    protected $fillable = [
        'username', 'activity', 'param'
    ];

    protected $hidden = [
        'created_at','updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
