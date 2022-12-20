<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class External extends Model
{
    protected $table = 'mst_external';

    public function campaign()
    {
        return $this->hasOne('App\Campaign', 'id', 'campaign_id');
    }
}
