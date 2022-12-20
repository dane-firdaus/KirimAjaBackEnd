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
use App\VoucherUsage;
use App\VourcherData;
use App\BranchOfficeMapping;
use App\Destination;
use App\VourcherDetail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use GuzzleHttp\HandlerStack;
use DateTime;

class MasterController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT', ['except' => ['getBranchOffice','appCheck','getLabel','getPromo','getCommodities', 'bannerImage']]);
    }

    public function testAuthKaspro(){
        $httpmethod = "GET";
        $relativeUrl = "/customer-account-inquiry/?mobileNumber=08561382946";
        $date = date('Y-m-d\TH:i:s.vP');

        $stringToSign = "(request-target):".$httpmethod.$relativeUrl."\n"."date:".$date;
        $hmacSecret = "NmExNDljYjRlMDM3NDVh";
        $hmacKey = "eyJvcmciOiI1ZjY2YzEyMzMxODYwMjAwMDFmN2U0YjMiLCJpZCI6Ijc1MWY2MzRhZWE5OTRkMzhhMzM0MzZlZTkyYjI3ZTBkIiwiaCI6Im11cm11cjY0In0=";
        $sig = hash_hmac('sha256', $stringToSign, $hmacSecret);
        $sig_base64 = base64_encode($sig);
        $sig_kaspro = 'Signature keyId="'.$hmacKey.'",algorithm="hmac-sha256",headers="(request-target) date",signature="'.$sig_base64.'"';

        $return = array(
            'date' => $date,
            'stringToSign' => $stringToSign,
            'sig' => $sig,
            'sig_base64' => $sig_base64,
            'sig_kaspro' => $sig_kaspro
        );

        //return response()->json($return, 200);
        echo $date.'<br/>'.$sig_kaspro;
    }

    public function testGetToken(){
        $client_id = "c3736b8118b64f7cac45c116c4059e3f";
        $client_secret = "MmM1OTllZDYtZWQ5OC00NDk1LWE2MWMtOWVlOTUxYmNkMGNi";
        $hmacKey = "eyJvcmciOiI1ZjY2YzEyMzMxODYwMjAwMDFmN2U0YjMiLCJpZCI6Ijc1MWY2MzRhZWE5OTRkMzhhMzM0MzZlZTkyYjI3ZTBkIiwiaCI6Im11cm11cjY0In0=";
        $hmacSecret = "NmExNDljYjRlMDM3NDVhMzk0YmMzODVlNjI2NDU5Nzg=";
        $origin = "175.106.11.86";

        $method = "get";
        $host_url = "https://apigw-devel.kaspro.id";
        $path_url = "/customer-account-inquiry/";
        $queryParam = "?mobileNumber=08561382946";

        // Authorization client - this is used to request OAuth access tokens
        $reauth_client = new Client([
            // URL for access_token request
            'base_uri' => 'https://apigw-devel.kaspro.id/authentication-server/oauth/token',
        ]);
        $reauth_config = [
            "client_id" => $client_id,
            "client_secret" => $client_secret
        ];
        $grant_type = new ClientCredentials($reauth_client, $reauth_config);
        $oauth = new OAuth2Middleware($grant_type);

        $stack = HandlerStack::create();
        $stack->push($oauth);

        $date = new \DateTime();
        $date = $date->format('D, d M Y H:i:s')." GMT";
        // $date = "Mon, 04 Oct 2021 03:38:07 GMT";

        $stringToSign = "(request-target): ". $method ." ". $path_url."\n";
        $stringToSign = $stringToSign . "date: " . $date;

        $sig = hash_hmac('sha256', $stringToSign, $hmacSecret, true);
        $sig_base64 = base64_encode($sig);
        $sig_url = urlencode($sig_base64);
        $sig_kaspro = 'Signature keyId="'.$hmacKey.'",algorithm="hmac-sha256",headers="(request-target) date",signature="'.$sig_url.'"';

        $header = [
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig_kaspro,
            'Date' => $date,
            'Origin' => $origin,
            'Partner-Key' => $hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);

        $response = $client->get($host_url.$path_url.$queryParam);

        echo $response->getBody();
    }

    public function baseFile(Request $request)
    {
        return response()->json(['encode' => base64_encode(file_get_contents($request->file('foto')))]);
    }

    public function bannerImage(Request $request)
    {
        $profileImage = storage_path('app/public/banner/banner-pickup.jpg');

        $type = File::mimeType($profileImage);
        $realFile = File::get($profileImage);

        $profileImage = Response::stream(function() use($realFile) {
            echo $realFile;
        }, 200, ["Content-Type"=> $type]);

        return $profileImage;
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
        if ($request->has('districtAll') && $request->input('districtAll') == 'true') {
            $type = "subconsole";
            $deliveryPoints = User::with(['identityCard:id,user_id,profile_image'])->whereRaw("trim(kota) = ? AND open_for_drop = true AND user_type = ?", [$request->input('cityName'), $type])->orderBy('kecamatan', 'asc')->get();
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

    public function getKecamatanPickup(Request $request)
    {
        $ajcController = new AJCController();
        $kecamatan = $ajcController->kecamatanPickup($request->input('destinationCriteria'));

        return response()->json($kecamatan, 200);
    }

    public function getJadwalPickup(Request $request)
    {
        $ajcController = new AJCController();
        $layanan = 'SLV';
        if($request->has('layanan')){
            if($request->input('layanan') == ""){
                return response()->json(['message' => 'Silakan Pilih Layanan Terlebih dulu'], 406);
            }else{
                $layanan = $request->input('layanan');
            }
        }
        // else{
        //     return response()->json(['message' => 'Silakan Update Ke Aplikasi Terbaru'], 406);
        // }

        $kecamatan = $ajcController->kecamatanPickup($request->input('kecamatan'));
        if(sizeof($kecamatan) <= 0){
            return response()->json(['message' => 'Maaf Sohib, Kecamatan yang Kamu pilih belum bisa Kami Layani'], 406);
        }
        $zona = $kecamatan[0]->ZonaGroup;
        $tanggal = date('Y-m-d');
        if($request->input('day') == 'tomorrow'){
            $tanggal = date('Y-m-d', strtotime("+1 day"));
        }

        $jadwal = $ajcController->schedulePickup($zona, $tanggal, $layanan);
        if(sizeof($jadwal) <= 0){
            return response()->json(['message' => 'Maaf Sohib, jadwal pick-up hari ini sudah tidak tersedia'], 406);
        }

        return response()->json($jadwal, 200);
    }

    //Phase#2Week#1 - Bayu - START
    public function getSchedulePickup(Request $request){
        $arrHari = array(
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
        );
        $arrBulan = array (
    		1 =>   'Januari',
    		'Februari',
    		'Maret',
    		'April',
    		'Mei',
    		'Juni',
    		'Juli',
    		'Agustus',
    		'September',
    		'Oktober',
    		'November',
    		'Desember'
    	);
        $arrTime = array(
            '08.00-10.00',
            '12.00-14.00',
            '16.00-18.00'
        );

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime("+1 day"));

        $todaySplit = explode('-', $today);
        $tomorrowSplit = explode('-', $tomorrow);

        $arrDate = array(
            array(
                'text' => 'Hari Ini',
                'day' => $arrHari[date('w', strtotime($today))],
                'value' => $today,
                'tanggal' => $todaySplit[2] . ' ' . $arrBulan[(int)$todaySplit[1]] . ' ' . $todaySplit[0]
            ),
            array(
                'text' => 'Besok',
                'day' => $arrHari[date('w', strtotime($tomorrow))],
                'value' => $tomorrow,
                'tanggal' => $tomorrowSplit[2] . ' ' . $arrBulan[(int)$tomorrowSplit[1]] . ' ' . $tomorrowSplit[0]
            )
        );

        $hasil = array();
        foreach($arrDate as $rowDate){
            foreach($arrTime as $rowTime){
                $hasil[$rowDate['text']][] = array(
                    'hari' => $rowDate['day'],
                    'date' => $rowDate['value'],
                    'tanggal' => $rowDate['tanggal'],
                    'time' => $rowTime
                );
            }
        }

        $result = array();
        if($request->input('day') == 'today'){
            $result = $hasil['Hari Ini'];
        }else{
            $result = $hasil['Besok'];
        }

        return response()->json($result, 200);
    }
    //Phase#2Week#1 - Bayu - END

    public function getVoucherByUser(Request $request){
        if($request->input('destination') == ''){
            return response()->json([
                'message' => 'Kecamatan Tujuan tidak boleh kosong'
            ], 406);
        }
        if($request->input('deliveryPoint') == ''){
            return response()->json([
                'message' => 'Lokasi pengantaran tidak boleh kosong'
            ], 406);
        }
        $parameter = $request->only(['pickupStatus', 'destination', 'deliveryPoint']);
        $dropPoint = $parameter['deliveryPoint'];
        $destination = $parameter['destination'];

        $pickupStatus = false;
        $layanan = 1;
        $AJCBranchCodeList = ['CGK' => 'JKT'];

        if($request->has('pickupStatus')){
            $pickupStatus = $request->input('pickupStatus');
        }

        if($pickupStatus){
            $layanan = 2;
            $branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE :citysubcon",['citysubcon'=>'%'.$dropPoint.'%'])->first();
            if ($branchCode != null) {
                $branchCode = $branchCode->airport_code;
            }
        }else{
            $deliveryPoint = DeliveryPoint::where('id', $dropPoint)->first();
            $subConsole = User::where('id', $dropPoint)->first();

            if (is_null($deliveryPoint) && is_null($subConsole)) {
                return response()->json(['message' => 'Delivery point not found']);
            }

            $branchCode = "";

            if (!is_null($deliveryPoint)) {
                $branchCode = $deliveryPoint->branch_city_code;
            } else {
                $cityOfSubconsole = $subConsole->kota;
                $branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE :citysubcon",['citysubcon'=>'%'.$cityOfSubconsole.'%'])->first();
                if ($branchCode != null) {
                    $branchCode = $branchCode->airport_code;
                }
            }
        }

        $hubPengirim = (array_key_exists($branchCode, $AJCBranchCodeList)) ? $AJCBranchCodeList[$branchCode] : $branchCode;
        
        // $query = "
        //     SELECT
        //     id,
        //     voucher_code,
        //     one_time_usage,
        //     (
        //         SELECT count(1) AS total
        //         FROM trx_booking b
        //         INNER JOIN trx_voucher_usage vu ON b.id = vu.booking_id
        //         WHERE
        //             b.user_id = 10000 AND
        //             vu.voucher_id = v.id AND
        //             b.deleted_at IS NULL
        //     ) AS usage_per_user,
        //     user_target,
        //     quota_unlimited,
        //     quota,
        //     (
        //         SELECT count(1) AS total
        //         FROM trx_booking b2
        //         INNER JOIN trx_voucher_usage vu2 ON b2.id = vu2.booking_id
        //         WHERE
        //             vu2.voucher_id = v.id AND
        //             b2.deleted_at IS NULL
        //     ) AS usage_per_voucher,
        //     budget_unlimited,
        //     budget_limit,
        //     (
        //         SELECT SUM(transaction_voucher_amount) AS total
        //         FROM  trx_voucher_usage vu3
        //         WHERE vu3.voucher_id = v.id
        //     ) AS usage_budget
        //     FROM mst_voucher v
        //     WHERE
        //     v.active_status = 1 AND
        //     start_date <= NOW() AND
        //     end_date >= NOW() AND
        //     (one_time_usage = 0 OR (
        //         (SELECT count(1) AS total
        //         FROM trx_booking b
        //         INNER JOIN trx_voucher_usage vu ON b.id = vu.booking_id
        //         WHERE
        //             b.user_id = 10000 AND
        //             vu.voucher_id = v.id AND
        //             b.deleted_at IS NULL) < 1
        //     )) AND
        //     (user_target = 1 OR (10000 IN (SELECT user_id FROM mst_voucher_detail WHERE voucher_id = v.id))) AND
        //     (quota_unlimited = B'1' OR (
        //         (SELECT count(1) AS total
        //         FROM trx_booking b2
        //         INNER JOIN trx_voucher_usage vu2 ON b2.id = vu2.booking_id
        //         WHERE
        //             vu2.voucher_id = v.id AND
        //             b2.deleted_at IS NULL) < quota
        //     )) AND
        //     (budget_unlimited = B'1' OR (
        //         (SELECT SUM(transaction_voucher_amount) AS total
        //         FROM  trx_voucher_usage vu3
        //         WHERE vu3.voucher_id = v.id) < budget_limit
        //     ))
        //     ORDER BY v.id
        // ";

        // $query = "SELECT * FROM mst_voucher";



        
        $query = "SELECT * FROM mst_voucher v
        WHERE
            v.active_status = 1 AND
            is_public = true AND
            start_date <= NOW() AND
            end_date >= NOW() AND
            (layanan_type = :layanan OR layanan_type = 0) AND
            (one_time_usage = 0 OR (
                (SELECT count(1) AS total
                FROM trx_booking b
                INNER JOIN trx_voucher_usage vu ON b.id = vu.booking_id
                WHERE
                    b.user_id = :userid AND
                    vu.voucher_id = v.id AND
                    b.deleted_at IS NULL) < 1
            )) AND
            (user_target = 1 OR (:userid IN (SELECT user_id FROM mst_voucher_detail WHERE voucher_id = v.id))) AND
            (quota_unlimited = B'1' OR (
                (SELECT count(1) AS total
                FROM trx_booking b2
                INNER JOIN trx_voucher_usage vu2 ON b2.id = vu2.booking_id
                WHERE
                    vu2.voucher_id = v.id AND
                    b2.deleted_at IS NULL) < quota
            )) AND
            (budget_unlimited = B'1' OR (
                (SELECT SUM(transaction_voucher_amount) AS total
                FROM  trx_voucher_usage vu3
                WHERE vu3.voucher_id = v.id) < budget_limit
            ))
        ORDER BY v.id";

        $selectVoucher = \DB::select($query,[
            'layanan' => $layanan,
            'userid' => auth()->user()->id
        ]);

        $result = array();
        foreach($selectVoucher as $voucher){
            $isValid = true;

            // CEK ORIGIN HUB
            $hubOrigin=explode(",", $voucher->origin_hub);
            if(!empty($voucher->origin_hub)){
                if($voucher->origin_hub != "All"){
                    // echo "<pre>";print_r($hubPengirim);echo '</pre>';
                    if( !in_array($hubPengirim, $hubOrigin) ){
                        $isValid = false;
                    }

                }
            }

            //CEK DESTINATION
            if(!empty($voucher->dest_hub)){
                if($voucher->dest_hub != "All"){
                    $voucher->dest_city= ($voucher->dest_city==","?"":$voucher->dest_city);
                    $hubCityDes=explode(",", $voucher->dest_city);
                    if(!empty($voucher->dest_city)){
                        $DestinationCode = Destination::where("name", $destination)->first();
                        if($DestinationCode){
                            if(!in_array($DestinationCode->code, $hubCityDes) ){
                                $isValid = false;
                            }
                        }

                    }
                }
            }

            if($isValid){
                $result[] = $voucher;
            }
        }

        if(sizeof($result) > 0){
            $hasil = array(
                'message' => 'success',
                'data' => $result
            );
            return response()->json($hasil, 200);
        }else{
            return response()->json([
                'message' => 'Voucher Tidak ditemukan'
            ], 200);
        }
    }

    // public function getVoucherByUser(Request $request){
    //     $parameter = $request->only(['pickupStatus', 'destination', 'deliveryPoint']);
    //     $dropPoint = $parameter['deliveryPoint'];
    //     $destination = $parameter['destination'];
    //     $pickupStatus = false;
    //     $layanan = 1;
    //     $AJCBranchCodeList = ['CGK' => 'JKT'];
    //
    //     if($request->has('pickupStatus')){
    //         $pickupStatus = $request->input('pickupStatus');
    //     }
    //
    //     if($pickupStatus){
    //         $layanan = 2;
    //         $branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE :citysubcon",['citysubcon'=>'%'.$dropPoint.'%'])->first();
    //         if ($branchCode != null) {
    //             $branchCode = $branchCode->airport_code;
    //         }
    //     }else{
    //         $deliveryPoint = DeliveryPoint::where('id', $dropPoint)->first();
    //         $subConsole = User::where('id', $dropPoint)->first();
    //
    //         if (is_null($deliveryPoint) && is_null($subConsole)) {
    //             return response()->json(['message' => 'Delivery point not found']);
    //         }
    //
    //         $branchCode = "";
    //
    //         if (!is_null($deliveryPoint)) {
    //             $branchCode = $deliveryPoint->branch_city_code;
    //         } else {
    //             $cityOfSubconsole = $subConsole->kota;
    //             $branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE :citysubcon",['citysubcon'=>'%'.$cityOfSubconsole.'%'])->first();
    //             if ($branchCode != null) {
    //                 $branchCode = $branchCode->airport_code;
    //             }
    //         }
    //     }
    //
    //     $hubPengirim = (array_key_exists($branchCode, $AJCBranchCodeList)) ? $AJCBranchCodeList[$branchCode] : $branchCode;
    //     // echo "<pre>";print_r($branchCode);echo '</pre>';exit;
    //     // $selectVoucher = \DB::select("
    //     //     SELECT * FROM mst_voucher
    //     //     WHERE
    //     //         start_date <= :startdate AND
    //     //         end_date >= :enddate AND
    //     //         (layanan_type = :layanan OR layanan_type = 0)"
    //     // ,[
    //     //     'startdate' => date('Y-m-d H:i:s'),
    //     //     'enddate' => date('Y-m-d H:i:s'),
    //     //     'layanan' => $layanan
    //     // ]);
    //
    //     $selectVoucher = \DB::select("
    //         SELECT * FROM mst_voucher
    //         WHERE
    //             active_status = 1 AND
    //             (layanan_type = :layanan OR layanan_type = 0)"
    //     ,[
    //         'layanan' => $layanan
    //     ]);
    //
    //     $result = array();
    //     foreach($selectVoucher as $voucher){
    //         $isValid = true;
    //
    //         //CEK ORIGIN HUB
    //         $hubOrigin=explode(",", $voucher->origin_hub);
    //         if(!empty($voucher->origin_hub)){
    //             if($voucher->origin_hub != "All"){
    //                 // echo "<pre>";print_r($hubPengirim);echo '</pre>';
    //                 if( !in_array($hubPengirim, $hubOrigin) ){
    //                     $isValid = false;
    //                 }
    //
    //             }
    //         }
    //
    //         //CEK DESTINATION
    //         if(!empty($voucher->dest_hub)){
    //             if($voucher->dest_hub != "All"){
    //                 $voucher->dest_city= ($voucher->dest_city==","?"":$voucher->dest_city);
    //                 $hubCityDes=explode(",", $voucher->dest_city);
    //                 if(!empty($voucher->dest_city)){
    //                     $DestinationCode = Destination::where("name", $destination)->first();
    //                     if($DestinationCode){
    //                         if(!in_array($DestinationCode->code, $hubCityDes) ){
    //                             $isValid = false;
    //                         }
    //                     }
    //
    //                 }
    //             }
    //         }
    //
    //         //CEK ONE TIME USAGE
    //         if($voucher->one_time_usage == 1){
    //             $checkBooking = \DB::select("
    //                 select count(1) as total
    //                 from trx_booking b
    //                 inner join trx_voucher_usage vu on b.id = vu.booking_id
    //                 where
    //                     b.user_id=:uid and
    //                     vu.voucher_id=:vid and
    //                     b.deleted_at IS NULL"
    //             ,[
    //                 'uid' => auth()->user()->id,
    //                 'vid' => $voucher->id
    //             ]);
    //             if(!empty($checkBooking[0])){
    //                 if($checkBooking[0]->total > 0) {
    //                     $isValid = false;
    //                 }
    //             }
    //         }
    //
    //         if(($voucher->user_target == 2 || $voucher->user_target == 3)){
    //             $voucherTarget = VourcherDetail::where([
    //                 'voucher_id' => $voucher->id,
    //                 'user_id' => auth()->user()->id
    //             ])->first();
    //             if(empty($voucherTarget->user_id)){
    //                 $isValid = false;
    //             }
    //         }
    //
    //         if($isValid){
    //             $result[] = $voucher;
    //         }
    //     }
    //
    //     return response()->json($result, 200);
    // }
}
