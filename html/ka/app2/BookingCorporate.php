<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BookingCorporate extends Model
{
    protected $table = 'trx_booking_corporate';

    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone('Asia/Jakarta')
            ->toDateTimeString()
        ;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone('Asia/Jakarta')
            ->toDateTimeString()
        ;
    }

    // Relation

    public function detail()
    {
        return $this->hasOne('App\BookingDetailCorporate', 'booking_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne('App\CorporatePayment', 'booking_id', 'id');
    }

    public function corporate()
    {
        return $this->hasOne('App\Corporate', 'id', 'corporate_id');
    }
}
