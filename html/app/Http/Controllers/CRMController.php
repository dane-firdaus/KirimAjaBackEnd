<?php

namespace App\Http\Controllers;

use App\CRMLead;
use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function customerList(Request $request)
    {
        
    }

    public function newLead(Request $request)
    {
        $param = $request->all();
        $lead = new CRMLead();
        $lead->corporate_name = $param['name'];
        
    }
}
