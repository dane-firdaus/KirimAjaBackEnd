<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subpurpose extends Model
{
    protected $table = 'mst_campaign_subpurpose';

    public function purpose()
    {
        return $this->hasOne('App\Purpose', 'id', 'id_purpose');
    }
}
