<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingExceed extends Model
{
    protected $table = 'trx_booking_exceed_weight';

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }
}
