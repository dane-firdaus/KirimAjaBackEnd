<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerBooking extends Model
{
    protected $table = 'trx_customer_booking';
    
    public function sohib()
    {
        return $this->hasOne('App\User', 'id', 'booking_delivery_point_id');
    }

    public function details()
    {
        return $this->hasMany('App\CustomerBookingDetail', 'booking_id', 'id');
    }

    public function getTotalDetails()
    {
        return $this->details()->count();
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
