<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    protected $table = 'trx_booking_detail';
    //protected $attributes = ['total_chargeable'];
    //protected $appends = ['total_chargeable'];

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function payment()
    {
        return $this->hasOne('App\Payment', 'booking_id', 'booking_id');
    }

    public function commodity()
    {
        return $this->hasOne('App\Commodities', 'id', 'package_commodity_id');
    }

}
