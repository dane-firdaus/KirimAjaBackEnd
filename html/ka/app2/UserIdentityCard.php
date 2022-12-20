<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIdentityCard extends Model
{
    protected $table = 'mst_user_identity_card';

    protected $hidden = [
        'created_at'
    ];
}
