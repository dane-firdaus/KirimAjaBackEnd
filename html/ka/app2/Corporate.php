<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Corporate extends Model
{
    protected $table = 'mst_corporate';
    
    protected $hidden = [
        'updated_at'
    ];
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s'
    ];
}
