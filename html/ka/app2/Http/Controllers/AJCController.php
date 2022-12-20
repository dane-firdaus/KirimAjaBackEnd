<?php

namespace App\Http\Controllers;

use App\AirWaybill;
use App\AJCBookingLog;
use App\AJCShipmentNotifyLog;
use App\Booking;
use App\DeliveryPoint;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Commodities; //20210302 - TID:ZWeiHYnV - START

class AJCController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT', ['except' => ['bookingAjc', 'shipmentNotification']]);
        $this->client = new Client([
            'verify' => false
        ]);
    }

    public function storeBooking($bookingId)
    {
        $booking = Booking::with(['details','payment'])->where('id', $bookingId)->first();
        if (is_null($booking)) {
            return 'Booking not found'.$bookingId;
        }
        
        $totalPackage = 0;
        $totalWeight = 0;

        //20210302 - TID:Fzym2wvL - START
        $_arQty=[];
        $_arWEIGHT=[];
        $_arGOODSDESC="";
        $_INST="";
        $_DIMENSI_HEIGHT=[];
        $_DIMENSI_WIDTH=[];
        $_DIMENSI_LENGTH=[];

        $nourut=1;
        //20210302 - TID:Fzym2wvL - END

        //20210302 - TID:ZWeiHYnV - START
        $contentOfGoods = '';
        $specialInstructions = '';
        //20210302 - TID:ZWeiHYnV - END

        foreach ($booking->details as $item) {
            //20210302 - TID:Fzym2wvL - START
            $totalPackage += $item->package_quantity;

            if ($item->package_volume > $item->package_weight) {
                $totalWeight += $item->package_volume;
                $_arWEIGHT[]=$item->package_volume;
            } else {
                $totalWeight += $item->package_weight;
                $_arWEIGHT[]=$item->package_weight;
            }

            $_arQty[]=$item->package_quantity;
            $_DIMENSI_HEIGHT[]=$item->package_height;
            $_DIMENSI_WIDTH[]=$item->package_width;
            $_DIMENSI_LENGTH[]=$item->package_length;
            //20210302 - TID:Fzym2wvL - END

            //20210302 - TID:ZWeiHYnV - START
            $comm_name = '';
            if(!empty($item->package_commodity_id))
            {
                $comm_name = Commodities::where('id', $item->package_commodity_id)->first()->commodity_name;
            }
            $contentOfGoods .= $nourut.'. '.$item->package_description.' | ';
            $specialInstructions .= $nourut.'. '. $comm_name .' | ';

            $nourut++;

            //20210302 - TID:ZWeiHYnV - END
        }

        $param = ['form_params' => [
            'username' => 'kirimaja',
            'api_key' => 'kirimaja',
            'ORDERID' => $booking->booking_code,
            'SERVICE' => 'SLV',
            'PACKAGE' => 'SPS',
            'MODA' => 'UDARA',
            'SHIPPER_NAME' => $booking->booking_origin_name,
            'SHIPPER_ADDR1' => $booking->booking_origin_addr_1,
            'SHIPPER_ADDR2' => '-',
            'SHIPPER_ADDR3' => '-',
            'SHIPPER_CITY' => $booking->booking_origin_city,
            'SHIPPER_ZIP' => $booking->booking_origin_zip,
            'SHIPPER_CONTACT' => $booking->booking_origin_contact,
            'SHIPPER_PHONE' => $booking->booking_origin_phone,
            'RECEIVER_NAME' => $booking->booking_destination_name,
            'RECEIVER_ADDR1' => $booking->booking_destination_addr_1,
            'RECEIVER_ADDR2' => '-',
            'RECEIVER_ADDR3' => '-',
            'RECEIVER_CITY' => $booking->booking_destination_city,
            'RECEIVER_ZIP' => $booking->booking_destination_zip,
            'RECEIVER_CONTACT' => $booking->booking_destination_contact,
            'RECEIVER_PHONE' => $booking->booking_destination_phone,
            //20210302 - TID:Fzym2wvL - START
            'DIMENSI_HEIGHT'=>json_encode($_DIMENSI_HEIGHT,true),
            'DIMENSI_WIDTH'=>json_encode($_DIMENSI_WIDTH,true),
            'DIMENSI_LENGTH'=>json_encode($_DIMENSI_LENGTH,true),
            'QTY' =>json_encode($_arQty,true),
            'WEIGHT' => json_encode($_arWEIGHT,true),
            //20210302 - TID:Fzym2wvL - END
            //20210302 - TID:ZWeiHYnV - START
            'GOODSDESC' => $contentOfGoods,
            'INST' => $specialInstructions,
            //20210302 - TID:ZWeiHYnV - END
            'INS_FLAG' => '-',
            'PICKUP' => date('Y-m-d H:m:s'),
        ]];

        $ajcLog = new AJCBookingLog();
        $ajcLog->booking_id = $booking->id;
        $ajcLog->awb = 0;
        $ajcLog->parameter = json_encode($param);
        $ajcLog->save();
        
        $ajcSys_InsertAutoAWB = 'https://service.goexpress.id/trace/insertautoawb';
        if (env('APP_ENV') == 'development') {
            $ajcSys_InsertAutoAWB = 'https://service-dev.goexpress.id/trace/insertautoawb';
        }

        $request = $this->client->post($ajcSys_InsertAutoAWB, $param);

        $response = $request ? $request->getBody()->getContents() : null;

        $ajcLog = AJCBookingLog::find($ajcLog->id);
        $ajcLog->response = $response;
        $response = json_decode($response);
        $ajcLog->awb = (isset($response->detail->cnote_no)) ? $response->detail->cnote_no : "0";
        $ajcLog->save();
        
        return $response;
    }

    public function trackConnote($connote)
    {
        $param = ['form_params' => [
            'username' => 'kirimaja',
            'api_key' => 'kirimaja',
            'keyword' => $connote
        ]];

        $request = $this->client->post('https://service.goexpress.id/trace/checkawb', $param);
        $response = $request ? $request->getBody()->getContents() : null;
        //20210308 - TID:ZWeiHYnV - START - kibar
        $return = json_decode($response);
        $check_pod = count($return->miletones)-1;

        if($return->miletones[$check_pod]->header == 'POD Return')
        {
            unset($return->miletones[$check_pod]);
        }

        //return json_decode($response);
        return $return;
        //20210308 - TID:ZWeiHYnV - START - kibar
    }

    public function shipmentNotification(Request $request)
    {
        $param = $request->only('connote', 'bookingCode');
        
        $log = new AJCShipmentNotifyLog();
        $log->log = json_encode($param);
        $log->ip_address = $request->ip();
        $log->save();
        
        return response()->json([
            'message' => 'data received, danke!'], 200);
    }
}
