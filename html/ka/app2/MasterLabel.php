<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterLabel extends Model
{
    protected $table = 'mst_label';

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
