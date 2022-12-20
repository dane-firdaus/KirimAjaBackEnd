<?php

namespace App\Service;

use App\PaymentRequest;
use App\TrxRequestDigiAsia;
use App\WalletAccount;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


// /**
//  * @method requestOTP(string $phoneNumber)
//  * @method registerUser(array $data)
//  *
//  */

class DigiAsiaService
{
    private $token = 'WLu28cXFYvrdtQ7KFNxDUI3hpufmj+EbNknAEL9i7pfdjx69s/lnu3YSScaxUv+7Iere9Or5f1AvNC3rO8l+U3gkcU87vUrlHu6llGJeZiolpM2mD1ZePTlPyjVrArkmlK5Ui8vnGmu55anh2jq2Y4KD9HIj2FI8ENzfFqPX3/vmVH2e8ImkxsDuK1Ot+oH6BVxUKThhqcVPFfv3Qe52AA==';

    public function __construct()
    {
    }

    public function requestOTP($phoneNumber)
    {
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/' . $phoneNumber . '/otpv2';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept-Language' => 'ID',
            'token' => $this->token,
        ])->get($endpoint);

        return json_decode($response->body());
    }

    public function validateOTP($phoneNumber, $otp)
    {
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/' . $phoneNumber . '/otpv2/validate';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept-Language' => 'ID',
            'token' => $this->token,
        ])->post($endpoint, [
            'otp' => $otp
        ]);

        return json_decode($response->body());
    }

    public function registerUser($data)
    {
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/746011481231/partner/subscribers';
        $request_id = strtoupper(Str::random(16));
        $password = '1234';

        $param = [
            "subscriber" => [
                "resident-address" => [
                    "specific-address" => "",
                    "region-code" => "",
                    "coordinates" => "",
                    "postal-code" => "",
                    "city-code" => ""
                ],
                "account-name" => $data['name'],
                "authorized-email" => $data['email'],
                "authorized-mobile" => $data['phone'],
                "first-name" => $data['firstName'],
                "middle-name" => "",
                "last-name" => $data['lastName']
            ],
            "auth" => [
                "password" => $password
            ],
            "request-id" => $request_id
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'token' => $this->token,
        ])->post($endpoint, $param);

        $recordRequest = new TrxRequestDigiAsia();
        $recordRequest->request_id = $request_id;
        $recordRequest->request_body = json_encode($param);
        $recordRequest->response_message = $response->body();
        $recordRequest->save();

        return json_decode($response->body(), true);
    }

    public function accountInquiry($data)
    {

        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/746011481231/partner/subscriber/wallet?';

        $response = Http::withHeaders([
            'token' => $this->token,
            'Accept' => 'application/json'
        ])->get($endpoint, [
            'msisdn' => $data['phone'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName']
        ]);

        return json_decode($response->body(), true);
    }

    public function accountValidate($phoneNumber)
    {
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$phoneNumber.'/validate';

        $response = Http::withHeaders([
            'token' => $this->token,
            'Accept' => 'application/json'
        ])->get($endpoint);

        return json_decode($response->body(), true);
    }

    public function payment($accountNumber, $amount, $booking)
    {
        $transId = 'KA-PAY-'.$booking->booking_code.time();
        $paymentReq = new PaymentRequest();
        $paymentReq->booking_id = $booking->id;
        $paymentReq->transid = $transId;
        $paymentReq->save();

        // 318012346083
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$accountNumber.'/payments/mpqr';
        $userWallet = WalletAccount::where('user_id', auth()->user()->id)->first();
        $response = Http::withHeaders([
            'token' => $this->token,
            'Accept' => 'application/json'
        ])->post($endpoint, [
            'auth' => [
                'partner-token' => $userWallet->partner_token
            ],
            'payments' => [
                [
                    'reference' => $booking->booking_code,
                    'amount' => $amount,
                    'pocket-id' => '2'
                ]
            ],
            'destination' => '746011481231',
            'request-id' => $transId
        ]);

        // Log::info(json_encode([
        //     'auth' => [
        //         'partner-token' => $userWallet->partner_token
        //     ],
        //     'payments' => [
        //         [
        //             'reference' => $bookingCode,
        //             'amount' => $amount,
        //             'pocket-id' => '2'
        //         ]
        //     ],
        //     'destination' => '318012346083',
        //     'request-id' => 'KA-PAYMENT-'.$bookingCode.time()
        // ]));

        return json_decode($response->body(), true);
    }

    public function payment2($accountNumber, $amount, $booking="")
    {

        $transId = 'KA-PAY-'.strtoupper(Str::random(12)).time();
        $paymentReq = new PaymentRequest();

        if(!empty($booking->id))
        {
            $paymentReq->booking_id = $booking->id;
        }
        if(!empty($booking['cart']))
        {
            $paymentReq->cart_ids = json_encode($booking['cart']);
        }

        $paymentReq->transid = $transId;
        $paymentReq->save();

        // 318012346083
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$accountNumber.'/payments/mpqr';
        $userWallet = WalletAccount::where('user_id', auth()->user()->id)->first();
        $response = Http::withHeaders([
            'token' => $this->token,
            'Accept' => 'application/json'
        ])->post($endpoint, [
            'auth' => [
                'partner-token' => $userWallet->partner_token
            ],
            'payments' => [
                [
                    'reference' => $transId,
                    'amount' => $amount,
                    'pocket-id' => '2'
                ]
            ],
            'destination' => '746011481231',
            'request-id' => $transId
        ]);


        // Log::info(json_encode([
        //     'auth' => [
        //         'partner-token' => $userWallet->partner_token
        //     ],
        //     'payments' => [
        //         [
        //             'reference' => $bookingCode,
        //             'amount' => $amount,
        //             'pocket-id' => '2'
        //         ]
        //     ],
        //     'destination' => '318012346083',
        //     'request-id' => 'KA-PAYMENT-'.$bookingCode.time()
        // ]));

        return json_decode($response->body(), true);
    }
    public function transactions($accountNumber, $startDate, $endDate)
    {
        $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$accountNumber.'/report/all_transaction';

        $response = Http::withHeaders([
            'token' => $this->token,
            'Accept' => 'application/json'
        ])->post($endpoint, [
            'date-from' => $startDate,
            'date-to' => $endDate,
        ]);

        return json_decode($response->body(), true);
    }
}
