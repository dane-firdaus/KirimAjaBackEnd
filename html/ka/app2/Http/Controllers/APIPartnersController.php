<?php

namespace App\Http\Controllers;

use App\AirWaybill;
use App\Booking;
use App\BookingDetail;
use App\BookingExceed;
use App\BranchOffice;
use App\BranchOfficeMapping;
use App\CustomerBooking;
use App\CustomerBookingDetail;
use App\DeliveryPoint;
use App\Destination;
use App\Events\SohibNotificationFromSubConsole;
use App\Events\SubConsoleOrder;
use App\Events\SubConsoleOrderCanceled;
use App\Events\SubConsoleOrderDeleted;
use App\Mail\TestSendGrid;
use App\Payment;
use App\SubConsoleStatus;
use App\SubConsoleTransaction;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class APIPartnersController extends Controller
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->middleware('APITokenJWT', ['except' => ['login']]);
        $this->client = new Client([
            'verify' => false
        ]);

        // $this->client = new Client();
    }

    public function login(Request $request)
    {
        $parameter = $request->only('email', 'password');


        if (!$token = auth('api')->attempt($parameter)) {
            return response()->json(['message' => 'Email atau Password tidak sesuai.'], 401);
        }

        // if(!auth()->user()->verified) {
        //     return response()->json(['message' => 'Please verify your account first.'], 401);
        // }
        return $this->respondWithToken($token);
    }
    /*  use for get awb number before booking
        this no longer in use.
         */
    public function registerBooking(Request $request)
    {
        $appUuid = $request->input('uuid');
        $awbInfo = AirWaybill::where(['app_uuid' => null, 'reserved' => false, 'booking_id' => 0])->first();

        DB::transaction(function () use ($awbInfo, $appUuid) {
            $awbInfo->app_uuid = $appUuid;
            $awbInfo->user_id = auth()->user()->id;
            $awbInfo->reserved = true;
            $awbInfo->save();
        });

        return response()->json($awbInfo, 201);
    }

    /*  booking */
    public function booking(Request $request)
    {
        return $this->newBooking($request);
    }

    /*  generate new booking
        applicable for sohib and subconle*/
    private function newBooking(Request $request)
    {
        $parameter = $request->all();

        $booking = new Booking();
        $booking->user_id = auth()->user()->id;
        $booking->booking_code = Str::random(10);

        //cheona - staging
//        if($booking->user_id == '36340' && $parameter['deliveryPoint'] <> '800017')
        if($booking->user_id == '36362' && $parameter['deliveryPoint'] <> '800017')
        {
            return response()->json(['message' => 'Illegal Drop-point for Cheona'], 406);
        }
        //cheona - staging

        $getPrice = $this->getPrice($request);

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

        $deliveryPoint = DeliveryPoint::find($parameter['deliveryPoint']);
        /* this subconsole query will search sohib and subconsole together */
        $subConsole = User::where(
            'id', $parameter['deliveryPoint']
        )->where('user_type', 'subconsole')->first();

        if (is_null($deliveryPoint) && is_null($subConsole)) {
            return response()->json(['message' => 'Illegal Drop-point/sohib/subconsole'], 406);
        }

        $deliveryPointType = "";
        if (!is_null($deliveryPoint)) {
            $booking->booking_delivery_point_id = $deliveryPoint->id;
            $deliveryPointType = 'droppoint';
        } else {
            $booking->booking_delivery_point_id = $subConsole->id;
            $deliveryPointType = 'subconsole';
        }

        if (array_key_exists('bookingReff', $parameter)) {
            $booking->customer_booking_id = $parameter['bookingReff'];
        }

        if ($deliveryPointType == 'droppoint') {
            $booking->valid = true;
        }

        $booking->save();

        $totalCost = 0;
        $serviceFee = $getPrice[0]['Price1st'];
        $totalWeight = 0;
        for ($i=0; $i < sizeof($parameter['shipment']); $i++) {
            $item = $parameter['shipment'][$i];

            $item['actual_weight'] = $item['weight'];
            $item['weight'] = $this->getRound($item['weight']);

            $detail = new BookingDetail();
            $detail->booking_id = $booking->id;
            $detail->package_description = $item['description'];
            $detail->package_length = $item['length'];
            $detail->package_width = $item['width'];
            $detail->package_height = $item['height'];
            $detail->package_weight = $item['weight'];
            $detail->package_quantity = $item['piece'];

            $item['volume'] = $this->getRound(($detail->package_length * $detail->package_width * $detail->package_height)/6000);
            $item['actual_volume'] = ($detail->package_length * $detail->package_width * $detail->package_height)/6000;

            $detail->package_volume = $item['volume'];

            if (array_key_exists('actual_weight', $item)) {
                $detail->package_actual_weight = $item['actual_weight'];
                $detail->package_actual_volume = $item['actual_volume'];
            }
            if (array_key_exists('commodity_id', $item)) {
                $detail->package_commodity_id = $item['commodity_id'];
            }
            $detail->save();

            if ($detail->package_weight < $detail->package_volume) {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_volume;
                $totalWeight += $detail->package_quantity * $detail->package_volume;
            } else {
                $totalCost += $serviceFee * $detail->package_quantity * $detail->package_weight;
                $totalWeight += $detail->package_quantity * $detail->package_weight;
            }
        }

        $comission = 0;
        $subConsoleCommission = 0;
        if ($deliveryPointType == "droppoint") {
            if ($totalWeight > 10) {
                $comission = 25;
            } else {
                $comission = 50;
            }
        } else if ($deliveryPointType == "subconsole") {
            if (auth()->user()->user_type == 'customer') {
                if ($totalWeight > 10) {
                    $subConsoleCommission = 25;
                } else {
                    $subConsoleCommission = 50;
                }
            } else {
                if ($totalWeight > 10) {
                    $comission = 15;
                    $subConsoleCommission = 10;
                } else {
                    $comission = 40;
                    $subConsoleCommission = 10;
                }
            }

        }

        $payment = new Payment();
        $payment->user_id = auth()->user()->id;
        $payment->booking_id = $booking->id;
        $payment->transaction_amount = $totalCost;
        if (auth()->user()->user_type == 'customer') {
            $payment->transaction_tax = number_format($totalCost*1/100, 2, '.', '');
            $payment->transaction_comission_amount = 0;
            $payment->transaction_comission_by = 0;
        } else {
            $payment->transaction_tax = number_format($totalCost*1/100, 2, '.', '');
            $payment->transaction_comission_amount = $totalCost*$comission/100;
            $payment->transaction_comission_by = $comission;
        }
        $payment->transaction_total_amount = $totalCost - ($payment->transaction_tax + $payment->transaction_comission_amount);
        $payment->save();

        if ($deliveryPointType == "subconsole") {
            $subConsoleTransaction = new SubConsoleTransaction();
            $subConsoleTransaction->user_id = $subConsole->id;
            $subConsoleTransaction->booking_id = $booking->id;
            $subConsoleTransaction->transaction_comission_amount = $totalCost*$subConsoleCommission/100;
            $subConsoleTransaction->transaction_comission_by = $subConsoleCommission;
            $subConsoleTransaction->save();

            event(new SubConsoleOrder($subConsoleTransaction));
        }

        if ($totalWeight > 10) {
            $bookingExceed = new BookingExceed();
            $bookingExceed->booking_id = $booking->id;
            $bookingExceed->booking_weight = $totalWeight;
            $bookingExceed->save();

        }

        $resp_booking = new \stdClass();

//        dd($payment);

        $resp_booking->code = $booking->booking_code;
        $resp_booking->cost = $payment->transaction_total_amount;
//        $resp_booking->tax = $payment->transaction_tax;
//        $resp_booking->commission = $payment->transaction_comission_amount;
//        $resp_booking->commission_percentage = $payment->transaction_comission_by;
//        $resp_booking->total_cost = $payment->transaction_amount - $payment->transaction_comission_amount;
//        $resp_booking->total_chargeable = $totalWeight;
        $resp_booking->updated = $booking->updated_at;
        $resp_booking->created = $booking->created_at;

        return response()->json($resp_booking, 201);
    }

    /*  generate new booking
        applicable for customer */
    private function newBookingCustomer(Request $request)
    {
        $parameter = $request->all();

        $booking = new CustomerBooking();
        $booking->device_token = $parameter['uuid'];
        $booking->user_id = auth()->user()->id;
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

        $booking->booking_fee = $parameter['serviceFee'];

        /* this subconsole query will search sohib and subconsole together, filter admin user */
        $subConsole = User::whereRaw('id = ? AND user_type <> ?', [$parameter['deliveryPoint'], 'admin'])->first();

        if (is_null($subConsole)) {
            return response()->json(['message' => 'Illegal Drop-point/sohib/subconsole'], 406);
        }

        $booking->booking_delivery_point_id = $subConsole->id;
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

    /*  get user booking
        for customer, it will redirect function to myBookingCustomer */
    public function myBooking()
    {
        // if (auth()->user()->user_type == 'customer') {
        //     return $this->myBookingCustomer();
        // }
        $headers = getallheaders();
        Log::info(json_encode($headers));
        if (strpos($headers['User-Agent'], 'com.garuda-indonesia.GA-DTD') && strpos($headers['User-Agent'], 'build:34')) {
            $bookings = Booking::with(['details.commodity:id,commodity_name', 'payment', 'deliveryPoint', 'subConsole:id,fullname,address,kecamatan,phone', 'subConsole.identityCard:id,user_id,profile_image', 'shipment', 'exceed'])->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(50);
        } else if (array_key_exists('Ka-Agent', $headers) && !is_null($headers['Ka-Agent'])) {
            $bookings = Booking::with(['details.commodity:id,commodity_name', 'payment', 'deliveryPoint', 'subConsole:id,fullname,address,kecamatan,phone', 'subConsole.identityCard:id,user_id,profile_image', 'shipment', 'exceed', 'cart'])->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(10);
        } else {
            $bookings = Booking::with(['details.commodity:id,commodity_name', 'payment', 'deliveryPoint', 'subConsole:id,fullname,address,kecamatan,phone', 'subConsole.identityCard:id,user_id,profile_image', 'shipment', 'exceed'])->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(50);
        }
        return response()->json($bookings);
    }

    public function findMyBooking(Request $request)
    {
        $headers = getallheaders();

        if (array_key_exists('Ka-Agent', $headers) && !is_null($headers['Ka-Agent'])) {
            $bookings = Booking::with(['details', 'payment', 'deliveryPoint', 'subConsole:id,fullname,address,kecamatan,phone', 'shipment', 'exceed', 'cart'])->whereRaw(
                'UPPER(booking_code) = ? AND user_id = ?', [strtoupper($request->input('bookingCode')), auth()->user()->id]
            )->orderBy('id', 'desc')->paginate(50);
        } else {
            $bookings = Booking::with(['details', 'payment', 'deliveryPoint', 'subConsole:id,fullname,address,kecamatan,phone', 'shipment', 'exceed'])->whereRaw(
                'UPPER(booking_code) = ? AND user_id = ?', [strtoupper($request->input('bookingCode')), auth()->user()->id]
            )->orderBy('id', 'desc')->paginate(50);
        }

        return response()->json($bookings);
    }

    public function deleteBooking(Request $request)
    {
        if ($request->has('option') && $request->input('option') == 'delete') {
            $booking = Booking::with(['payment'])->whereRaw(
                'UPPER(booking_code) = ? AND user_id = ?', [strtoupper($request->input('bookingCode')), auth()->user()->id]
            )->first();

            if (is_null($booking)) {
                return response()->json([
                    'message' => 'booking not found'
                ], 404);
            }

            if ($booking->payment->paid == true) {
                return response()->json([
                    'message' => 'Data booking sudah terbayar'
                ], 200);
            }

            $booking->delete();

            return response()->json([
                'message' => 'data deleted'
            ]);
        }
    }

    private function myBookingCustomer()
    {
        $bookings = CustomerBooking::with(['sohib:id,fullname,address,kecamatan,phone','details'])->where('user_id', auth()->user()->id)->orderBy('id','desc')->paginate(50);
        return response()->json($bookings);
    }

    /*  Sohib or Sub-console to get customer booking who assigned to them */
    public function myBookingOffer()
    {
        if (auth()->user()->user_type == 'customer') {
            return response()->json([
                'message' => 'sorry.'
            ], 406);
        }

        $bookings = CustomerBooking::with(['details'])->where([
            'booking_delivery_point_id' => auth()->user()->id,
            'valid' => false
        ])->orderBy('id', 'desc')->paginate(10);
        return response()->json($bookings);
    }

    /*  Sohib or Sub-console to accept or reject the order */
    public function bookingOfferAccaptance(Request $request)
    {
        $param = $request->only('id','booking_id','status','notes');

        $update = CustomerBooking::with(['details'])->where([
            'id' => $param['id'],
            'booking_delivery_point_id' => auth()->user()->id
        ])->first();

        if (is_null($update)) {
            return response()->json(['message' => 'booking not found'], 406);
        }
        if ($param['status'] == 'rejected') {
            $update->booking->valid = false;
        } else {
            $update->booking->valid = true;
        }

        $update->booking->save();
        $update->valid = $param['status'];
        $update->save();

        return response()->json($update, 200);
    }

    /*  get booking detail
        for customer, it will redirect to getCustomerBooking */
    public function getBooking(Request $request)
    {
        // if (auth()->user()->user_type == 'customer') {
        //     return $this->getCustomerBooking($request);
        // }

        $booking = Booking::select(['booking_code','created_at','updated_at','id'])->with(['payment','shipment'])->whereRaw(
            'UPPER(booking_code) = ? AND user_id = ?', [strtoupper($request->input('code')), auth()->user()->id])->
        first();

        if (is_null($booking)) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $resp_booking = new \stdClass();
        $resp_booking->code = $booking->booking_code;
        $resp_booking->cost = $booking->payment->transaction_total_amount;
        $resp_booking->connote = '-';
        $resp_booking->updated = $booking->updated_at;
        $resp_booking->created = $booking->created_at;

        if(!empty($booking->shipment->awb)){
            $resp_booking->connote = $booking->shipment->awb;
        }


        return response()->json($resp_booking);
    }

    public function getCustomerBooking(Request $request)
    {
        $booking = CustomerBooking::with([
            'details','sohib:id,fullname,address,kecamatan,phone'
        ])->where(['id' => $request->input('id'), 'user_id' => auth()->user()->id])->first();

        if (is_null($booking)) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json($booking);
    }

    /*  edit drop point and edit shipment for the owner of shipment
        edit shipment only if shipment status still unpaid */
    public function editBooking(Request $request)
    {
        $booking = Booking::with(['details','payment','deliveryPoint','subConsole','shipment','user'])->where(['id' => $request->input('id')])->first();

        if (is_null($booking)) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($request->has('deliveryPoint')) {
            $subConsole = DeliveryPoint::where('id', $request->input('deliveryPoint'))->first();
            $bookingValid = true;
            $totalCost = $booking->payment->transaction_amount;
            $updatedCommission = 50;

            if (is_null($subConsole)) {
                $subConsole = User::where([
                    'id' => $request->input('deliveryPoint'),
                    'user_type' => 'subconsole'
                ])->first();
                $bookingValid = false;
                $updatedCommission = 40;
            }

            if (is_null($subConsole)) {
                return response()->json(['message' => 'Delivery point not found.'], 404);
            }

            $booking->booking_delivery_point_id = $subConsole->id;
            $booking->valid = $bookingValid;
            $booking->save();

            $payment = $booking->payment;
            $payment->transaction_tax = number_format($totalCost*1/100, 2, '.', '');
            if ($booking->user->usert_type == 'customer') {
                $payment->transaction_comission_amount = 0;
                $payment->transaction_comission_by = 0;
            } else {
                $payment->transaction_comission_amount = $totalCost*$updatedCommission/100;
                $payment->transaction_comission_by = $updatedCommission;
            }
            $payment->save();

            $trxSubconsole = SubConsoleTransaction::where('booking_id', $booking->id)->first();
            if (!is_null($trxSubconsole)) {
                $previousSubconsole = $trxSubconsole;
                Log::info('subconsole canceled from controller '.$previousSubconsole->user_id);
                event(new SubConsoleOrderDeleted($previousSubconsole));
                if ($bookingValid) {
                    $trxSubconsole->delete();
                } else {
                    $trxSubconsole->user_id = $subConsole->id;
                    $trxSubconsole->save();

                    event(new SubConsoleOrder($trxSubconsole));
                }
            } else {
                if (!$bookingValid) {
                    $trxSubconsole = new SubConsoleTransaction();
                    $trxSubconsole->user_id = $subConsole->id;
                    $trxSubconsole->booking_id = $booking->id;
                    if ($booking->user->user_type == 'customer') {
                        $trxSubconsole->transaction_comission_amount = $totalCost*50/100;
                        $trxSubconsole->transaction_comission_by = 50;
                    } else {
                        $trxSubconsole->transaction_comission_amount = $totalCost*10/100;
                        $trxSubconsole->transaction_comission_by = 10;
                    }
                    $trxSubconsole->valid_status = 'order';
                    $trxSubconsole->save();

                    event(new SubConsoleOrder($trxSubconsole));
                }
            }

            return response()->json([
                'message' => 'Delivery point data updated',
                'status' => true,
                'data' => $subConsole
            ], 201);
        }

        if ($request->has('shipment')) {
            if ($booking->payment->paid == true) {
                return response()->json([
                    'message' => 'Pesananmu sudah dibayar. Tidak dapat melakukan perubahan data paket.'
                ], 200);
            }
            $shipments = $request->input('shipment');
            $previousShipment = 0;

            foreach ($booking->details as $item) {
                if ($item['package_volume'] > $item['package_weight']) {
                    $previousShipment += $item['package_volume'];
                } else {
                    $previousShipment += $item['package_weight'];
                }
            }

            $serviceFee = $booking->payment->transaction_amount / $previousShipment;
            $comission = 0;
            $totalCost = 0;
            $totalShipment = 0;
            if (!is_null($booking->deliveryPoint) && $booking->deliveryPoint->type == "droppoint") {
                $comission = 50;
            } else if ($booking->subConsole->user_type == "subconsole") {
                $comission = 40;
            }

            for ($i=0; $i < count($shipments); $i++) {
                $package = BookingDetail::where(['booking_id' => $booking->id, 'id' => $shipments[$i]['id']])->first();
                if (!is_null($package)) {
                    $package->package_description = $shipments[$i]['description'];
                    $package->package_length = $shipments[$i]['length'];
                    $package->package_width = $shipments[$i]['width'];
                    $package->package_height = $shipments[$i]['height'];
                    $package->package_weight = $shipments[$i]['weight'];
                    $package->package_volume = $shipments[$i]['volume'];
                    $package->package_commodity_id = $shipments[$i]['commodity_id'];
                    $package->package_quantity = $shipments[$i]['quantity'];
                    $package->save();

                    if ($shipments[$i]['weight'] < $shipments[$i]['volume']) {
                        $totalCost += $serviceFee * $shipments[$i]['quantity'] * $shipments[$i]['volume'];
                        $totalShipment += $shipments[$i]['volume'];
                    } else {
                        $totalCost += $serviceFee * $shipments[$i]['quantity'] * $shipments[$i]['weight'];
                        $totalShipment += $shipments[$i]['weight'];
                    }
                }
            }

            if ($totalShipment > 10) {
                $comission = 15;
            }

            $booking->payment->transaction_amount = $totalCost;
            $booking->payment->transaction_tax = number_format($totalCost*1/100, 2, '.', '');
            if ($booking->user->user_type == 'customer') {
                $booking->payment->transaction_comission_amount = 0;
                $booking->payment->transaction_comission_by = 0;
            } else {
                $booking->payment->transaction_comission_amount = $totalCost*$comission/100;
                $booking->payment->transaction_comission_by = $comission;
            }

            $booking->payment->save();

            if (!is_null($booking->subConsole)) {
                $subConsoleTransaction = SubConsoleTransaction::where([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->booking_delivery_point_id
                ])->first();

                if (!is_null($subConsoleTransaction)) {
                    $subConsoleTransaction->transaction_comission_amount = $totalCost*$subConsoleTransaction->transaction_comission_by/100;
                    $subConsoleTransaction->save();
                }
            }

            return response()->json(['message' => 'Shipment data updated'], 201);
        }
        return response()->json(['message' => 'what kind of you looking for?'], 200);
    }

    public function verificationBooking(Request $req)
    {
        if (auth()->user()->user_type != 'user') {
            $param = $req->only('shipment', 'booking_id', 'user_id');
            $booking = null;
            $subConsoleTransaction = null;

            if (auth()->user()->user_type == 'subconsole') {
                $isValid = SubConsoleTransaction::where([
                    'user_id' => auth()->user()->id,
                    'booking_id' => $param['booking_id'],
                ])->first();

                if (is_null($isValid)) {
                    return response()->json(['message' => 'booking not listed']);
                }

                $subConsoleTransaction = $isValid;
            }

            $booking = Booking::with(['details','payment','deliveryPoint','shipment','subConsole','user'])->where([
                'id' => $param['booking_id'],
                'user_id' => $param['user_id']
            ])->first();

            if (is_null($booking)) {
                return response()->json(['message' => 'booking not found'], 200);
            }

            $shipments = $param['shipment'];
            $previousShipment = 0;

            foreach ($booking->details as $item) {
                if ($item['package_volume'] > $item['package_weight']) {
                    $previousShipment += $item['package_volume'];
                } else {
                    $previousShipment += $item['package_weight'];
                }
            }

            $serviceFee = $booking->payment->transaction_amount / $previousShipment;
            $comission = 0;
            $totalCost = 0;
            if ($booking->deliveryPoint != null && $booking->deliveryPoint->type == "droppoint") {
                $comission = 50;
            } else if ($booking->subConsole != null) {
                $comission = 40;
            }

            $currentShipment = 0;
            for ($i=0; $i < count($shipments); $i++) {
                $package = BookingDetail::where(['booking_id' => $booking->id, 'id' => $shipments[$i]['id']])->first();
                if (!is_null($package)) {
                    $package->package_description = $shipments[$i]['description'];
                    $package->package_length = $shipments[$i]['length'];
                    $package->package_width = $shipments[$i]['width'];
                    $package->package_height = $shipments[$i]['height'];
                    $package->package_weight = $shipments[$i]['weight'];
                    $package->package_volume = $shipments[$i]['volume'];
                    $package->package_quantity = $shipments[$i]['quantity'];
                    $package->package_commodity_id = $shipments[$i]['commodity_id'];
                    $package->save();
                    if ($shipments[$i]['weight'] < $shipments[$i]['volume']) {
                        $currentShipment += $shipments[$i]['volume'];
                        $totalCost += $serviceFee * ($shipments[$i]['quantity'] * $shipments[$i]['volume']);
                    } else {
                        $currentShipment += $shipments[$i]['weight'];
                        $totalCost += $serviceFee * ($shipments[$i]['quantity'] * $shipments[$i]['weight']);
                    }
                }
            }

            if ($currentShipment > 10) {
                $comission = 15;
            }

            $booking->payment->transaction_amount = $totalCost;
            $booking->payment->transaction_tax = number_format($totalCost*1/100, 2, '.', '');
            if ($booking->user->user_type == 'customer') {
                $booking->payment->transaction_comission_amount = 0;
                $booking->payment->transaction_comission_by = 0;
            } else {
                $booking->payment->transaction_comission_amount = $totalCost*$comission/100;
                $booking->payment->transaction_comission_by = $comission;
            }
            $booking->payment->save();

            if ($subConsoleTransaction != null) {
                if ($booking->user->user_type == 'customer') {
                    if ($currentShipment > 10) {
                        $subConsoleTransaction->transaction_comission_amount = $totalCost*25/100;
                    } else {
                        $subConsoleTransaction->transaction_comission_amount = $totalCost*50/100;
                    }
                } else {
                    $subConsoleTransaction->transaction_comission_amount = $totalCost*10/100;
                }
                $subConsoleTransaction->save();
            }

            $booking->valid = true;
            $booking->save();

            return response()->json(['message' => 'Shipment data verified'], 201);
        }

        return response()->json(['message' => 'not allowed'], 401);
    }

    public function tracking(Request $request)
    {
        $ajcController = new AJCController();
        $trackConnote = $ajcController->trackConnote($request->input('connote'));

        return response()->json($trackConnote);
    }

    /* get cost for shipment, this will show in pre-booking page */
    public function getPrice(Request $request)
    {

        $parameter = $request->all();

        $destination = $parameter['destination']['city'];
        $dropPoint = $parameter['deliveryPoint'];


        for ($i=0; $i < sizeof($parameter['shipment']); $i++) {
            $item = $parameter['shipment'][$i];
            $detail = new BookingDetail();
            $totalWeight = 0;

            $item['volume'] = $this->getRound(($detail->package_length * $detail->package_width * $detail->package_height)/6000);
            $item['weight'] = $this->getRound($item['weight']);

            if ($item['weight'] < $item['volume'])
            {
                $totalWeight += $item['piece'] * $item['volume'];
            } else {
                $totalWeight += $item['piece'] * $item['weight'];
            }
        }
        $estWeight = $totalWeight;

        $deliveryPoint = DeliveryPoint::where('id', $dropPoint)->first();
        $subConsole = User::where('id', $dropPoint)->first();

        if (is_null($deliveryPoint) && is_null($subConsole)) {
            return response()->json(['message' => 'Delivery point not found']);
        }

        $type = "";
        $branchCode = "";
        if (!is_null($deliveryPoint)) {
            $branchCode = $deliveryPoint->branch_city_code;
            $type = "droppoint";
        } else {
            $cityOfSubconsole = $subConsole->kota;
            //20210305 - TID:pfj9NTWO - START - KIBAR
            //$branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE '%".$cityOfSubconsole."%'")->first();
            $branchCode = BranchOfficeMapping::whereRaw("TRIM(city) ILIKE :citysubcon",['citysubcon'=>'%'.$cityOfSubconsole.'%'])->first();
            //20210305 - TID:pfj9NTWO - END - KIBAR
            if ($branchCode != null) {
                $branchCode = $branchCode->airport_code;
                $type = "subconsole";
            }
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

                if ($type == 'droppoint') {
                    if ($estWeight != null && $estWeight > 10) {
                        $response['list'][0]['Commission'] = 25;
                    } else {
                        $response['list'][0]['Commission'] = 50;
                    }
                } else if ($type == 'subconsole') {
                    if ($estWeight != null && $estWeight > 10) {
                        $response['list'][0]['Commission'] = 15;
                    } else {
                        $response['list'][0]['Commission'] = 40;
                    }
                }

                return $response['list'];
            }

        }
    }

    /* for homepage check rate */
    public function getCheckRate(Request $request)
    {
        $destination = $request->input('destination');
        $origin = $request->input('origin');

        $param = ['form_params' => [
            'username' => 'kirimaja',
            'api_key' => 'kirimaja',
            'OriginCabang' => $origin,
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
                //rename params
                for($g=0;$g<count($response['list']);$g++)
                {
                    $response['list'][$g]['Dest'] = $response['list'][$g]['CityName'];
                    unset($response['list'][$g]['CityName']);
                    unset($response['list'][$g]['Service']);
                    unset($response['list'][$g]['Delivery']);
                    unset($response['list'][$g]['Volume']);
                    unset($response['list'][$g]['Customer']);
                }
                //rename params
                return response()->json($response['list']);
            }

        }
        return response($response, 200, ['content-type' => 'application/json']);
    }


    /* list of subconsole request */
    public function mySubconsole(Request $request)
    {
        $mySubconsoles = SubConsoleTransaction::with(['booking:id,user_id,booking_code', 'booking.user:id,fullname,phone,user_type', 'booking.details:id,booking_id', 'booking.shipment', 'booking.payment', 'deliveryPoint',])
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return response()->json($mySubconsoles, 200);
    }

    /* detail of shipment */
    public function mySubconsoleDetail(Request $request, $id)
    {
        $subConsoleItem = SubConsoleTransaction::with(['booking', 'booking.user:id,fullname,phone,user_type', 'booking.details', 'booking.payment', 'booking.shipment', 'deliveryPoint'])
            ->where('user_id', auth()->user()->id)
            ->where('id', $id)
            ->first();

        return response()->json($subConsoleItem, 200);
    }

    /* subconsole update for status and assign drop point */
    public function mySubconsoleUpdate(Request $request)
    {
        $param = $request->only('id','booking_id','status','dropPointId','notes');

        $update = SubConsoleTransaction::with(['booking:id,user_id,valid','deliveryPoint'])->where([
            'id' => $param['id'],
            'booking_id' => $param['booking_id']
        ])->first();

        if (is_null($update)) {
            return response()->json(['message' => 'item tidak ditemukan'], 406);
        }

        /* this for update status and assign drop point */
        if (array_key_exists('dropPointId', $param)) {
            /* if assign drop point, check if transaction has been received first */
            if ($update->valid_status == 'received') {
                /* if received, check if drop point exist */
                $dropPoint = DeliveryPoint::where('id', $param['dropPointId'])->first();
                if (is_null($dropPoint)) {
                    return response()->json([
                        'message' => 'Drop Point tidak ditemukan.'
                    ], 200);
                } else {
                    /* assign drop point */
                    $update->delivery_point_id = $param['dropPointId'];
                    $update->save();
                    return response()->json($update, 200);
                }
            } else {
                return response()->json([
                    'message' => 'Transaksi belum diterima oleh Sub-console.'
                ], 200);
            }
        } else {
            switch ($param['status']) {
                case 'accepted':
                    $update->booking->valid = true;
                    $update->booking->save();
                    $update->valid_status = $param['status'];
                    $update->save();
                    break;
                case 'rejected':
                    $update->booking->reject_booking = true;
                    $update->booking->save();
                    $update->valid_status = $param['status'];
                    $update->save();
                    break;
                case 'received':
                    $update->booking->valid_booking = true;
                    $update->booking->save();
                    $update->valid_status = $param['status'];
                    $update->save();
                    break;
                default:
                    # code...
                    break;
            }

            $status = new SubConsoleStatus();
            $status->trx_subconsole_id = $update->id;
            $status->booking_id = $update->booking_id;
            $status->status = $update->valid;
            $status->save();

            event(new SohibNotificationFromSubConsole($update));
            return response()->json($update, 200);
        }

    }

    /* AJC */
    public function getMasterAJCBranch()
    {
        $request = $this->client->get('https://service.goexpress.id/rss/json/cabang');

        $response = $request ? $request->getBody()->getContents() : null;
        $response = json_decode($response, true);
        $branchs = array();
        for ($i=0; $i < count($response); $i++) {
            if ($response[$i]['code'] == 'JKT') {
                $branch = [
                    'code' => 'JKT',
                    'name' => 'JABODETABEK',
                ];

                array_push($branchs, $branch);
                continue;
            }

            if ($response[$i]['code'] == 'ALL') {
                continue;
            } elseif ($response[$i]['code'] == 'BOO') {
                continue;
            } elseif ($response[$i]['code'] == 'JBE') {
                continue;
            }
            //20210301 - TID: aDL7eRNZ - START
//            } elseif ($response[$i]['code'] == 'TRK') {
//                continue;
//            }
            //20210301 - TID: aDL7eRNZ - END

            array_push($branchs, $response[$i]);
        }

        return response($branchs, 200, ['content-type' => 'application/json']);
    }

    public function getDestination(Request $request)
    {
        if(!empty($request->input('name')))
        {
            $destination = Destination::select('name')->where('name', 'LIKE', '%'.strtoupper($request->input('name')).'%')->get();
            return response()->json($destination, 200);
        }
        else
        {
            abort(401);
        }

    }

    public function getMasterAJCDestination()
    {
        $request = $this->client->get('https://service.goexpress.id/rss/json/destination');

        $response = $request ? $request->getBody()->getContents() : null;

        $masterDestination = json_decode($response, true);
        foreach ($masterDestination as $key => $value) {
            $destination = new Destination();
            $destination->name = $value['name'];
            $destination->code = $value['code'];
            $destination->save();
        }

        return response($response, 200, ['content-type' => 'application/json']);
    }

    public function bookingAjc(Request $request)
    {
        $id = $request->input('id');
        $awb = $request->input('awb');
        $booking = Booking::with(['details','payment'])->where('id', $id)->first();

        $param = ['form_params' => [
            'username' => 'igox',
            'api_key' => 'igox',
            'ORDERID' => $booking->booking_code,
            'SERVICE' => 'SLV',
            'PACKAGE' => '-',
            'MODA' => 'UDARA',
            'SHIPPER_NAME' => $booking->booking_origin_name,
            'SHIPPER_ADDR1' => $booking->booking_origin_addr_1,
            'SHIPPER_ADDR2' => $booking->booking_origin_addr_2,
            'SHIPPER_ADDR3' => $booking->booking_origin_addr_3,
            'SHIPPER_CITY' => $booking->booking_origin_city,
            'SHIPPER_ZIP' => $booking->booking_origin_zip,
            'SHIPPER_CONTACT' => $booking->booking_origin_contact,
            'SHIPPER_PHONE' => $booking->booking_origin_phone,
            'RECEIVER_NAME' => $booking->booking_destination_name,
            'RECEIVER_ADDR1' => $booking->booking_destination_addr_1,
            'RECEIVER_ADDR2' => $booking->booking_destination_addr_2,
            'RECEIVER_ADDR3' => $booking->booking_destination_addr_3,
            'RECEIVER_CITY' => $booking->booking_destination_city,
            'RECEIVER_ZIP' => $booking->booking_destination_zip,
            'RECEIVER_CONTACT' => $booking->booking_destination_contact,
            'RECEIVER_PHONE' => $booking->booking_destination_phone,
            'QTY' => 1,
            'WEIGHT' => 5,
            'GOODSDESC' => 'Paket',
            'INST' => '-',
            'INS_FLAG' => '-',
            'AWB' => $awb,
            'PICKUP' => date('Y-m-d H:m:s'),
        ]];

        $request = $this->client->post('https://service.goexpress.id/trace/insertawb', $param);

        // return response()->json($param);
        $response = $request ? $request->getBody()->getContents() : null;
        return response($response, 200, ['content-type' => 'application/json']);
    }

    public function getRound($oriVal) {
        $dec = ($oriVal - floor($oriVal));
        $num = floor($oriVal);
        $valNew = $oriVal;
        if ($dec !== 0) {
            $vale = floor($dec * 1000) / 1000;
            if ($vale > 0.30) {
                $valNew = ($num * 1) + 1;
            } else {
                if ($num == 0) {
                    $valNew = ($num * 1) + 1;
                } else {
                    $valNew = ($num * 1);
                }
            }
        }
        if ($valNew < 1) {
            $valNew = 1;
        }
        return $valNew;
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL()
        ]);
    }
}
