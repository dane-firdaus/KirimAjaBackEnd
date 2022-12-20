<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MstCityCoordinate extends Model
{
    protected $table = 'mst_city_coordinate';

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function agent()
    {
        return $this->hasMany('App\User', 'kota', 'city_name');
    }

    public function getTotalAgent()
    {
        return $this->agent()->count();
    }
}
