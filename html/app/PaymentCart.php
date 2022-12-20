<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentCart extends Model
{
    protected $table = 'trx_cart_payment';

    protected $fillable = [
        'id','user_id', 'booking_id', 'cart_status' //20210323 - TID: U9LgjemB - KIBAR
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }
}
