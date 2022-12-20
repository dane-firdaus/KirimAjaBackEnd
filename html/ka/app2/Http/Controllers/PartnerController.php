<?php

namespace App\Http\Controllers;

use App\Booking;
use App\BookingCorporate;
use App\BookingCorporateSession;
use App\BookingDetailCorporate;
use App\Corporate;
use App\CorporatePayment;
use App\CorporateUser;
use App\DeliveryPoint;
use App\MasterDistrictPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class PartnerController extends Controller
{
    public function __construct() {
        $this->middleware('APITokenCorporate', ['except' => ['login']]);
        $this->corporateUser = auth('corporate-api')->user();
    }

    public function login(Request $request)
    {
        $param = $request->only('username', 'password');

        $user = CorporateUser::where('username', $param['username'])->first();

        if (is_null($user)) {
            return response()->json(['message' => 'user not found'], 200);
        }

        if ($user->is_active == false) {
            return response()->json(['message' => 'user not active'], 200);
        }

        if (!Hash::check($param['password'], $user->password)) {
            return response()->json(['message' => 'invalid authentication'], 401);
        }
		
        if (!$token = auth('corporate-api')->attempt($param)) {            
            return response()->json(['message' => 'Email atau Password tidak sesuai.'], 401);
        }

        return response()->json([
            'message' => 'success', 
            'token' => $token, 
        ], 200);
    }

    public function corporateProfile(Request $request)
    {
        $corporate = Corporate::find(auth('corporate-api')->user()->corporate_id);
        return response()->json($corporate, 200);
    }

    public function getDeliveryPoint(Request $request)
    {
        $deliveryPoint = DeliveryPoint::select('name','id')->where('name', 'ILIKE', '%tebet%')->orWhere('name', 'ILIKE', '%jurumudi%')->get();
        return response()->json($deliveryPoint, 200);
    }

    public function getPricing(Request $request)
    {
        $masterPrice = MasterDistrictPrice::select('origin','destination','price')->where([
            ['origin', 'ILIKE', '%'.$request->input('origin').'%'],
            ['destination', 'ILIKE', '%'.$request->input('destination').'%'],
        ])->first();

        return response()->json($masterPrice, 200);
    }

    public function getMasterLocation(Request $request)
    {
        $param = $request->all();
        $type = [
            'city' => 'origin',
            'district' => 'destination'
        ];
        $masterPlace = MasterDistrictPrice::select(DB::raw('TRIM('.$type[$param['type']].') as '.$type[$param['type']]))->orderBy($type[$param['type']], 'ASC')->distinct()->pluck($type[$param['type']]);
        return response()->json($masterPlace, 200);
    }

    public function preBooking(Request $request)
    {

        $parameter = $request->all();

        $corporation = Corporate::find(auth('corporate-api')->user()->corporate_id);

        $masterPrice = MasterDistrictPrice::where([
            ['origin', 'ILIKE', '%'.$parameter['origin']['city'].'%'],
            ['destination', 'ILIKE', '%'.$parameter['destination']['district'].'%'],
        ])->first();

        if (is_null($masterPrice)) {
            return response()->json(['message' => 'maaf, kami tidak kota awal atau kota tujuan'], 406);
        }

        $booking = new BookingCorporate();
        $booking->corporate_id = $corporation->id;
        $booking->corporate_user_id = auth('corporate-api')->user()->id;
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
        $booking->booking_destination_city = $parameter['destination']['district'];
        $booking->booking_destination_zip = $parameter['destination']['zip'];
        $booking->booking_destination_contact = $parameter['destination']['contact'];
        $booking->booking_destination_phone = $parameter['destination']['phone'];
        $booking->save();

        $totalCost = 0;
        $totalWeight = 0;
        $serviceFee = $masterPrice->price;
        for ($i=0; $i < sizeof($parameter['shipment']); $i++) { 
            $item = $parameter['shipment'][$i];
            $detail = new BookingDetailCorporate();
            $detail->booking_id = $booking->id;
            $detail->package_description = $item['description'];
            $detail->package_length = $item['length'];
            $detail->package_width = $item['width'];
            $detail->package_height = $item['height'];
            
            $weight = ($item['weight'] - floor($item['weight']) > 0.3) ? ceil($item['weight']) : floor($item['weight']);
            $volume = $item['length']*$item['width']*$item['height']/6000;
            $detail->package_actual_weight = $item['weight'];
            $detail->package_actual_volume = $volume;
            $volume = ($volume - floor($volume) > 0.3) ? ceil($volume) : floor($volume);
            $detail->package_weight = $weight;
            $detail->package_volume = $volume;
            $detail->package_quantity = $item['piece'];
            $detail->save();

            if ($detail->package_weight < $detail->package_volume) {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_volume;
                $totalWeight += $detail->package_quantity * $detail->package_volume;
            } else {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_weight;
                $totalWeight += $detail->package_quantity * $detail->package_weight;
            }
        }

        $response = [];
        $response['data'] = $booking;
        $response['price'] = [  
            'shipment' => $totalCost,
            'weight' => $totalWeight,
            'currency' => 'IDR'
        ];

        $sessionId = Str::uuid();
        $session = new BookingCorporateSession();
        $session->session_id = $sessionId;
        $session->corporate_id = $corporation->id;
        $session->total_cost = $totalCost;
        $session->total_chargeable = $totalWeight;
        $session->save();

        $response['sessionId'] = $sessionId;

        return response()->json($response, 201);
    }

    public function finalBooking(Request $request)
    {
        $corporation = Corporate::find(auth('corporate-api')->user()->corporate_id);
        $bookingId = $request->input('bookingId');
        $booking = BookingCorporate::with(['corporate'])->where([
            'id' => $bookingId,
            'booking_valid' => false,
            'corporate_id' => $corporation->id
        ])->first();

        $sessionId = $request->input('bookingSession');
        $session = BookingCorporateSession::where([
            'session_id' => $sessionId,
            'corporate_id' => $corporation->id,
            'booking_corporate_id' => null
        ])->first();
        
        if (is_null($booking) || is_null($session)) {
            return response()->json([
                'message' => 'maaf, kami tidak dapat menemukan data anda'
            ], 406);
        }

        $dropPoint = $request->input('dropPointId');
        $deliveryPoint = DeliveryPoint::where('id', $dropPoint)->first();

        if (is_null($deliveryPoint)) {
            return response()->json([
                'message' => 'maaf, kami tidak dapat menemukan drop point yang anda berikan'
            ], 406);
        }

        $payment = new CorporatePayment();
        $payment->booking_id = $booking->id;
        $payment->payment_type = $booking->corporate->payment_type;
        $payment->amount = $session->total_cost;
        $payment->tax = number_format($session->total_cost*1/100, 2, '.', '');
        $payment->comission_amount = $session->total_cost*$booking->corporate->commission/100;
        $payment->comission_by = $booking->corporate->commission;
        $payment->save();

        $booking->booking_code = 'KACP-'.strtoupper(Str::random(5));
        $booking->booking_delivery_point_id = $deliveryPoint->id;
        $booking->booking_valid = true;
        $booking->save();

        $session->booking_corporate_id = $booking->id;
        $session->save();
        
        return response()->json($booking, 201);
    }

    public function myBooking(Request $request)
    {
        $corporate = auth('corporate-api')->user();
        $bookings = BookingCorporate::where('corporate_id', $corporate->corporate_id)->orderBy('id','desc')->get();
        return response()->json($bookings);
    }

    public function bookingDetail(Request $request, $bookingCode)
    {
        $booking = BookingCorporate::with('detail')->where([
            'booking_code' => $bookingCode,
            'corporate_id' => $this->corporateUser->id
        ])->first();

        return response()->json($booking, 201);
    }

    public function myInvoice(Request $request)
    {
        if ($this->corporateUser->payment_type != 'terms') {
            abort(404);
        }

        if ($request->has('status')) {
            $invoice = BookingCorporate::with(['detail', 'payment'])->whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->where('corporate_id', $this->corporateUser->id)->get();
        } else {
            $invoice = BookingCorporate::with(['detail', 'payment'])->where('corporate_id', $this->corporateUser->id)->get();
        }

        return response()->json($invoice, 200);
    }
}
