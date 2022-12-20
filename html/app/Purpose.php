<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purpose extends Model
{
    protected $table = 'mst_campaign_purpose';

    public function subpurpose(){
        return $this->hasMany('App\Subpurpose', 'id_purpose', 'id');
    }
}
