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

use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use GuzzleHttp\HandlerStack;
// /**
//  * @method requestOTP(string $phoneNumber)
//  * @method registerUser(array $data)
//  *
//  */

class DigiAsiaService
{
    private $token = 'WLu28cXFYvrdtQ7KFNxDUI3hpufmj+EbNknAEL9i7pfdjx69s/lnu3YSScaxUv+7Iere9Or5f1AvNC3rO8l+U3gkcU87vUrlHu6llGJeZiolpM2mD1ZePTlPyjVrArkmlK5Ui8vnGmu55anh2jq2Y4KD9HIj2FI8ENzfFqPX3/vmVH2e8ImkxsDuK1Ot+oH6BVxUKThhqcVPFfv3Qe52AA==';

    private $client_id = "c3736b8118b64f7cac45c116c4059e3f";
    private $client_secret = "MmM1OTllZDYtZWQ5OC00NDk1LWE2MWMtOWVlOTUxYmNkMGNi";
    private $hmacKey = "eyJvcmciOiI1ZjY2YzEyMzMxODYwMjAwMDFmN2U0YjMiLCJpZCI6Ijc1MWY2MzRhZWE5OTRkMzhhMzM0MzZlZTkyYjI3ZTBkIiwiaCI6Im11cm11cjY0In0=";
    private $hmacSecret = "NmExNDljYjRlMDM3NDVhMzk0YmMzODVlNjI2NDU5Nzg=";
    private $origin = "175.106.11.86";
    private $host_url = "https://apigw-devel.kaspro.id";
    private $auth_url = "/authentication-server/oauth/token";
    private $stack = null;

    public function __construct()
    {
        $reauth_client = new Client([
            // URL for access_token request
            'base_uri' => $this->host_url.$this->auth_url,
        ]);
        $reauth_config = [
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret
        ];
        $grant_type = new ClientCredentials($reauth_client, $reauth_config);
        $oauth = new OAuth2Middleware($grant_type);

        $this->stack = HandlerStack::create();
        $this->stack->push($oauth);
    }

    private function generateSignature($method, $path_url){
        $date = new \DateTime();
        $date = $date->format('D, d M Y H:i:s')." GMT";
        //$date = "Mon, 04 Oct 2021 07:00:30 GMT";

        $stringToSign = "(request-target): ". $method ." ". $path_url."\n";
        $stringToSign = $stringToSign . "date: " . $date;

        $sig = hash_hmac('sha256', $stringToSign, $this->hmacSecret, true);
        $sig_base64 = base64_encode($sig);
        $sig_url = urlencode($sig_base64);
        $sig_kaspro = 'Signature keyId="'.$this->hmacKey.'",algorithm="hmac-sha256",headers="(request-target) date",signature="'.$sig_url.'"';

        return array(
            'date' => $date,
            'sig_kaspro' => $sig_kaspro
        );
    }

    public function requestOTP($phoneNumber)
    {
        $method = "get";
        $path_url = "/pil-partner-get-otp/";
        $queryParam = "?mobileNumber=".$phoneNumber;

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);

        try{
            $response = $client->get($this->host_url.$path_url.$queryParam);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }
        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/' . $phoneNumber . '/otpv2';
        //
        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept-Language' => 'ID',
        //     'token' => $this->token,
        // ])->get($endpoint);
        //
        // return json_decode($response->body());
    }

    public function validateOTP($phoneNumber, $otp)
    {
        $method = "post";
        $path_url = "/validate-otp/";

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);


        try {
            $response = $client->post($this->host_url.$path_url, [
                'json' => [
                    'mobileNumber' => $phoneNumber,
                    'otp' => $otp
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }

        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/' . $phoneNumber . '/otpv2/validate';
        //
        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept-Language' => 'ID',
        //     'token' => $this->token,
        // ])->post($endpoint, [
        //     'otp' => $otp
        // ]);
        //
        // return json_decode($response->body());
    }

    public function registerUser($data)
    {
        $method = "post";
        $path_url = "/register-customer/";
        $request_id = strtoupper(Str::random(16));

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);
        // return json_decode($response->getBody(), true);

        try {
            $param = [
                'requestId' => $request_id,
                'firstName' => $data['firstName'],
                'middleName' => "",
                'lastName' => $data['lastName'],
                'email' => $data['email'],
                'mobileNumber' => $data['phone'],
                'address' => "",
                'city' => "",
                'province' => "",
                'country' => "",
                'postalCode' => "0"
            ];

            $response = $client->post($this->host_url.$path_url, [
                'json' => $param,
            ]);

            $recordRequest = new TrxRequestDigiAsia();
            $recordRequest->request_id = $request_id;
            $recordRequest->request_body = json_encode($param);
            $recordRequest->response_message = $response->getBody();
            $recordRequest->save();

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }


        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/746011481231/partner/subscribers';
        // $request_id = strtoupper(Str::random(16));
        // $password = '1234';
        //
        // $param = [
        //     "subscriber" => [
        //         "resident-address" => [
        //             "specific-address" => "",
        //             "region-code" => "",
        //             "coordinates" => "",
        //             "postal-code" => "",
        //             "city-code" => ""
        //         ],
        //         "account-name" => $data['name'],
        //         "authorized-email" => $data['email'],
        //         "authorized-mobile" => $data['phone'],
        //         "first-name" => $data['firstName'],
        //         "middle-name" => "",
        //         "last-name" => $data['lastName']
        //     ],
        //     "auth" => [
        //         "password" => $password
        //     ],
        //     "request-id" => $request_id
        // ];
        //
        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'Accept' => 'application/json',
        //     'token' => $this->token,
        // ])->post($endpoint, $param);

        // $recordRequest = new TrxRequestDigiAsia();
        // $recordRequest->request_id = $request_id;
        // $recordRequest->request_body = json_encode($param);
        // $recordRequest->response_message = $response->body();
        // $recordRequest->save();

        // return json_decode($response->body(), true);
    }

    public function accountInquiry($data)
    {
        $method = "get";
        $path_url = "/customer-account-inquiry/";
        $queryParam = "?mobileNumber=".$data['phone'];

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);

        try{
            $response = $client->get($this->host_url.$path_url.$queryParam);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }

        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/746011481231/partner/subscriber/wallet?';
        //
        // $response = Http::withHeaders([
        //     'token' => $this->token,
        //     'Accept' => 'application/json'
        // ])->get($endpoint, [
        //     'msisdn' => $data['phone'],
        //     'firstName' => $data['firstName'],
        //     'lastName' => $data['lastName']
        // ]);
        //
        // return json_decode($response->body(), true);
    }

    public function accountValidate($phoneNumber)
    {
        $method = "get";
        $path_url = "/customer-account-inquiry/";
        $queryParam = "?mobileNumber=".$phoneNumber;

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);

        try{
            $response = $client->get($this->host_url.$path_url.$queryParam);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }
        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$phoneNumber.'/validate';
        //
        // $response = Http::withHeaders([
        //     'token' => $this->token,
        //     'Accept' => 'application/json'
        // ])->get($endpoint);
        //
        // return json_decode($response->body(), true);
    }

    public function payment($phoneNumber, $amount, $booking)
    {
        $transId = 'KA-PAY-'.$booking->booking_code.time();
        $paymentReq = new PaymentRequest();
        $paymentReq->booking_id = $booking->id;
        $paymentReq->transid = $transId;
        $paymentReq->save();

        $method = "post";
        $path_url = "/pil-transaction-payment-to-merchant/";

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);


        try {
            $response = $client->post($this->host_url.$path_url, [
                'json' => [
                    'requestId' => $transId,
                    'mobileNumber' => $phoneNumber,
                    'payments' => [
                        // {
                        'pocketId' => '2',
                        'amount' => $amount,
                        'reference' => $booking->booking_code
                        // }
                    ],
                    'destination' => '746011481231'
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     return json_decode($e->getResponse()->getBody());
                //     // echo print_r($e->getResponse());
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
        }

        // 318012346083
        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$accountNumber.'/payments/mpqr';
        // $userWallet = WalletAccount::where('user_id', auth()->user()->id)->first();
        // $response = Http::withHeaders([
        //     'token' => $this->token,
        //     'Accept' => 'application/json'
        // ])->post($endpoint, [
        //     'auth' => [
        //         'partner-token' => $userWallet->partner_token
        //     ],
        //     'payments' => [
        //         [
        //             'reference' => $booking->booking_code,
        //             'amount' => $amount,
        //             'pocket-id' => '2'
        //         ]
        //     ],
        //     'destination' => '746011481231',
        //     'request-id' => $transId
        // ]);

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

        // return json_decode($response->body(), true);
    }

    public function payment2($phoneNumber, $amount, $booking="")
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

        $method = "post";
        $path_url = "/pil-transaction-payment-to-merchant/";

        $sig = $this->generateSignature($method, $path_url);

        $header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'KasPro-Signature' => $sig['sig_kaspro'],
            'Date' => $sig['date'],
            'Origin' => $this->origin,
            'Partner-Key' => $this->hmacKey,
        ];

        //This is the normal Guzzle client that you use in your application
        $client = new Client([
            'handler' => $this->stack,
            'auth'    => 'oauth',
            'headers' => $header
        ]);


        try {
            $response = $client->post($this->host_url.$path_url, [
                'json' => [
                    'requestId' => $transId,
                    'mobileNumber' => $phoneNumber,
                    'payments' => [
                        // {
                        'pocketId' => '2',
                        'amount' => $amount,
                        'reference' => $transId
                        // }
                    ],
                    'destination' => '746011481231'
                ],
            ]);

            //echo '<pre>';print_r($response);echo '</pre>';exit;

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {
            if ($e->hasResponse()){
                // if ($e->getResponse()->getStatusCode() == '400') {
                //     // return json_decode($e->getResponse());
                //     echo "Got response 401";
                // }
                return json_decode($e->getResponse()->getBody(), true);
            }
            // echo "Got response 402";
        } catch (\Exception $e) {
            // if ($e->hasResponse()){
            //     // if ($e->getResponse()->getStatusCode() == '400') {
            //     //     return json_decode($e->getResponse()->getBody());
            //     //     // echo print_r($e->getResponse());
            //     // }
            //     return json_decode($e->getResponse()->getBody(), true);
            // }
        }

        // // 318012346083
        // $endpoint = 'http://dev.kaspro.id/xT4F32kgPE/'.$accountNumber.'/payments/mpqr';
        // $userWallet = WalletAccount::where('user_id', auth()->user()->id)->first();
        // $response = Http::withHeaders([
        //     'token' => $this->token,
        //     'Accept' => 'application/json'
        // ])->post($endpoint, [
        //     'auth' => [
        //         'partner-token' => $userWallet->partner_token
        //     ],
        //     'payments' => [
        //         [
        //             'reference' => $transId,
        //             'amount' => $amount,
        //             'pocket-id' => '2'
        //         ]
        //     ],
        //     'destination' => '746011481231',
        //     'request-id' => $transId
        // ]);


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

        // return json_decode($response->body(), true);
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
