<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

// t10 20210324 TID: U9LgjemB 
class RptConnoteTroubleshoot extends Model
{
    protected $table = 'rpt_connote_troubleshoot';
    protected $fillable = [
        'booking_id', 'awb', 'user_id','booking_code','booking_code'
    ];

    protected $hidden = [
      //  'created_at','updated_at'
    ];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    //     'updated_at' => 'datetime:Y-m-d H:i:s',
    // ];

    

    public function proof()
    {
        return $this->hasOne('App\RptInvoiceTroubleshoot', 'invoice', 'invoice');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }

}
