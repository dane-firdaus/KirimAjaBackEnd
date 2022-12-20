<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $table = 'trx_voucher_usage';

    //20210224 - TID: 3B23WByr - START
    public function master()
    {
        return $this->hasOne('App\VourcherData', 'id', 'voucher_id');
    }
    //20210224 - TID: 3B23WByr - END

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }

}
