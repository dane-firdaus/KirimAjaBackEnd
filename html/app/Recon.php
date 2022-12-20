<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recon extends Model
{
    protected $table = 'recon';

    protected $guarded = [
        'id'
    ];

    public function paymentRequest()
    {
        return $this->hasOne('App\PaymentRequest', 'transid', 'invoice_no')->select(['id','booking_id','transid','cart_ids']);
    }
}
