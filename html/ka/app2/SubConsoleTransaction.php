<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class SubConsoleTransaction extends Model
{
    protected $table = 'trx_subconsole';

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }

    public function deliveryPoint()
    {
        return $this->hasOne('App\DeliveryPoint', 'id', 'delivery_point_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
