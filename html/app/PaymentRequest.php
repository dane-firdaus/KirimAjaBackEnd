<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $table = 'trx_payment_request';
    //20210323 - TID: U9LgjemB - KIBAR

    protected $fillable = [
        'booking_id', 'transid', 'status_code','va_number','paid','paid_at','paid_channel','transaction_amount','cart_ids','created_at'
    ];

    protected $hidden = [
      'updated_at'
    ];

   
    //20210323 - TID: U9LgjemB - KIBAR

    public function payment()
    {
        return $this->hasOne('App\Payment', 'booking_id', 'booking_id');
    }
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
