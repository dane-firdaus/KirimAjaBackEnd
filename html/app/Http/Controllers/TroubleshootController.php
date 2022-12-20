<?php

namespace App\Http\Controllers;

use App\AJCBookingLog;
use App\Booking;
use App\Events\OrderReceiptEvent;
use App\PaymentCart;
use App\PaymentRequest;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TroubleshootController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWTDashboard');
        $this->client = new Client();
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

}
