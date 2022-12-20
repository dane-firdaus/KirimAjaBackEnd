<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AirWaybill extends Model
{
    protected $table = 'mst_awb';

    protected $guarded = [
        'id','created_at','updated_at'
    ];

    protected $hidden = [
        'id','created_at','updated_at'
    ];
}
