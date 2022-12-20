<?php

namespace App\Http\Controllers;

use App\Booking;
use App\BranchOffice;
use App\CustomerBooking;
use App\CustomerBookingDetail;
use App\Destination;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); 
    }

    public function booking(Request $request)
    {
        $parameter = $request->all();

        $booking = new CustomerBooking();
        $booking->device_token = $parameter['token'];
        $booking->booking_code = Str::random(10);

        $booking->booking_origin_name = $parameter['origin']['name'];
        $booking->booking_origin_addr_1 = $parameter['origin']['address_1'];
        $booking->booking_origin_addr_2 = $parameter['origin']['address_2'];
        $booking->booking_origin_addr_3 = $parameter['origin']['address_3'];
        $booking->booking_origin_city = $parameter['origin']['city'];
        $booking->booking_origin_zip = $parameter['origin']['zip'];
        $booking->booking_origin_contact = $parameter['origin']['contact'];
        $booking->booking_origin_phone = $parameter['origin']['phone'];

        $booking->booking_destination_name = $parameter['destination']['name'];
        $booking->booking_destination_addr_1 = $parameter['destination']['address_1'];
        $booking->booking_destination_addr_2 = $parameter['destination']['address_2'];
        $booking->booking_destination_addr_3 = $parameter['destination']['address_3'];
        $booking->booking_destination_city = $parameter['destination']['city'];
        $booking->booking_destination_zip = $parameter['destination']['zip'];
        $booking->booking_destination_contact = $parameter['destination']['contact'];
        $booking->booking_destination_phone = $parameter['destination']['phone'];
        
        $booking->booking_delivery_point_id = $parameter['deliveryPoint'];
        $booking->booking_fee = $parameter['serviceFee'];
        $booking->save();
        
        $totalCost = 0;
        $serviceFee = $parameter['serviceFee'];
        $totalWeight = 0;

        for ($i=0; $i < sizeof($parameter['shipment']); $i++) { 
            $item = $parameter['shipment'][$i];
            $detail = new CustomerBookingDetail();
            $detail->booking_id = $booking->id;
            $detail->package_description = $item['description'];
            $detail->package_length = $item['length'];
            $detail->package_width = $item['width'];
            $detail->package_height = $item['height'];
            $detail->package_weight = $item['weight'];
            $detail->package_volume = $item['volume'];
            $detail->package_quantity = $item['piece'];
            if (array_key_exists('actual_weight', $item)) {
                $detail->package_actual_weight = $item['actual_weight'];
                $detail->package_actual_volume = $item['actual_volume'];
            }
            $detail->save();

            if ($item['chargeable'] == 'volume') {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_volume;
                $totalWeight += $detail->package_quantity * $detail->package_volume;
            } else {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_weight;
                $totalWeight += $detail->package_quantity * $detail->package_weight;
            }
        }

        $booking = CustomerBooking::with(['sohib:id,fullname,phone,address,kecamatan,kota,provinsi'])->find($booking->id);
        $booking->booking_estimate_cost = $totalCost;
        $booking->save();

        return response()->json($booking, 201);
    }

    public function getSohibSubConsole(Request $request)
    {
        $deliveryPoints = DB::select("SELECT id, fullname, address, kecamatan, trim(kota) as kota, trim(provinsi) as provinsi 
                                    FROM mst_user 
                                    WHERE trim(kota) = '".$request->input('districtName')."' AND kecamatan IS NOT NULL AND kecamatan != 'null' AND user_type = 'user'");
        return response()->json($deliveryPoints);
    }

    public function getDestination(Request $request)
    {
        $destination = Destination::where('name', 'LIKE', '%'.strtoupper($request->input('destinationCriteria')).'%')->get();
        return response()->json($destination, 200);
    }

    public function tracking(Request $request)
    {
        $ajcController = new AJCController();
        $trackConnote = $ajcController->trackConnote($request->input('connote'));

        return response()->json($trackConnote);
    }

    public function getCheckRate(Request $request)
    {
        $destination = $request->input('destination');
        $origin = $request->input('origin');

        if (strlen($origin) > 3) {
            //20210305 - TID:pfj9NTWO - START - KIBAR
            //DB::enableQueryLog();
            //$branchCode = BranchOffice::whereRaw("TRIM(name) ILIKE '%".$origin."%'")->first();
            $branchCode = BranchOffice::whereRaw("TRIM(name) ILIKE :origin",['origin'=>'%'.$origin.'%'])->first();
            //dd(DB::getQueryLog());
            //20210305 - TID:pfj9NTWO - END - KIBAR
            if ($branchCode != null) {
                $branchCode = $branchCode->code;
            } else {
                return response()->json(['message' => 'origin code not found'], 406);
            }
        } else {
            $branchCode = $origin;
        }

        $AJCBranchCodeList = ['CGK' => 'JKT'];

        $param = ['form_params' => [
            'username' => 'kirimaja',
            'api_key' => 'kirimaja',
            'OriginCabang' => (array_key_exists($branchCode, $AJCBranchCodeList)) ? $AJCBranchCodeList[$branchCode] : $branchCode,
            'Destination' => $destination,
            'Service' => 'SLV'
        ]];
        
        $request = $this->client->post('https://service.goexpress.id/trace/pricelist', $param);

        $response = $request ? $request->getBody()->getContents() : null;
        if ($response != null) {
            $response = json_decode($response, true);
            if (array_key_exists('list', $response)) {
                if (empty($response['list'])) {
                    return response()->json(['message' => 'Mohon maaf Kak, destinasi tersebut belum tersedia untuk saat ini.']);
                }
                $result = array();
                //20210301 - TID: 3B23WByr - START
                $leadTime = $response['list'][0]['LeadTime'];
                $leadTimeText = $leadTime;
                if($leadTime > 1){
                    $leadTime2 = $leadTime - 1;
                    $leadTimeText = $leadTime2."-".$leadTime;
                }
                $result['message'] = 'Biaya kirim ke kota yang kamu pilih adalah IDR '.number_format($response['list'][0]['Price1st']).'/kg.\n\nEstimasi waktu pengiriman '.$leadTimeText.' hari.';
                //20210301 - TID: 3B23WByr - END
                return response()->json($result);
            }
            
        }
        return response($response, 200, ['content-type' => 'application/json']);
    }
}
