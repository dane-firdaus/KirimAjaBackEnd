<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $table = 'mst_service_type';
    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
