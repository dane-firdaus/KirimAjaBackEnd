<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerBookingDetail extends Model
{
    protected $table = 'trx_customer_booking_detail';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
