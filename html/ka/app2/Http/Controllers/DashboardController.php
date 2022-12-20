<?php

namespace App\Http\Controllers;

use Adldap\Adldap;
use Adldap\Auth\BindException;
use App\AJCBookingLog;
use App\Booking;
use App\BookingDetail;
use App\BookingExceed;
use App\Corporate;
use App\CorporateUser;
use App\DashboardUser;
use App\DashboardUserToken;
use App\ExcelModel\TrxBookingDetailImport;
use App\ExcelModel\TrxBookingImport;
use App\ExcelModel\TrxPaymentImport;
use App\MstAirportCoordinate;
use App\MstCityCoordinate;
use App\Partner;
use App\Payment;
use App\PaymentRequest;
use App\User;
use App\VourcherData;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    // public $user;
    public function __construct()
    {
        $this->middleware('APITokenJWTDashboard', ['except' => ['loginIntra', 'loginDashboard', 'importBookingData']]);
        $this->client = new Client();
    }

    public function loginIntra(Request $request, AuthenticationException $exception)
    {
        $credentials = $request->only('username', 'password');
        $ad = new Adldap();
        $config = [
            // Mandatory Configuration Options
            'hosts'            => [env('LDAP_HOSTS')],
            'base_dn'          => env('LDAP_BASE_DN'),
            'username'         => env('LDAP_ACCOUNT_PREFIX').'andri.ari',
            'password'         => 'G4rud41d@@!!',
        
            // Optional Configuration Options
            'account_prefix'   => env('LDAP_ACCOUNT_PREFIX'),
            'port'             => 389,
            'use_ssl'          => false,
            'use_tls'          => false,
            'version'          => 3,
            'timeout'          => 5,
        ];

        $ad->addProvider($config);

        try {
            $provider = $ad->connect();
            if ($provider->auth()->attempt($credentials['username'], $credentials['password'])) {
                $search = $provider->search();
                $record = $search->findBy('samaccountname', $credentials['username']);
                $tokenAuth = ['email' => 'andri.ari@garuda-indonesia.com', 'password' => 'makan99!'];

                $token = auth('api')->attempt($tokenAuth);
                return response()->json([
                    'message' => 'success', 
                    'user' => $record->givenname[0],
                    'token' => $token], 200);
            } else {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }            
        } catch (BindException $e) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }
    }

    public function loginDashboard(Request $request)
    {
        $parameter = $request->only('username', 'password');
        $parameter['username'] = $parameter['username']."@garuda-indonesia.com";

        $user = DashboardUser::where('email', $parameter['username'])->first();
        if (is_null($user)) {
            return response()->json(['message' => 'user invalid'], 401);
        }

        if (!Hash::check($parameter['password'], $user->password)) {
            return response()->json(['message' => 'user and password invalid'], 401);
        }

        $token = DashboardUserToken::where('id', $user->id)->first();
        if (!is_null($token)) {
            $token->token = Str::random(100);
            $token->save();
        } else {
            $token = new DashboardUserToken();
            $token->user_dashboard_id = $user->id;
            $token->token = Str::random(100);
            $token->save();
        }
        
        return response()->json(['message' => 'success', 'token' => $token->token, 'user' => $user->fullname], 202);
    }

    public function createDashboardUser(Request $request)
    {
        $parameter = $request->only('name','email', 'password');

        $user = DashboardUser::where('email', $parameter['email'])->first();
        if (!is_null($user)) {
            return response()->json(['message' => 'user exist'], 406);
        }

        $user = new DashboardUser();
        $user->fullname = $parameter['name'];
        $user->email = $parameter['email'];
        $user->password = Hash::make($parameter['password']);
        $user->save();

        return response()->json($user, 201);
    }
    
    public function getUser(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }
        $users = User::orderBy('id', 'desc')->get()->makeVisible('created_at');
        return response()->json($users);
    }

    public function userDetail(Request $request, $id)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        if ($request->isMethod('GET')) {
            $user = User::find($id);
            return response()->json($user, 200);
        } else if ($request->isMethod('POST')) {
            $user = User::find($id);
            $type = $request->input('type');
            
            if ($type == 'profile') {
                $user->fullname = $request->input('fullname');
                $user->phone = $request->input('phone');
                $user->address = $request->input('address');
                $user->city_code = $request->input('cityCode');
                $user->npwp = $request->input('npwp');
                //$user->user_type = $request->userType;
                $user->latitude = $request->input('latitude');
                $user->longitude = $request->input('longitude');
                $user->save();

                return response()->json(['message' => 'Profile updated'], 201);
            } else if ($type == 'password') {
                $newPassword = Hash::make($request->input('newPassword'));
                $user->password = $newPassword;
                $user->save();

                return response()->json(['message' => 'Password updated'], 201);
            } else if ($type == 'delete') {
                $user->delete();

                return response()->json(['message' => 'User deleted'], 201);
            }
        }
    }

    public function getBooking(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }
        $bookings = Booking::with(['user:id,fullname,email','details', 'payment', 'deliveryPoint', 'exceed'])->orderBy('id', 'desc')->get();
        return response()->json($bookings);
    }
    
    public function getBookingbyDate(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }        
        //sample
        //$request->from = '2020-06-25';
        //$request->to = '2020-06-26';

        $from = date($request->from);
        $to = date($request->to);
        $bookings = Booking::with(['user:id,fullname,email','details', 'payment', 'deliveryPoint', 'exceed'])->whereBetween('created_at',[$from, $to])->orderBy('id', 'desc')->get();
        return response()->json($bookings);
    }
   
   
    public function getBookingDetail(Request $request, $id)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        if (is_numeric($id)) {
            $bookings = Booking::with(['details', 'payment', 'deliveryPoint', 'exceed'])->where('id', $id)->first();
        } else {
            $bookings = Booking::with(['details', 'payment', 'deliveryPoint', 'exceed'])->whereRaw('UPPER(booking_code) = ?', [strtoupper($id)])->first();
        }
        
        return response()->json($bookings);
    }

    public function getExceedBooking(Request $request)
    {
        $bookings = BookingExceed::with(['booking'])->orderBy('id', 'desc')->get();
        return response()->json($bookings, 200);
    }

    public function editExceedBooking(Request $request)
    {
        $parameter = $request->only('id', 'approve');
        $booking = BookingExceed::with(['booking'])->where('id', $parameter['id'])->first();
        $booking->booking_approved = $parameter['approve'];
        $booking->save();

        return response()->json($booking, 200);
    }

    public function trackingBooking(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        $parameter = $request->only('connote_booking');
        $trackConnote = null;
        $booking = null;
        $ajcController = new AJCController();

        if (is_numeric($parameter['connote_booking'])) {    
            $trackConnote = $ajcController->trackConnote($parameter['connote_booking']);
            $booking = AJCBookingLog::where('awb', $parameter['connote_booking'])->first();
            $booking = Booking::with(['user:id,fullname,email,phone','shipment'])->where('id', $booking->booking_id)->first();
        } else {
            $booking = Booking::with(['user:id,fullname,email,phone','shipment'])->whereRaw('UPPER(booking_code) = ?', strtoupper($parameter['connote_booking']))->first();
            $trackConnote = $ajcController->trackConnote($booking->shipment->awb);
        }
        
        return response()->json(['trackingData' => $trackConnote, 'bookingInfo' => $booking], 200);

    }
    public function getPerformaceOverview(Request $request)
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            // $totalBooking = Booking::whereHas('payment', function ($query) use ($request) {
            //     $query->where('paid', '=', $request->input('status'));
            // })->whereBetween('created_at', [
            //     $request->input('start_date'),
            //     $request->input('end_date')
            // ]);

            if ($request->has('raw_data') && $request->input('raw_data') == 'totalBooking') {
                $totalBooking = Booking::with(['user:id,fullname,email','details','payment','deliveryPoint','exceed'])->whereHas('payment', function ($query) use ($request) {
                    $query->where('paid', '=', $request->input('status'));
                })->whereBetween('created_at', [
                    $request->input('start_date'),
                    $request->input('end_date')
                ]);

                return response()->json($totalBooking->get(), 200);
            } else {
                $totalBooking = Booking::whereHas('payment', function ($query) use ($request) {
                    $query->where('paid', '=', $request->input('status'));
                })->whereBetween('created_at', [
                    $request->input('start_date'),
                    $request->input('end_date')
                ]);
            }

            $totalTonage = BookingDetail::whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])->get()->sum(function ($t) {
                $total = 0;
                if ($t->package_weight > $t->package_volume) {
                    $total = $t->package_weight;
                } else {
                    $total = $t->package_volume;
                }
                return round($total);
            });

            $totalPackages = BookingDetail::whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])->count();

            $totalUsers = User::whereBetween('created_at', [
                $request->input('start_date'), 
                $request->input('end_date')
            ])->count();

            $totalActiveUser = User::whereHas('booking')->whereBetween('created_at', [
                $request->input('start_date'), 
                $request->input('end_date')
            ])->count();

            $totalRevenue = Booking::whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])->get()->sum(function ($t) {
                $total = $t->payment->transaction_amount;
                return $total;
            });

            $totalTax = Booking::whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])->get()->sum(function ($t) {
                $total = $t->payment->transaction_tax;
                return $total;
            });

            $totalCommission = Booking::whereHas('payment', function ($query) use ($request) {
                $query->where('paid', '=', $request->input('status'));
            })->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])->get()->sum(function ($t) {
                $total = $t->payment->transaction_comission_amount;
                return $total;
            });

            return response()->json([
                'bookings' => $totalBooking->count(),
                'tonages' => $totalTonage,
                'packages' => $totalPackages,
                'users' => $totalUsers,
                'active_users' => $totalActiveUser,
                'revenue' => $totalRevenue,
                'tax' => $totalTax,
                'commission' => $totalCommission
            ]);
        } else {
            $totalBooking = Booking::all()->count();
            $totalPackages = BookingDetail::all()->count();
            $totalTonage = BookingDetail::all()->sum(function($t) {
                $total = 0;
                if ($t->package_weight > $t->package_volume) {
                    $total = $t->package_weight;
                } else {
                    $total = $t->package_volume;
                }
                return round($total);
            });
            $totalUsers = User::all()->count();
    
            $totalTransactions = Booking::whereHas('payment', function ($query) {
                $query->where('paid', '=', true);
            })->count();
    
            $totalActiveUser = DB::select('SELECT COUNT(*) total_active_user 
            FROM trx_booking, mst_user 
            WHERE trx_booking.user_id = mst_user.id 
            GROUP BY trx_booking.user_id');
        }
        
        return response()->json([
            'bookings' => $totalBooking,
            'tonages' => $totalTonage,
            'packages' => $totalPackages,
            'users' => $totalUsers,
            'active_users' => count($totalActiveUser),
            'transactions' => $totalTransactions,
        ]);
    }

    public function getPaymentOverview(Request $request)
    {
        $totalIncome = Payment::sum('transaction_amount');
        $totalTax = Payment::sum('transaction_tax');
        $totalCommission = Payment::sum('transaction_comission_amount');

        $totalRevenue = Payment::where('paid', true)->sum('transaction_amount');

        return response()->json([
            'income' => $totalIncome,
            'tax' => $totalTax,
            'commission' => $totalCommission,
            //'revenue' => $totalRevenue,
        ]);
    }

    public function getIncomeOverview(Request $request)
    {
        $totalIncome = Payment::where('paid', true)->sum('transaction_amount');
        $totalTax = Payment::where('paid', true)->sum('transaction_tax');
        $totalCommission = Payment::where('paid', true)->sum('transaction_comission_amount');

        return response()->json([
            'income' => $totalIncome,
            'tax' => $totalTax,
            'commission' => $totalCommission,
            //'revenue' => $totalRevenue,
        ]);
    }

    public function userAcquisition(Request $request)
    {
        $offset = $request->input('offset');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $totalUserPerDay = null;

        if (isset($startDate)) {
            $totalUserPerDay = User::selectRaw("count(id) total_user, date_trunc('day', created_at) as register_date")
                            ->where('created_at', '>=', $startDate)
                            ->where('created_at', '<=', $endDate)
                            ->groupBy('register_date')
                            ->orderBy('register_date', 'DESC')
                            ->offset($offset)
                            ->limit(5)->get();
        } else {
            $totalUserPerDay = User::selectRaw("count(id) total_user, date_trunc('day', created_at) as register_date")
                            ->groupBy('register_date')
                            ->orderBy('register_date', 'DESC')
                            ->offset($offset)
                            ->limit(5)->get();
        }

        return response()->json(['avg_user' => $totalUserPerDay]);
    }

    public function userAcquisitionByUserType(Request $request)
    {
        $userType = User::selectRaw("count(id) as total_user, user_type")
                            ->groupBy('user_type')
                            ->orderBy('user_type', 'ASC')
                            ->get();

        $totalUnit = User::selectRaw("count(id) as total_employee, department")
                            ->groupBy('department')
                            ->orderBy('department', 'ASC')
                            ->get();

        return response()->json(['user_type' => $userType, 'total_unit' => $totalUnit]);
    }

    public function userBookingPerformance(Request $request)
    {
        $totalBookingByUser = DB::select('SELECT COUNT(trx_booking.id) total_booking, INITCAP(mst_user.fullname) AS fullname
        FROM mst_user, trx_booking 
        WHERE trx_booking.user_id = mst_user.id 
        GROUP BY mst_user.fullname 
        ORDER BY total_booking DESC
        LIMIT 10');

        return response()->json($totalBookingByUser);
    }

    public function userDepartmentPerformance(Request $request)
    {
        $totalDepartByUser = DB::select('SELECT COUNT(trx_booking.id) total_booking, mst_user.department 
        FROM mst_user, trx_booking 
        WHERE trx_booking.user_id = mst_user.id 
        GROUP BY mst_user.department 
        ORDER BY total_booking DESC
        LIMIT 10');

        return response()->json($totalDepartByUser);
    }

    public function activeUser(Request $request)
    {
        $totalActiveUser = DB::select('SELECT COUNT(*) total_active_user 
        FROM trx_booking, mst_user 
        WHERE trx_booking.user_id = mst_user.id 
        GROUP BY trx_booking.user_id');

        return response()->json(['active_users' => count($totalActiveUser)]);
    }

    public function getBookingTrend(Request $request)
    {
        $totalBookingPerDay = Booking::selectRaw("count(id) as total_booking, date_trunc('day', created_at) as transaction_date")
                            ->groupBy('transaction_date')
                            ->orderBy('transaction_date', 'DESC')
                            ->get();

        return response()->json(['avg_booking' => $totalBookingPerDay]);
    }

    public function getOrderByDeliveryPoint(Request $request)
    {
        
    }

    public function getTotalAgentInCities(Request $request)
    {
        $agentByCities = MstCityCoordinate::withCount(['agent'])->get();
        return response()->json($agentByCities);
    }

    public function getAgentByCities(Request $request, $id)
    {
        $agentByCities = MstCityCoordinate::with(['agent'])->where('id', $id)->get();
        return response()->json($agentByCities);
    }

    public function getAgentByAirport(Request $request)
    {
        $agentByCities = MstAirportCoordinate::withCount(['agent'])->get();
        return response()->json($agentByCities);
    }

    public function storeCityCoordinate(Request $request)
    {
        $parameter = $request->only('name', 'latitude', 'longitude');
        $cityCoordinate = new MstCityCoordinate();
        $cityCoordinate->city_name = $parameter['name'];
        $cityCoordinate->latitude = $parameter['latitude'];
        $cityCoordinate->longitude = $parameter['longitude'];
        $cityCoordinate->save();

        return response()->json($cityCoordinate);
    }

    public function importBookingData(Request $request)
    {
        $userId = $request->input('user_id');
        $bookings = Excel::toArray(new TrxBookingImport, $request->file('xls_file'));
        $oldBookingData = array();
        $oldBookingIds = array();
        $bookingDetails = array();
        $bookingPayment = array();

        $newBookingIds = array();
        
        if ($request->has('xls_detail_file')) {
            $bookingDetails = Excel::toArray(new TrxBookingDetailImport, $request->file('xls_detail_file'));
        }

        if ($request->has('xls_payment_file')) {
            $bookingPayment = Excel::toArray(new TrxPaymentImport, $request->file('xls_payment_file'));
        }

        foreach ($bookings[0] as $booking) {
            if ($booking['user_id'] == $userId) {
                $data = Booking::where('booking_code', $booking['booking_code'])->first();
                if (!is_null($data)) {
                    continue;
                }

                $storeBooking = new Booking();
                $storeBooking->user_id = $booking['user_id'];
                $storeBooking->booking_code = $booking['booking_code'];
                $storeBooking->booking_origin_name = $booking['booking_origin_name'];
                $storeBooking->booking_origin_addr_1 = $booking['booking_origin_addr_1'];
                $storeBooking->booking_origin_addr_2 = $booking['booking_origin_addr_2'];
                $storeBooking->booking_origin_addr_3 = $booking['booking_origin_addr_3'];
                $storeBooking->booking_origin_city = $booking['booking_origin_city'];
                $storeBooking->booking_origin_zip = $booking['booking_origin_zip'];
                $storeBooking->booking_origin_contact = $booking['booking_origin_contact'];
                $storeBooking->booking_origin_phone = $booking['booking_origin_phone'];
                $storeBooking->booking_destination_name = $booking['booking_destination_name'];
                $storeBooking->booking_destination_addr_1 = $booking['booking_destination_addr_1'];
                $storeBooking->booking_destination_addr_2 = $booking['booking_destination_addr_2'];
                $storeBooking->booking_destination_addr_3 = $booking['booking_destination_addr_3'];
                $storeBooking->booking_destination_city = $booking['booking_destination_city'];
                $storeBooking->booking_destination_zip = $booking['booking_destination_zip'];
                $storeBooking->booking_destination_contact = $booking['booking_destination_contact'];
                $storeBooking->booking_destination_phone = $booking['booking_destination_phone'];
                $storeBooking->booking_delivery_point_id = $booking['booking_delivery_point_id'];
                $storeBooking->valid = $booking['valid'];
                $storeBooking->save();

                array_push($oldBookingData, $booking);
                array_push($oldBookingIds, $booking['id']);
                $newBookingIds[$booking['id']] = $storeBooking->id;
            }
        }

        foreach ($bookingDetails[0] as $detail) {
            if (in_array($detail['booking_id'], $oldBookingIds)) {
                $storeBookingDetail = new BookingDetail();
                $storeBookingDetail->booking_id = $newBookingIds[$detail['booking_id']];
                $storeBookingDetail->package_description = $detail['package_description'];
                $storeBookingDetail->package_length = $detail['package_length'];
                $storeBookingDetail->package_width = $detail['package_width'];
                $storeBookingDetail->package_height = $detail['package_height'];
                $storeBookingDetail->package_weight = $detail['package_weight'];
                $storeBookingDetail->package_volume = $detail['package_volume'];
                $storeBookingDetail->package_quantity = $detail['package_quantity'];
                $storeBookingDetail->save();
            }
        }

        foreach ($bookingPayment[0] as $payment) {
            if (in_array($payment['booking_id'], $oldBookingIds)) {
                $storePayment = new Payment();
                $storePayment->user_id = $payment['user_id'];
                $storePayment->booking_id = $newBookingIds[$payment['booking_id']];
                $storePayment->transaction_amount = $payment['transaction_amount'];
                $storePayment->transaction_tax = $payment['transaction_tax'];
                $storePayment->transaction_comission_amount = $payment['transaction_comission_amount'];
                $storePayment->transaction_comission_by = $payment['transaction_comission_by'];
                $storePayment->transaction_id = $payment['transaction_id'];
                $storePayment->paid = $payment['paid'];
                $storePayment->paid_at = new DateTime();
                $storePayment->paid_channel = $payment['paid_channel'];
                $storePayment->paid_response = $payment['paid_response'];
                $storePayment->payment_proof = $payment['payment_proof'];
                $storePayment->save();
            }
        }

        return response()->json($newBookingIds, 200);
    }

    // Manage Payment
    public function getPaymentRequest(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        $param = $request->only('bookingId','check');
        $payRequest = PaymentRequest::with('payment')->where('booking_id', $param['bookingId'])->get();
        $response = array();

        if (!is_null($payRequest) && $request->has('check')) {
            foreach ($payRequest as $item) {
                $checkResponse = $this->getPaymentStatus($item->transid, $item->payment->paid_channel);
                if ($checkResponse != "") {
                    if ($checkResponse['RESPONSECODE'] != '5516') {
                        array_push($response, $checkResponse);
                    }
                }
                
            }
        }

        return response()->json($response, 200);
    }

    private function getPaymentStatus($transid, $channel)
    {
        $words = sha1('7982'.'sIp41FgKqMtc'.$transid);

        $body = ['form_params' => [
            'MALLID' => '7982',
            'CHAINMERCHANT' => 'NA',
            'SESSIONID' => $transid,
            'TRANSIDMERCHANT' => $transid,
            'WORDS' => $words,
            'PAYMENTCHANNEL' => $channel
        ]];

        $request = $this->client->post('https://gts.doku.com/Suite/CheckStatus', $body);

        $response = $request ? $request->getBody()->getContents() : null;
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        $json = json_decode($json, true);
        return $json;
    }

    public function checkPaymentStatus(Request $request)
    {
        if (auth()->user()->user_type != 'admin') {
            return response()->json([], 401);
        }

        $param = $request->only('TRANSID', 'CHANNEL');

        $words = sha1('7982'.'sIp41FgKqMtc'.$param['TRANSID']);

        $body = ['form_params' => [
            'MALLID' => '7982',
            'CHAINMERCHANT' => 'NA',
            'SESSIONID' => $param['TRANSID'],
            'TRANSIDMERCHANT' => $param['TRANSID'],
            'WORDS' => $words,
            'PAYMENTCHANNEL' => $param['CHANNEL']
        ]];

        $request = $this->client->post('https://gts.doku.com/Suite/CheckStatus', $body);

        $response = $request ? $request->getBody()->getContents() : null;
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        return Response::make($json, 200, ['Content-Type' => 'application/json']);
    }

    // Manage Corporate
    public function newCorporate(Request $request)
    {
        $param = $request->only('name','prefix','address','subdistrict','city','province','country','tax','email','phone','ext','commission','payment');

        $corporate = new Corporate();
        $corporate->name = $param['name'];
        $corporate->prefix = $param['prefix'];
        $corporate->address = $param['address'];
        $corporate->subdistrict = $param['subdistrict'];
        $corporate->city = $param['city'];
        $corporate->province = $param['province'];
        $corporate->country = $param['country'];
        $corporate->tax_number = $param['tax'];
        $corporate->email = $param['email'];
        $corporate->phone = $param['phone'];
        $corporate->phone_extension = $param['ext'];
        $corporate->commission = $param['commission'];
        $corporate->payment_type = $param['payment'];
        $corporate->save();

        return response()->json($corporate, 200);
    }

    public function newCorporateLogin(Request $request)
    {
        $param = $request->only('corpId', 'username', 'password');

        $corporate = Corporate::find($param['corpId']);
        if (is_null($corporate)) {
            return response()->json(['message' => 'corporate not found'], 200);
        }

        $user = CorporateUser::where([
            'corporate_id' => $param['corpId'],
            'username' => strtolower($corporate->prefix.$param['username'])
        ])->first();

        if (!is_null($user)) {
            return response()->json(['message' => 'username already registered'], 406);
        }

        $user = new CorporateUser();
        $user->corporate_id = $param['corpId'];
        $user->username = strtolower($corporate->prefix.$param['username']);
        $user->password = Hash::make($param['password']);
        $user->save();

        return response()->json(['message' => 'registered', 'username' => $user->username], 200);
    }

    public function listCorporation(Request $request)
    {
        $corporates = Corporate::orderBy('id','desc')->paginate(10);
        return response()->json($corporates, 200);
    }

    // Manage Partner & Voucher

    public function newPartner(Request $request)
    {
        $param = $request->all();
        
        $partner = new Partner();
        $partner->partner_name = $param['name'];
        $partner->partner_address = $param['address'];
        $partner->partner_email = $param['email'];
        $partner->partner_phone = $param['phone'];
        $partner->partner_pic = $param['pic'];
        $partner->save();

        return response()->json($partner, 200);
    }

    public function getVouchers(Request $request)
    {
        // $vouchers = VourcherData::select('voucher_name')->distinct()->pluck('voucher_name');
        // $vouchers = VourcherData::distinct()->select('voucher_name')->count();
        // $vouchers = DB::table('mst_voucher')->count(DB::raw('DISTINCT voucher_name'));
        // dd($vouchers);
        // return response()->json($vouchers, 200);
    }

    public function newVoucher(Request $request)
    {
        $param = $request->all();
        
        $partner = Partner::where('id', $param['partnerId'])->first();
        if (is_null($partner)) {
            return response()->json([
                'message' => 'partner not found'
            ], 404);
        }

        $totalVoucher = $param['quantity'];
        for ($i=0; $i < $totalVoucher; $i++) { 
            $voucher = new VourcherData();
            $voucher->partner_id = $partner->id;
            $voucher->voucher_name = $param['name'];
            $voucher->voucher_code = strtoupper(Str::random(6));
            $voucher->voucher_value = $param['value'];
            $voucher->save();
        }

        return response()->json([
            'message' => 'voucher created',
            'detail' => [
                'name' => $param['name']
            ]
        ], 200);
    }
}
