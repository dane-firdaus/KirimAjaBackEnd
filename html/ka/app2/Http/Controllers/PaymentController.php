<?php

namespace App\Http\Controllers;

use App\AJCBookingLog;
use App\Booking;
use App\DOKULog;
use App\DOKUNotify;
use App\Events\OrderReceiptEvent;
use App\MarketplaceOrder;
use App\Payment;
use App\PaymentCart;
use App\PaymentRequest;
use App\SubConsoleTransaction;
use App\User;
use App\VoucherUsage;
use App\VourcherData;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\VourcherDetail; //20210329 - TID: 3hZhpizs - KIBAR

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT', ['except' => [
            'getPaymentProof', 'marketplacePayment', 'redirectPayment', 'notifyPayment', 'paymentVa']
        ]);
    }

    public function paymentVa(Request $request)
    {
        $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
        $monthInIndonesia = [
            'Jan' => 'Januari', 
            'Feb' => 'Februari', 
            'Mar' => 'Maret', 
            'Apr' => 'April', 
            'May' => 'Mei', 
            'Jun' => 'Juni', 
            'Jul' => 'Juli',
            'Aug' => 'Agustus',
            'Sep' => 'September',
            'Oct' => 'Oktober',
            'Nov' => 'November',
            'Dec' => 'Desember'
        ];

        $day = date('D', time());
        $month = date('M', time());
        
        $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());
        $booking = Booking::with(['details', 'payment', 'shipment', 'user:id,email'])->whereRaw('booking_code = ?', 'nDWLuqIlt9')->first();

        return view('payment_redirect_va', [
            'paidDate' => $paidDate,
            'paidAt' => date('H:m', time()),
            'success' => true,
            'booking' => $booking,
            'payment' => $booking
        ]);
    }

    public function paymentOption(Request $request)
    {
        $user = auth()->user();
        $bookingCode = $request->input('bookingId');
        if ($request->isMethod('GET')) {
            $bookings = Booking::with(['details','payment','exceed'])->whereIn('id', json_decode($bookingCode))->get();
            
            if (is_null($bookings)) {
                return response()->json(['message' => 'Booking not found']);
            }

            // foreach ($bookings as $booking) {
            //     if (isset($booking->exceed)) {
            //         if ($booking->exceed->booking_approved == false) {
            //             return view('payment.exceed-info');
            //         }
            //     }
            // }

            return view('payment_option');
        } else if ($request->isMethod('POST')) {
            $file = $request->file('proof_of_payment');
            $bookings = Booking::with(['details','payment'])->whereIn('id', json_decode($bookingCode))->get();
            $filename =  time().'_'.$user->email.'.'.$file->getClientOriginalExtension();
            
            $path = Storage::putFileAs(
                'paymentProof', $file, $filename
            );

            if ($path) {
                $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
                $monthInIndonesia = [
                    'Jan' => 'Januari', 
                    'Feb' => 'Februari', 
                    'Mar' => 'Maret', 
                    'Apr' => 'April', 
                    'May' => 'Mei', 
                    'Jun' => 'Juni', 
                    'Jul' => 'Juli',
                    'Aug' => 'Agustus',
                    'Sep' => 'September',
                    'Oct' => 'Oktober',
                    'Nov' => 'November',
                    'Dec' => 'Desember'
                ];

                $day = date('D', time());
                $month = date('M', time());
                
                $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());

                $payment = $bookings[0]->payment;
                $payment->paid = true;
                $payment->paid_at = new DateTime();
                $payment->payment_proof = $filename;
                $payment->save();

                $ajcController = new AJCController();
                $storeBooking = $ajcController->storeBooking($bookings[0]->id);
                
                return view('payment_redirect', [
                    'paidDate' => $paidDate,
                    'paidAt' => date('H:i', time()),
                    'success' => true,
                    'booking' => $bookings[0],
                    'payment' => $payment,
                    'connote' => $storeBooking->detail->cnote_no
                ]);
            }
            //return response()->json($bookings[0]->payment);
        }
    }

    public function marketplacePayment(Request $request)
    {
        $user = auth()->user();
        $bookingCode = $request->input('bookingId');
        if ($request->isMethod('GET')) {
            $booking = MarketplaceOrder::whereIn('id', json_decode($bookingCode))->get();
            
            if (is_null($booking)) {
                return response()->json(['message' => 'Booking not found']);
            }

            return view('payment_option_kirimmakanan', [
                'bookingId' => $bookingCode
            ]);
        } else if ($request->isMethod('POST')) {
            $file = $request->file('proof_of_payment');
            //$bookings = Booking::with(['details','payment'])->whereIn('id', json_decode($bookingCode))->get();
            $booking = MarketplaceOrder::with(['details', 'details.product'])->whereIn('id', json_decode($bookingCode))->get();
            $filename =  'kirimmakanan_'.time().'_'.$booking[0]->booking.'.'.$file->getClientOriginalExtension();
            
            $path = Storage::putFileAs(
                'paymentProof', $file, $filename
            );

            if ($path) {
                $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
                $monthInIndonesia = [
                    'Jan' => 'Januari', 
                    'Feb' => 'Februari', 
                    'Mar' => 'Maret', 
                    'Apr' => 'April', 
                    'May' => 'Mei', 
                    'Jun' => 'Juni', 
                    'Jul' => 'Juli',
                    'Aug' => 'Agustus',
                    'Sep' => 'September',
                    'Oct' => 'Oktober',
                    'Nov' => 'November',
                    'Dec' => 'Desember'
                ];

                $day = date('D', time());
                $month = date('M', time());
                
                $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());
                
                $booking[0]->is_paid = true;
                $booking[0]->paid_at = new DateTime();
                $booking[0]->payment_proff = $filename;
                $booking[0]->save();

                return view('payment_redirect_kirimmakanan', [
                    'paidDate' => $paidDate,
                    'paidAt' => date('H:i', time()),
                    'success' => true,
                    'booking' => $booking[0]
                ]);
            }
        }
    }

    public function getPaymentProof(Request $request)
    {
        $file = $request->input('filename');
        $url = storage_path('app/paymentProof/'.$file);
        $type = File::mimeType($url);
        $realFile = File::get($url);

        $response = Response::stream(function() use($realFile) {
            echo $realFile;
          }, 200, ["Content-Type"=> $type]);
        return $response;
    }

    public function checkPromo(Request $request)
    {
        $promoCode = $request->input('promoCode');
        $voucher = VourcherData::with(['usage' => function ($query) {
            $query->where('is_valid', true);
        }])->where([
            'voucher_code' => $promoCode,
        ])->first();

        if (!is_null($voucher->usage)) {
            return response()->json([
                'message' => 'Voucher tidak valid, sudah digunakan.'
            ], 406);
        } else {
            return response()->json([
                'message' => 'Voucher valid.'
            ], 200);
        }
    }

    //20210224 - TID: 3B23WByr - START
    public function getPromo(Request $request)
    {
        $promoCode = $request->input('promoCode');
        return $this->validatePromo($promoCode); //20210329 - TID: 3hZhpizs - KIBAR
    }
    //20210224 - TID: 3B23WByr - END

    //20210329 - TID: 3hZhpizs - KIBAR
    public function validatePromo($promoCode,$value=0)
    {
        $voucher = VourcherData::where([
            'voucher_code' => $promoCode,
            'active_status' => 1
        ])->first();
        if (is_null($voucher)) {
            return response()->json([
                'message' => 'Maaf, Voucher tidak valid'
            ], 406);
        } else {
            $voucherInfo = VoucherUsage::where('voucher_id', $voucher->id)->get();
            $voucher_value = $voucher->voucher_value;

            if($value > 0)
            {
                $voucher_value = $value;
            }

            if($voucher->one_time_usage == 1)
            {
                $checkBooking = \DB::select("select count(1) as total from trx_booking b inner join trx_voucher_usage vu on b.id = vu.booking_id where b.user_id=:uid and vu.voucher_id=:vid",['uid'=>auth()->user()->id,'vid'=>$voucher->id]);
                if(!empty($checkBooking[0]))
                {
                    if($checkBooking[0]->total > 0) {
                        return response()->json([
                            'message' => 'Maaf, Anda telah menggunakan Voucher ini'
                        ], 406);
                    }
                }
            }
            if(strtotime(date('Y-m-d H:i')) <  strtotime($voucher->start_date))
            {
                return response()->json([
                    'message' => 'Maaf, masa berlaku Voucher tidak valid'
                ], 406);

            }
            if(strtotime(date('Y-m-d H:i')) > strtotime($voucher->end_date))
            {
                return response()->json([
                    'message' => 'Maaf, masa berlaku Voucher tidak valid'
                ], 406);

            }
            if(($voucher->user_target == 2 || $voucher->user_target == 3))
            {
                $voucherTarget = VourcherDetail::where(['voucher_id'=>$voucher->id,'user_id'=>auth()->user()->id])->first();

                if(empty($voucherTarget->user_id))
                {
                    return response()->json([
                        'message' => 'Maaf, Anda tidak dapat menggunakan Voucher ini'
                    ], 406);
                }
            }
            if(($voucher->quota_unlimited == 0 || is_null($voucher->quota_unlimited)) && count($voucherInfo) >= $voucher->quota )
            {
                return response()->json([
                    'message' => 'Maaf, jumlah penggunaan Voucher telah mencapai maksimal'
                ], 406);
            }
            if(($voucher->budget_unlimited == 0 || is_null($voucher->budget_unlimited)))
            {
                $checkVoucherBooking = \DB::select("select sum(transaction_voucher_amount) as total from  trx_voucher_usage vu where vu.voucher_id=:vid",[ 'vid'=>$voucher->id]);

                if(($checkVoucherBooking[0]->total + $voucher_value) > $voucher->budget_limit)
                {
                    return response()->json([
                        'message' => 'Maaf, penggunaan Voucher telah melebihi kapasitas'
                    ], 406);
                }
            }

            if($voucher->voucher_type == 'percentage')
            {
                if($voucher->voucher_value <= 0 || $voucher->voucher_value > 100)
                {
                    return response()->json([
                        'message' => 'Maaf, Voucher ini tidak dapat digunakan'
                    ], 406);
                }

            }
            return response()->json([
                'type' => $voucher->voucher_type,
                'value' => $voucher->voucher_value,
                'message' => 'Voucher valid'
            ], 200);
        }
    }
    //20210329 - TID: 3hZhpizs - KIBAR

    /* DOKU INTEGRATION */
    public function chargePayment(Request $request)
    {
        $user = auth()->user();
        $bookingCode = $request->input('bookingId');
        if ($request->isMethod('GET')) {
            $bookings = Booking::with(['details','payment','exceed'])->whereIn('id', json_decode($bookingCode))->get();
            
            if (is_null($bookings)) {
                return response()->json(['message' => 'Booking not found']);
            }

            // foreach ($bookings as $booking) {
            //     if (isset($booking->exceed)) {
            //         if ($booking->exceed->booking_approved == false) {
            //             return view('payment.exceed-info');
            //         }
            //     }
            // }

            return view('payment_option');
        } else if ($request->isMethod('POST')) {
            $file = $request->file('proof_of_payment');
            $bookings = Booking::with(['details','payment'])->whereIn('id', json_decode($bookingCode))->get();
            $filename =  time().'_'.$user->email.'.'.$file->getClientOriginalExtension();
            
            $path = Storage::putFileAs(
                'paymentProof', $file, $filename
            );

            if ($path) {
                $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
                $monthInIndonesia = [
                    'Jan' => 'Januari', 
                    'Feb' => 'Februari', 
                    'Mar' => 'Maret', 
                    'Apr' => 'April', 
                    'May' => 'Mei', 
                    'Jun' => 'Juni', 
                    'Jul' => 'Juli',
                    'Aug' => 'Agustus',
                    'Sep' => 'September',
                    'Oct' => 'Oktober',
                    'Nov' => 'November',
                    'Dec' => 'Desember'
                ];

                $day = date('D', time());
                $month = date('M', time());
                
                $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());

                $payment = $bookings[0]->payment;
                $payment->paid = true;
                $payment->paid_at = new DateTime();
                $payment->payment_proof = $filename;
                $payment->save();

                $ajcController = new AJCController();
                $storeBooking = $ajcController->storeBooking($bookings[0]->id);
                
                return view('payment_redirect', [
                    'paidDate' => $paidDate,
                    'paidAt' => date('H:i', time()),
                    'success' => true,
                    'booking' => $bookings[0],
                    'payment' => $payment,
                    'connote' => $storeBooking->detail->cnote_no
                ]);
            }
            //return response()->json($bookings[0]->payment);
        }
        
    }

    /* main payment function, charge means the booking data will be prepare to fit with DOKU parameter */
    public function chargeTransaction(Request $request)
    {
        JWTAuth::parseToken();
        $user = JWTAuth::parseToken()->authenticate();
        
        $bookingCode = $request->input('bookingId');
        $paymentRequest = PaymentRequest::with('payment')->where('booking_id', json_decode($bookingCode)[0])->orderby('id', 'desc')->first();
        $requestDataExist = true;

        if (!is_null($paymentRequest) && $paymentRequest->payment->paid) {
            $bookings = Booking::with(['details','payment'])->whereIn('id', json_decode($bookingCode))->get();
            return view('payment_cart_paid', [
                'bookings' => $bookings
            ]);
        }

        if (!is_null($paymentRequest) && !$request->has('rerequest')) {
            $paymentChannel = [
                41 => 'Bank Mandiri - Virtual Account',
                36 => 'Bank Permata - Virtual Account',
                32 => 'Bank CIMB Niaga - Virtual Account',
                04 => 'DOKU Wallet',
                50 => 'LinkAja',
                53 => 'OVO',
            ];

            if ($paymentRequest->payment->paid_channel != null && $paymentRequest->payment->paid_channel != '15') {
                return view('payment.payment_already_request', [
                    'route' => $request->fullUrlWithQuery(['rerequest' => true]),
                    'bank_vendor' => $paymentChannel[$paymentRequest->payment->paid_channel],
                    'va_account' => $paymentRequest->va_number
                ]);
            }
            
        }

        if ($request->has('rerequest') && $request->input('rerequest') == false) {
            return '--';
        }
        $bookings = [];
        $transaction = [];

        /* this for handling payment in subconsole for pay customer booking */
        if ($request->has('subconsoleTransaction') && $request->input('subconsoleTransaction') == true) {
            $transaction = SubConsoleTransaction::with(['booking.payment'])->where([
                'id' => json_decode($bookingCode)[0],
                'user_id' => $user->id, 
            ])->whereHas('booking.payment', function ($query) {
                $query->where('valid_booking', true);
            })->first();

        } else {
            $bookings = Booking::with(['details','payment'])->whereIn('id', json_decode($bookingCode))->get();
            //array_push($bookings, $booking);
        }
        
        if (is_null($bookings) && is_null($transaction)) {
            return 'booking not found';
        }

        $amount = 0;
        $transId = strtoupper(Str::random(12));
        
        if (!empty($bookings)) {
            foreach ($bookings as $booking) {
                if ($booking->payment->transaction_comission_by == 0) {
                    //20210326 - TID: 3hZhpizs - START
//                    $amount += $booking->payment->transaction_amount;
//                    $amount += $booking->payment->transaction_tax;
                    $amount += $booking->payment->transaction_total_amount;
                    //20210326 - TID: 3hZhpizs - END
                } else {
                    //20210326 - TID: 3hZhpizs - START
//                    $amount += $booking->payment->transaction_amount - $booking->payment->transaction_comission_amount;
//                    $amount += $booking->payment->transaction_tax;
                    $amount += $booking->payment->transaction_total_amount;
                    //20210326 - TID: 3hZhpizs - END
                }
            }
        }

        /* applies if subconsole transaction and calculate the amount to pay based on subconsole commission */
        if (!empty($transaction)) {
            $shipmentCost = $transaction->booking->payment->transaction_amount;
            $shipmentTax = $transaction->booking->payment->transaction_tax;
            $amount += ($shipmentCost+$shipmentTax) - $transaction->transaction_comission_amount;
            array_push($bookings, $transaction->booking);
        }

        /* if the request has promo code on it */
        if ($request->has('promoCode') && $request->input('promoCode') != '') {
            $promoCode = $request->input('promoCode');
            $voucher = VourcherData::with(['usage' => function ($query) {
                $query->where('is_valid', true);
            }])->where([
                'voucher_code' => $promoCode,
            ])->first();
    
            if (!is_null($voucher->usage)) {
                return response()->json([
                    'message' => 'Voucher tidak valid, sudah digunakan.'
                ], 406);
            }

            $useVoucher = new VoucherUsage();
            $useVoucher->booking_id = $bookings[0]->id;
            $useVoucher->voucher_id = $voucher->id;
            $useVoucher->save();

            $voucherValue = $voucher->voucher_value;
            $amount = $amount - $voucherValue;

            /* if the amount to pay is minus, so directly paid without having to PG. Thanks Promo Code! */
            if ($amount < 0) {
                return $this->paymentByCoupon($bookings[0]->id);
            }
        }
        
        $amount = number_format($amount, 2, '.', '');
        $requestDate = date('YmdHms');
        $name = $user->fullname;
        $email = $user->email;
        $mobile = $user->phone;
        $basket = "KirimAja Service,".$amount.',1,'.$amount;

        // $words = sha1($amount.'11565756'.'ATHH5MQkCDWz'.$transId);
        $words = sha1($amount.'7982'.'sIp41FgKqMtc'.$transId);
        $paymentReq = new PaymentRequest();
        $paymentReq->booking_id = $bookings[0]->id;
        $paymentReq->transid = $transId;
        $paymentReq->transaction_amount = $amount; //20210323 - TID: U9LgjemB - KIBAR
        $paymentReq->save();

        $dokuPay = 'https://pay.doku.com/Suite/Receive';
        if (env('APP_ENV') == 'development') {
            $dokuPay = 'https://staging.doku.com/Suite/Receive';
        }
        
        return view('payment_charge', [
            'dokuPay' => $dokuPay,
            'amount' => $amount,
            'transId' => $transId,
            'words' => $words,
            'requestDate' => $requestDate,
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'basket' => $basket
        ]);
    }

    /* will received notification from DOKU if payment success (or even failed :P) */
    public function notifyPayment(Request $request)
    {
        $notify = new DOKUNotify();
        $notify->log = json_encode($request->all());
        $notify->save();

        $dokuResponse = $request;
        if (env('APP_ENV') == 'development') {
            Log::info('notify: '.json_encode($dokuResponse->all()));
        }

        if ($dokuResponse->input('RESULTMSG') == 'SUCCESS') {
            $paymentReq = PaymentRequest::where('transid', $dokuResponse->input('TRANSIDMERCHANT'))->first();
            
            if (is_null($paymentReq)) {
                return response()->json(['message' => 'transaction not found']);
            }

            if ($paymentReq->booking_id == null) {
                $bookingids = json_decode($paymentReq->cart_ids, true);
                foreach ($bookingids as $id) {
                    $bookinInCart = PaymentCart::where('id', $id)->first();
                    $this->bookingIsPaid($bookinInCart->booking_id, $dokuResponse->input('RESPONSECODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                }
            } else {
                $this->bookingIsPaid($paymentReq->booking_id, $dokuResponse->input('RESPONSECODE'), $dokuResponse->input('PAYMENTCHANNEL'));
            }

            return 'paid, thank you!';
        }

        return 'ok';
    }

    /* redirect page after user choose payment option OR if user pay with CC. */
    public function redirectPayment(Request $request)
    {
        $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
        $monthInIndonesia = [
            'Jan' => 'Januari', 
            'Feb' => 'Februari', 
            'Mar' => 'Maret', 
            'Apr' => 'April', 
            'May' => 'Mei', 
            'Jun' => 'Juni', 
            'Jul' => 'Juli',
            'Aug' => 'Agustus',
            'Sep' => 'September',
            'Oct' => 'Oktober',
            'Nov' => 'November',
            'Dec' => 'Desember'
        ];

        $day = date('D', time());
        $month = date('M', time());
        
        $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());

        if ($request->isMethod('GET')) {
            return 'KirimAja';
        }

        $dokuResponse = $request;
        
        $paymentReq = PaymentRequest::where('transid', $dokuResponse->input('TRANSIDMERCHANT'))->first();
        if (is_null($paymentReq)) {
            return response()->json(['message' => 'transaction not found']);
        }

        $paymentReq->paid_channel = $dokuResponse->input('PAYMENTCHANNEL');
        $paymentReq->status_code = $dokuResponse->input('STATUSCODE');
        $paymentReq->va_number = ($dokuResponse->has('PAYMENTCODE')) ? $dokuResponse->input('PAYMENTCODE') : '';
        $paymentReq->save();

        if (env('APP_ENV') == 'development') {
            Log::info('redirect: '.json_encode($dokuResponse->all()));
        }

        $dokuLog = new DOKULog();
        $dokuLog->log = json_encode($dokuResponse->all());
        $dokuLog->save();

        $bookings = [];
        if ($dokuResponse->input('STATUSCODE') == 0000 || $dokuResponse->input('STATUSCODE') == '0000') {    
            /* 
                this scenario only applies if payment with Credit Card 
                OR when user still on DOKU page when pay the Virtual Account and press the "Back to Merchant"
            */
            
            if ($paymentReq->booking_id == null) {
                $cartIds = json_decode($paymentReq->cart_ids, true);
                
                foreach ($cartIds as $id) {
                    $bookinInCart = PaymentCart::where('id', $id)->first();

                    $booking = $this->bookingIsPaid($bookinInCart->booking_id, $dokuResponse->input('STATUSCODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                    array_push($bookings, $booking);
                }
            } else {
                $booking = $this->bookingIsPaid($paymentReq->booking_id, $dokuResponse->input('STATUSCODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                array_push($bookings, $booking);
                $voucherInfo = VoucherUsage::where('booking_id', $booking->id)->first();
                if (!is_null($voucherInfo)) {
                    $voucherInfo->is_valid = true;
                    $voucherInfo->save();
                }
            }

            return view('payment_cart_redirect', [
                'paidDate' => $paidDate,
                'paidAt' => date('H:m', time()),
                'success' => true,
                'bookings' => $bookings,
                'payment' => $booking
            ]);
        } else if ($dokuResponse->input('PAYMENTCHANNEL') != '15' && $dokuResponse->input('STATUSCODE') == '5511') {
            /* 
                this scenario only applies if payment with Virtual Account
            */

            if ($paymentReq->booking_id == null) {
                $cartIds = json_decode($paymentReq->cart_ids, true);
                
                foreach ($cartIds as $id) {
                    $bookinInCart = PaymentCart::where('id', $id)->first();

                    $booking = $this->bookingIsPaid($bookinInCart->booking_id, $dokuResponse->input('STATUSCODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                    array_push($bookings, $booking);
                }
            } else {
                $booking = $this->bookingIsPaid($paymentReq->booking_id, $dokuResponse->input('STATUSCODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                array_push($bookings, $booking);
                $voucherInfo = VoucherUsage::where('booking_id', $booking->id)->first();
                if (!is_null($voucherInfo)) {
                    $voucherInfo->is_valid = true;
                    $voucherInfo->save();
                }
            }

            switch ($dokuResponse->input('PAYMENTCHANNEL')) {
                case '41':
                    $vendor = "Bank Mandiri";
                    $virtualNo = $dokuResponse->input('PAYMENTCODE');
                    break;
                case '36':
                    $vendor = "Bank Permata";
                    $virtualNo = $dokuResponse->input('PAYMENTCODE');
                    break;
                case '32':
                    $vendor = "Bank CIMB Niaga";
                    $virtualNo = $dokuResponse->input('PAYMENTCODE');
                    break;
                default:
                    
                    break;
            }
            return view('payment_cart_redirect_va', [
                'provider' => $vendor,
                'virtualNo' => $virtualNo,
                'paidDate' => $paidDate,
                'paidAt' => date('H:i', time()),
                'success' => true,
                'bookings' => $bookings,
                'amount' => $dokuResponse->input('AMOUNT')
            ]);
        } else {

            return 'payment failed.';
            // if ($paymentReq->booking_id == null) {
            //     $cartIds = json_decode($paymentReq->cart_ids, true);
                
            //     foreach ($cartIds as $id) {
            //         # code...
            //     }
            // } else {
            //     $booking = $this->bookingIsPaid($paymentReq->booking_id, $dokuResponse->input('STATUSCODE'), $dokuResponse->input('PAYMENTCHANNEL'));
                
            //     $voucherInfo = VoucherUsage::where('booking_id', $booking->id)->first();
            //     if (!is_null($voucherInfo)) {
            //         $voucherInfo->is_valid = true;
            //         $voucherInfo->save();
            //     }
            // }

            // return view('payment_redirect', [
            //     'paidDate' => $paidDate,
            //     'paidAt' => date('H:i', time()),
            //     'success' => false,
            //     'booking' => $booking,
            //     'payment' => $booking
            // ]);
        }
    }

    /* ah you already know lah, this for what... promo code people... */
    public function paymentByCoupon($bookingCode)
    {
        $dayInIndonesia = ['Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu', 'Sun' => 'Minggu'];
        $monthInIndonesia = [
            'Jan' => 'Januari', 
            'Feb' => 'Februari', 
            'Mar' => 'Maret', 
            'Apr' => 'April', 
            'May' => 'Mei', 
            'Jun' => 'Juni', 
            'Jul' => 'Juli',
            'Aug' => 'Agustus',
            'Sep' => 'September',
            'Oct' => 'Oktober',
            'Nov' => 'November',
            'Dec' => 'Desember'
        ];

        $day = date('D', time());
        $month = date('M', time());
        
        $paidDate = $dayInIndonesia[$day].', '.date('j', time()).' '.$monthInIndonesia[$month].' '.date('Y', time());

        if (is_array($bookingCode)) {
            $bookings = [];
            foreach ($bookingCode as $id) {
                $booking = $this->bookingIsPaid($id, '0000', '99');
                array_push($bookings, $booking);
            }

            return view('payment_cart_redirect', [
                'paidDate' => $paidDate,
                'paidAt' => date('H:m', time()),
                'success' => true,
                'bookings' => $bookings,
            ]);
        } else {
            $booking = $this->bookingIsPaid($bookingCode, '0000', '99');
            return view('payment_cart_redirect', [
                'paidDate' => $paidDate,
                'paidAt' => date('H:m', time()),
                'success' => true,
                'bookings' => [$booking],
            ]);
        }
    }

    /* Carting (for KA Spartans!)*/

    /* retrieve all items in cart */
    public function getCart(Request $request)
    {
        $carts = PaymentCart::with(['booking:id,booking_code', 'booking.payment'])->whereHas('booking.payment', function ($query) {
            return $query->where('paid', '=', false);
        })->where([
            'user_id' => auth()->user()->id,
            'cart_status' => 1,//20210323 - TID: U9LgjemB - KIBAR
            'payment_request_id' => null
        ])->orderBy('id','desc')->get();

        return response()->json($carts, 200);
    }
    
    /* add item to the cart */
    public function addCart(Request $request)
    {
        $param = $request->only('bookingId');

        $booking = Booking::where('user_id', auth()->user()->id)->whereIn('id', $param['bookingId'])->get();
        if (is_null($booking) || count($booking) == 0) {
            return response()->json(['message' => 'booking not found'], 406);
        }

        //2021022 - TID: q3sSSfFK - START
        $max_cart = 25;
        $carts = PaymentCart::with(['booking:id,booking_code', 'booking.payment'])->whereHas('booking.payment', function ($query) {
            return $query->where('paid', '=', false);
        })->where([
            'user_id' => auth()->user()->id,
            'cart_status' => 1,//20210323 - TID: U9LgjemB - KIBAR
            'payment_request_id' => null
        ])->orderBy('id','desc')->get();

        if(count($carts)+count($booking) > $max_cart)
        {
            //REMARK - BAYU
            return response()->json(['message' => 'Maksimal kapasitas Keranjang adalah '.$max_cart .' Kode Booking'], 406);
            //REMARK - BAYU
            exit;
        }
        //2021022 - TID: q3sSSfFK - START

        foreach ($booking as $item) {
//            PaymentCart::updateOrCreate(
//                ['user_id' => auth()->user()->id, 'booking_id' => $item->id],
//                ['user_id' => auth()->user()->id, 'booking_id' => $item->id]
//            );
            //20210323 - TID: U9LgjemB - KIBAR
            PaymentCart::updateOrCreate(
                ['user_id' => auth()->user()->id, 'booking_id' => $item->id],
                ['user_id' => auth()->user()->id, 'booking_id' => $item->id, 'cart_status'=>1]
            );
            //20210323 - TID: U9LgjemB - KIBAR
        }
        
        return response()->json(['message' => 'Paket kamu berhasil ditambahkan ke keranjang!'], 200);
    }

    /* remove item to the cart */
    public function deleteCart(Request $request)
    {
        $cart = PaymentCart::where([
            'user_id' => auth()->user()->id,
            'id' => $request->input('cartId'),
        ])->first();

        if (is_null($cart)) {
            return response()->json([
                'message' => 'item not found'
            ], 404);
        }

        //20210323 - TID: U9LgjemB - KIBAR
        $cart->cart_status = 0;
        $cart->save();
//        $cart->delete();
        //20210323 - TID: U9LgjemB - KIBAR

        return response()->json([
            'message' => 'item deleted'
        ], 200);
    }

    /* pay the cart yow! */
    public function payCart(Request $request)
    {
        JWTAuth::parseToken();
        $user = JWTAuth::parseToken()->authenticate();

        $carts = PaymentCart::with(['booking:id,booking_code', 'booking.payment'])->whereHas('booking.payment', function ($query) {
            return $query->where('paid', '=', false);
        })->where([
            'user_id' => $user->id,
            'cart_status' => 1,//20210323 - TID: U9LgjemB - KIBAR
            'payment_request_id' => null
        ])->orderBy('id','desc')->get();

        $amount = 0;
        $ids = array();
        $bookings = array();
        $voucher = null;
        /* if the request has promo code on it */
        if ($request->has('promoCode') && $request->input('promoCode') != '') {
            $promoCode = $request->input('promoCode');
            $voucher = VourcherData::with(['usage' => function ($query) {
                $query->where('is_valid', true);
            }])->where([
                'voucher_code' => $promoCode,
            ])->first();
    
            if (!is_null($voucher->usage)) {
                return response()->json([
                    'message' => 'Voucher tidak valid, sudah digunakan.'
                ], 406);
            }

            if (is_null($voucher)) {
                return response()->json([
                    'message' => 'Voucher tidak valid.'
                ], 406);
            }
        }

        foreach ($carts as $cart) {
            //20210326 - TID: 3hZhpizs - START
//            $amount += $cart->booking->payment->transaction_amount - $cart->booking->payment->transaction_comission_amount;
//            $amount += $cart->booking->payment->transaction_tax;
            $amount += $cart->booking->payment->transaction_total_amount;
            //20210326 - TID: 3hZhpizs - END

            if (!is_null($voucher)) {
                $useVoucher = new VoucherUsage();
                $useVoucher->booking_id = $cart->booking->id;
                $useVoucher->voucher_id = $voucher->id;
                $useVoucher->save();
            }

            array_push($ids, $cart->id);
            array_push($bookings, $cart->booking->id);
        }

        if (!is_null($voucher)) {
            $voucherValue = $voucher->voucher_value;
            $amount = $amount - $voucherValue;
        }

        if ($amount < 0) {
            return $this->paymentByCoupon($bookings);
        }

        $transId = strtoupper(Str::random(12));

        $amount = number_format($amount, 2, '.', '');
        $requestDate = date('YmdHms');
        $name = $user->fullname;
        $email = $user->email;
        $mobile = $user->phone;
        $basket = "KirimAja Service,".$amount.',1,'.$amount;

        // $words = sha1($amount.'11565756'.'ATHH5MQkCDWz'.$transId);
        $words = sha1($amount.'7982'.'sIp41FgKqMtc'.$transId);
        $paymentReq = new PaymentRequest();
        $paymentReq->cart_ids = json_encode($ids);
        $paymentReq->transid = $transId;
        $paymentReq->transaction_amount = $amount; //20210323 - TID: U9LgjemB - KIBAR
        $paymentReq->save();

        $dokuPay = 'https://pay.doku.com/Suite/Receive';
        if (env('APP_ENV') == 'development') {
            $dokuPay = 'https://staging.doku.com/Suite/Receive';
        }
        
        return view('payment_charge', [
            'dokuPay' => $dokuPay,
            'amount' => $amount,
            'transId' => $transId,
            'words' => $words,
            'requestDate' => $requestDate,
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'basket' => $basket
        ]);
    }

    /* User Transaction */
    public function myCommission(Request $request)
    {
        $param = $request->only('month', 'range_start', 'range_end');
        $subConsole = [];
        $subConsoleCommissionValue = 0;
        if ($request->has('month')) {
            $transactions = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->whereMonth('created_at', $param['month'])->paginate(10);
            $commissionValue = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->whereMonth('created_at', $param['month'])->sum('transaction_comission_amount');
        } elseif ($request->has('range_start') && $request->has('range_end')) {
            $transactions = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->whereBetween('created_at', [$param['range_start'], $param['range_end']])->paginate(10);
            $commissionValue = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->whereBetween('created_at', [$param['range_start'], $param['range_end']])->sum('transaction_comission_amount');
            if (auth()->user()->user_type == 'subconsole') {
                $subConsole = SubConsoleTransaction::with(['booking','booking.payment'])->whereHas('booking.payment', function ($query) {
                    $query->where('paid', true);
                })->where(['user_id' => auth()->user()->id])->whereBetween('created_at', [$param['range_start'], $param['range_end']])->orderBy('id', 'desc')->paginate(10);
                $subConsoleCommissionValue = SubConsoleTransaction::with(['booking','booking.payment'])->whereHas('booking.payment', function ($query) {
                    $query->where('paid', true);
                })->where(['user_id' => auth()->user()->id])->whereBetween('created_at', [$param['range_start'], $param['range_end']])->orderBy('id', 'desc')->sum('transaction_comission_amount');
            }
        } else {
            $transactions = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->paginate(10);
            $commissionValue = Payment::with(['booking:id,booking_code'])->where(['user_id' => auth()->user()->id, 'paid' => true])->sum('transaction_comission_amount');
        }

        return response()->json([
            'commission' => number_format($commissionValue), 
            'subConsoleCommission' => number_format($subConsoleCommissionValue),
            'transaction' => $transactions, 
            'subconsoleTransaction' => $subConsole], 200);
    }

    public function myTransaction(Request $request)
    {
        $param = $request->only('month', 'range_start', 'range_end');

        
    }

    /* update paid status of booking */
    public function bookingIsPaid($id, $statusCode, $channel)
    {
        $booking = Booking::with(['details', 'payment', 'shipment', 'deliveryPoint', 'subConsole', 'user:id,email,user_type'])->whereRaw('id = ?', $id)->first();
        $booking->payment->paid = ($statusCode == '0000') ? true : false;
        $booking->payment->paid_at = new DateTime();
        $booking->payment->paid_channel = $channel;
        $booking->payment->paid_response = $statusCode;
        $booking->payment->save();

        if ($booking->payment->paid) {
            $ajcBooked = AJCBookingLog::where('booking_id', $booking->id)->first();
            if (is_null($ajcBooked)) {
                $ajcController = new AJCController();
                $ajcController->storeBooking($booking->id);
                event(new OrderReceiptEvent($booking));
            }
        }
        
        $voucherInfo = VoucherUsage::where('booking_id', $booking->id)->first();
        if (!is_null($voucherInfo)) {
            $voucherInfo->is_valid = true;
            $voucherInfo->save();
        }

        return $booking;
    }

    /* Dashboard */
    public function manualConfirmation(Request $request)
    {
        $dokuResponse = $request;
        
        $booking = Booking::with(['details', 'payment', 'shipment', 'user:id,email'])->whereRaw('UPPER(booking_code) = ?', $dokuResponse->input('TRANSIDMERCHANT'))->first();
        if (is_null($booking)) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        if (!is_null($booking->shipment)) {
            return response()->json(['message' => 'booking already had connote.'], 406);
        }

        $booking->payment->paid = ($dokuResponse->input('STATUSCODE') == '0000') ? true : false;
        $booking->payment->paid_at = new DateTime();
        $booking->payment->paid_channel = $dokuResponse->input('PAYMENTCHANNEL');
        $booking->payment->paid_response = $dokuResponse->input('STATUSCODE');
        $booking->payment->save();

        $ajcController = new AJCController();
        $ajcController->storeBooking($booking->id);
        event(new OrderReceiptEvent($booking));

        $booking = Booking::with(['details', 'payment', 'shipment', 'user:id,email'])->whereRaw('UPPER(booking_code) = ?', $dokuResponse->input('TRANSIDMERCHANT'))->first();
        
        return response()->json([
            'message' => 'paid',
            'booking' => $booking,
        ], 200);
    }

    public function bookings(Request $request)
    {
        $ids = $request->input('ids');
        $bookings = $this->getBookings($ids);
        return response()->json($bookings, 200);
    }

    public function getBookings($ids)
    {
        $booking = Booking::with(['details', 'payment', 'shipment', 'user:id,email'])->whereIn('id', $ids)->get();
        //return response()->json($booking, 200);
        return $booking;
    }
}
