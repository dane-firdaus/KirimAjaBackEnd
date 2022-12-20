<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MstAirportCoordinate extends Model
{
    protected $table = 'mst_airport_coordinate';

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function agent()
    {
        return $this->hasMany('App\User', 'city_code', 'airport_code');
    }

    public function getTotalAgent()
    {
        return $this->agent()->count();
    }
}
