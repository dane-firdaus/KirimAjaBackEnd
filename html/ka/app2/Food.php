<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'mst_food_mkt';

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
