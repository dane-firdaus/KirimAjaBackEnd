<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;
    protected $table = 'trx_booking';

    protected $hidden = [
        'deleted_at'
    ];
    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function details()
    {
        return $this->hasMany('App\BookingDetail');
    }

    public function getTotalDetails()
    {
        return $this->details()->count();
    }

    public function payment()
    {
        return $this->hasOne('App\Payment');
    }

    public function deliveryPoint()
    {
        return $this->hasOne('App\DeliveryPoint', 'id', 'booking_delivery_point_id');
    }

    public function subConsole()
    {
        return $this->hasOne('App\User', 'id', 'booking_delivery_point_id');
    }

    public function validInfo()
    {
        return $this->hasOne('App\SubConsoleTransaction', 'booking_id', 'id');
    }

    public function shipment()
    {
        return $this->hasOne('App\AJCBookingLog');
    }

    public function exceed()
    {
        return $this->hasOne('App\BookingExceed', 'booking_id', 'id');
    }

    public function getBookingCodeAttribute($value)
    {
        return strtoupper($value);
    }

    public function paymentRequest()
    {
        return $this->hasMany('App\PaymentRequest', 'booking_id', 'id');
    }

    public function cart()
    {
        //return $this->hasOne('App\PaymentCart', 'booking_id', 'id');
        return $this->hasOne('App\PaymentCart', 'booking_id', 'id')->where('cart_status',1); //20210323 - TID: U9LgjemB - KIBAR
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    //20210224 - TID: 3B23WByr - START
    public function voucher_usage()
    {
        return $this->hasOne('App\VoucherUsage', 'id', 'booking_id');
    }
    public function voucher()
    {
        return $this->hasOne('App\VourcherData', 'id', 'voucher_id');
    }
    //20210224 - TID: 3B23WByr - END

}
