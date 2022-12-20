<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'mst_campaign';

    public function purpose()
    {
        return $this->hasOne('App\Purpose', 'id', 'id_purpose');
    }

    public function subpurpose()
    {
        return $this->hasOne('App\Subpurpose', 'id', 'id_subpurpose');
    }

    public function user()
    {
        return $this->hasMany('App\User', 'campaign', 'deeplink_param');
    }

    public function booking()
    {
        return $this->hasMany('App\Booking', 'campaign', 'deeplink_param');
    }
}
