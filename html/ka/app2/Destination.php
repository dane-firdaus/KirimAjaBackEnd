<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $table = 'mst_destination';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
