<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrder extends Model
{
    protected $table = 'trx_mkt';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function details()
    {
        return $this->hasMany('App\MarketplaceOrderDetail', 'transaction_id', 'id');
    }
}
