<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSavedAddress extends Model
{
    protected $table = 'mst_user_address';

    protected $hidden = [
        'created_at'
    ];

    protected $casts = [
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];
}
