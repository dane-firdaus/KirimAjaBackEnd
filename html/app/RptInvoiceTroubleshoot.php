<?php

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class RptInvoiceTroubleshoot extends Model
{
    protected $table = 'rpt_invoice_troubleshoot';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i:s');
    }
}


