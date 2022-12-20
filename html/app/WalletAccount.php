<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletAccount extends Model
{
    protected $table = 'mst_wallet_account';

    protected $hidden = [
        'created_at', 'updated_at', 'partner_token'
    ];

    protected $fillable = [
        'user_id', 'phone_number', 'account_number', 'partner_token', 'first_name', 'last_name', 'forget_pin', 'forget_pin_validity'
    ];
}
