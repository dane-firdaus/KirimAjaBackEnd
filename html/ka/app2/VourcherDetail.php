<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class VourcherDetail extends Model
{
    protected $table = 'mst_voucher_detail';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

}
