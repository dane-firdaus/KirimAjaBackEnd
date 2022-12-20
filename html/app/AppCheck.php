<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppCheck extends Model
{
    protected $table = 'mst_app_check';

    protected $hidden = [
        'id', 'updated_at'
    ];
}
