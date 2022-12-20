<?php

namespace App\Http\Controllers;

use App\AirWaybill;
use App\AppCheck;
use App\BranchOffice;
//02032021 - TID: dLdzR8rs START
use App\CityBranchOffice;
//02032021 - TID: dLdzR8rs END
use App\Commodities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DeliveryPoint;
use App\ExcelModel\MstDistrictPriceImport;
use App\MasterDistrictPrice;
use App\MasterLabel;
use App\Promo;
use App\User;
use Maatwebsite\Excel\Facades\Excel;

class MasterController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT', ['except' => ['getBranchOffice','appCheck','getLabel','getPromo','getCommodities']]);   
    }

    public function baseFile(Request $request)
    {
        return response()->json(['encode' => base64_encode(file_get_contents($request->file('foto')))]);
    }

    public function getBranchOffice(Request $request)
    {
        $branches = BranchOffice::orderBy('name', 'asc')->get();
        return response()->json($branches, 200);
    }

    //02032021 - TID: dLdzR8rs START
    public function getCodeByCity(Request $request)
    {
        $cityBranch = CityBranchOffice::where('city_name', $request->input('kota_kab'))->first();
        return response()->json($cityBranch);
    }
    //02032021 - TID: dLdzR8rs END

    public function getDeliveryPoint(Request $request)
    {
        $branch = auth()->user()->city_code;
        if ($request->has('cityCode')) {
            $branch = $request->input('cityCode');
            $deliveryPoints = DeliveryPoint::where('branch_city_code', $branch)->where('address', '!=', NULL)->get();
            return response()->json($deliveryPoints);
        }

        if ($request->has('cityName') && $request->has('cityOverview') && $request->input('cityOverview') == 'true') {
            $type = "subconsole";
            $deliveryPoints = DB::select("SELECT count(id) as total_subconsole, trim(kecamatan) as district
                                        FROM mst_user 
                                        WHERE user_type = '".$type."' 
                                        AND trim(kota) = '".$request->input('cityName')."'
                                        AND open_for_drop = true GROUP BY district");
            return response()->json($deliveryPoints);
        }

        if ($request->has('districtName')) {
            $type = "subconsole";
            $deliveryPoints = User::with(['identityCard:id,user_id,profile_image'])->whereRaw("trim(kecamatan) = ? AND open_for_drop = true AND user_type = ?", [$request->input('districtName'), $type])->get();
            return response()->json($deliveryPoints);
        }
    }

    public function storeDeliveryPoint(Request $request)
    {
        $parameter = $request->only(['name', 'type', 'cityCode', 'latitude', 'longitude', 'opsDay', 'opsTime']);

        $deliveryPoint = new DeliveryPoint();
        $deliveryPoint->name = $parameter['name'];
        $deliveryPoint->type = $parameter['type'];
        $deliveryPoint->branch_city_code = $parameter['cityCode'];
        $deliveryPoint->latitude = $parameter['latitude'];
        $deliveryPoint->longitude = $parameter['longitude'];
        $deliveryPoint->operational_day = $parameter['opsDay'];
        $deliveryPoint->operational_time = $parameter['opsTime'];
        $deliveryPoint->save();
        
        return response()->json($deliveryPoint);
    }

    public function appCheck(Request $request)
    {
        $platform = $request->input('platform');
        $version = $request->input('version');
        
        $check = AppCheck::where(['platform' => $platform, 'version' => $version])->first();

        return response()->json($check);
    }

    public function getLabel(Request $request)
    {
        $label = MasterLabel::where('module', $request->input('module'))->get();
        return response()->json($label);
    }

    public function getPromo(Request $request)
    {
        //02032021 - TID: nNuQqFUJ START
        //$promos = Promo::where('active', true)->orderBy('id', 'desc')->get();
        $promos = Promo::where('active', true)->where('post', 'publish')->orderBy('id', 'desc')->offset(0)->limit(3)->get();
        //02032021 - TID: nNuQqFUJ END
        return response()->json($promos);
    }

    public function getCommodities(Request $request)
    {
        //20210408 - TID:PGGumXwG - KIBAR
        //$commodities = Commodities::orderBy('commodity_name', 'asc')->get();
        $commodities = Commodities::orderBy('commodity_name', 'asc')->where('active',true)->get();
        //20210408 - TID:PGGumXwG - KIBAR
        return response()->json($commodities, 200);
    }


    public function newCommodities(Request $request)
    {
        if (auth()->user()->user_type != "admin") {
            return response()->json([], 404);
        }

        $commodity = new Commodities();
        $commodity->commodity_name = $request->input('name');
        $commodity->remarks = $request->input('remark');
        $commodity->active = true;
        $commodity->save();

        return response()->json($commodity, 200);
    }

    /* for handling corporate pricing */

    public function importDistrictPrice(Request $request)
    {
        if (auth()->user()->user_type != "admin") {
            return response()->json([], 404);
        }

        $prices = Excel::toArray(new MstDistrictPriceImport, $request->file('xls_file'));

        foreach ($prices[0] as $price) {
            if ($price['harga'] != '#N/A') {
                $master = new MasterDistrictPrice();
                $master->origin = $price['origin'];
                $master->destination = $price['destination'];
                $master->price = $price['harga'];
                $master->save();
            }            
        }

        return response()->json($prices[0], 200);
    }
}
