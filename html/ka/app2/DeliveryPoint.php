<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryPoint extends Model
{
    protected $table = 'mst_delivery_point';

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
