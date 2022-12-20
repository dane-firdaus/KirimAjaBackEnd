<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrderDetail extends Model
{
    protected $table = 'trx_mkt_detail';

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function product()
    {
        return $this->hasOne('App\Food', 'id', 'product_id');
    }
}
