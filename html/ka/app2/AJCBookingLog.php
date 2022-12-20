<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AJCBookingLog extends Model
{
    protected $table = 'trx_ajc_booking';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'parameter', 'response'
    ];

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }
}
