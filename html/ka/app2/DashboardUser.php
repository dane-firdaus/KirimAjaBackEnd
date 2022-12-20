<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DashboardUser extends Model
{
    protected $table = 'mst_user_dashboard';

    protected $hidden = [
        'password'
    ];
}
