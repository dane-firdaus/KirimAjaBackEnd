<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class VourcherData extends Model
{
    protected $table = 'mst_voucher';

    public function usage()
    {
        return $this->hasOne('App\VoucherUsage', 'voucher_id', 'id');
    }

    public function voucherdetail()
    {
        return $this->hasMany('App\VourcherDetail', 'voucher_id', 'id');  // t10 detail voucher
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }
}
