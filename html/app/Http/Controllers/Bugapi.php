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
//20210323 - TID: U9LgjemB - KIBAR
use App\Events\OrderReceiptEvent;
use App\PaymentCart;
use DateTime;
//20210323 - TID: U9LgjemB - KIBAR

class Bugapi extends Controller
{
    public function __construct()
    {
        $this->client = new Client([
            'verify' => false
        ]);
    }
    function index(){

        $bookingId="145";
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
            $comm_name = Commodities::where('id', $item->package_commodity_id)->first()->commodity_name;
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

    //    echo "<pre>";
    //    print_r($param);
    //    echo "</pre>";
    //    exit();

        $ajcSys_InsertAutoAWB = config('global.LINK_API_SIS').'/trace/insertautoawb';
        $request = $this->client->post($ajcSys_InsertAutoAWB, $param);

        $response = $request ? $request->getBody()->getContents() : null;
        $response = json_decode($response);
        print_r($response);
        // print_r($response->detail->cnote_no);

        // dd($param);

        // dd($param);

        // $param['form_params']['GOODSDESC']
    }




    //20210323 - TID: U9LgjemB - KIBAR
    public function generateConnote(Request $request)
    {
        $bookingCode = strtoupper($request->input('bookingCode'));

        $booking = Booking::with(['shipment','payment','paymentRequest'])->whereRaw('UPPER(booking_code) = ?', $bookingCode)->first();
        if (is_null($booking)) {
            return response()->json([
                'message' => 'booking not found'
            ], 200);
        }
        dd($booking);

        $carting = PaymentCart::where('booking_id', $booking->id)->first();
    }
    public function issuedConnote(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        $bookingCode = strtoupper($request->input('bookingCode'));

        $booking = Booking::with(['shipment','payment','paymentRequest'])->whereRaw('UPPER(booking_code) = ?', $bookingCode)->first();
        if (is_null($booking)) {
            /* you know lah */
            return response()->json([
                'message' => 'booking not found'
            ], 200);
        }

        $carting = PaymentCart::where('booking_id', $booking->id)->first();

        if (($booking->paymentRequest == null || count($booking->paymentRequest) == 0) && is_null($carting)) {
            /* not yet request payment to doku */
            return response()->json([
                'message' => 'booking not yet request for the payment',
                'booking' => $booking
            ], 200);
        }

        if ($booking->payment->paid == false) {
            /* payment status still unpaid */
            return response()->json([
                'message' => 'still unpaid',
                'booking' => $booking
            ], 200);
        }

        $airwaybill = AJCBookingLog::where('booking_id', $booking->id)->first();
        if (is_null($airwaybill)) {
            /* booking doesn't have awb data */
            $ajcController = new AJCController();
            $storeBooking = $ajcController->storeBooking($booking->id);

            if ($booking->shipment->awb != "0") {
                event(new OrderReceiptEvent($booking));
            }

            return response()->json([
                'message' => 'success booking awb',
                'aeroResponse' => $storeBooking
            ], 200);
        }

        if ($airwaybill->awb == 0 || $airwaybill->awb == "0") {
            /* booking has awb data, but response is zero when request to ajc */
            $airwaybill->delete();
            $ajcController = new AJCController();
            $storeBooking = $ajcController->storeBooking($booking->id);

            if ($booking->shipment->awb != "0") {
                event(new OrderReceiptEvent($booking));
            }

            return response()->json([
                'message' => 'success rebooking awb',
                'aeroResponse' => $storeBooking
            ], 200);
        }

        return response()->json($booking, 200);
    }

    public function checkPayment(Request $request)
    {
        $paymentChannel = [
            15 => 'Credit Card',
            41 => 'Bank Mandiri - Virtual Account',
            36 => 'Bank Permata - Virtual Account',
            32 => 'Bank CIMB Niaga - Virtual Account',
            04 => 'DOKU Wallet',
            50 => 'LinkAja',
            53 => 'OVO',
        ];

        $bookingCode = strtoupper($request->input('bookingCode'));

        $booking = Booking::with(['payment','paymentRequest'])->whereRaw('UPPER(booking_code) = ?', $bookingCode)->first();

        if (is_null($booking)) {
            return response()->json(['message' => 'booking not found'], 404);
        }

        if (is_null($booking->paymentRequest)) {
            return response()->json(['message' => 'booking not yet request payment to PG'], 406);
        }

        $data = array();

        foreach ($booking->paymentRequest as $item) {
            $status = $this->getPaymentStatus($item->transid);
            if ($status['PAYMENTCHANNEL'] != null) {
                $status['PAYMENTCHANNEL'] = $paymentChannel[$status['PAYMENTCHANNEL']];
                array_push($data, $status);
            }
        }

        return response()->json($data, 200);
    }

    public function updatePaymentStatus(Request $request)
    {
        $paymentChannel = [
            41 => 'Bank Mandiri - Virtual Account',
            36 => 'Bank Permata - Virtual Account',
            32 => 'Bank CIMB Niaga - Virtual Account',
            04 => 'DOKU Wallet',
            50 => 'LinkAja',
            53 => 'OVO',
        ];

        $bookingCode = strtoupper($request->input('bookingCode'));

        $booking = Booking::with(['payment','paymentRequest','shipment'])->whereRaw('UPPER(booking_code) = ?', $bookingCode)->first();

        if (is_null($booking)) {
            return response()->json(['message' => 'booking not found'], 406);
        }

        if (is_null($booking->paymentRequest)) {
            return response()->json(['message' => 'booking not yet request payment to PG.'], 406);
        }

        if (!is_null($booking->shipment) && $booking->payment->paid == true) {
            return response()->json(['message' => 'booking already had awb.'], 406);
        }

        if ($booking->payment->paid == true) {
            return response()->json([
                'message' => 'booking already paid'
            ], 200);
        }

        $booking->payment->paid = ($request->input('status') == '0000') ? true : false;
        $booking->payment->paid_at = new DateTime();
        $booking->payment->paid_channel = $paymentChannel[$request->input('paymentChannel')];
        $booking->payment->paid_response = $request->input('status');
        $booking->payment->save();

        return response()->json([
            'message' => 'booking updated'
        ], 200);
    }

    private function getPaymentStatus($transid)
    {
        $words = sha1('7982'.'sIp41FgKqMtc'.$transid);

        $body = ['form_params' => [
            'MALLID' => '7982',
            'CHAINMERCHANT' => 'NA',
            'SESSIONID' => $transid,
            'TRANSIDMERCHANT' => $transid,
            'WORDS' => $words
        ]];

        $request = $this->client->post('https://gts.doku.com/Suite/CheckStatus', $body);

        $response = $request ? $request->getBody()->getContents() : null;
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        $json = json_decode($json, true);
        return $json;
    }
    //20210323 - TID: U9LgjemB - KIBAR


}
