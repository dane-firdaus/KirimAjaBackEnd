<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'trx_payment';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function booking()
    {
        return $this->hasOne('App\Booking', 'id', 'booking_id');
    }
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    
}
