<?php

namespace App\Http\Controllers;

use Adldap\Adldap;
use App\AJCBookingLog;
use App\Announcement;
use App\Booking;
use App\Events\OrderReceiptEvent;
use App\FirebaseToken;
use App\Mail\UserConfirmationMail;
use App\Mail\UserFogetPINMail;
use App\Mail\UserForgetPassword as MailUserForgetPassword;
use App\MasterLabel;
use App\PaymentRequest;
use App\Service\DigiAsiaService;
use App\User;
use App\UserForgetPassword;
use App\UserIdentityCard;
use App\UserSavedAddress;
use App\WalletAccount;
use DateTime;
use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use JWTAuth;
use App\Log; //20210308 - TID:pfj9NTWO - START - KIBAR

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT', ['except' => [
            'login', 'register', 'refresh', 'termsConditions', 'subConsoleTerms', 'faqPage', 'testEmail', 'testOrderBooking',
            'userVerification', 'forgetPassword', 'doForgetPassword', 'welcomeMessage','uploadprofilesubconsole'
            ]
        ]);
    }

    /* welcome message in the app for guest */
    public function welcomeMessage()
    {
        return response('Hai, segera daftar jadi Sohib dan dapatkan komisi hingga 50%', 200)->header('Content-Type', 'text/plain');
    }

    /* terms and conditions in register */
    public function termsConditions(Request $request)
    {
        return view('terms-conditions');
    }

    /* additional terms and conditions for subconsole register */
    public function subConsoleTerms(Request $request)
    {
        $terms = MasterLabel::where('module', 'subconsole-terms')->orderBy('id', 'asc')->get();
        return response()->json($terms);
    }

    /* faq page */
    public function faqPage(Request $request)
    {
        return view('faq');
    }

    public function testEmail()
    {
        return view('emails.user_confirmation', [
            'nama' => 'Andri',
            'verify_link' => secure_url('user-verification', ['asdasdasdasd']),
            'ka_icon' => url(Storage::url('appicon.png'))
        ]);
    }

    public function testOrderBooking(Request $request)
    {
        $booking = Booking::with(['details', 'payment', 'shipment'])->find($request->input('id'));

        return view('emails.order_booking', [
            'booking' => $booking
        ]);
    }

    /* register */
    public function register(Request $request)
    {
        $parameter = $request->only('fullname', 'email', 'password', 'phone', 'address', 'npwp', 'district', 'userType', 'identityCard', 'identityCardSelfie');

        if ($this->checkUser($parameter['email']) != null) {
            return response()->json(['message' => 'Mohon maaf, pengguna sudah terdaftar.'], 406);
        }
        
        $user = new User();
        $user->fullname = $parameter['fullname'];
        $user->email = strtolower($parameter['email']);
        $user->password = Hash::make($parameter['password']);
        $user->phone = $parameter['phone'];
        /* made this to maintain previous app. */
        if (array_key_exists('userType', $parameter)) {
            $user->user_type = $parameter['userType'];
            if ($parameter['userType'] == 'customer') {
                $user->npwp = '00';
                $user->address = 'ADDR';
            } else {
                $user->npwp = ($parameter['npwp'] != '' || $parameter['npwp'] != null) ? $parameter['npwp'] : '-';
                $user->address = ($parameter['address'] != '' || $parameter['address'] != null) ? $parameter['address'] : '-';
            }
        } else {
            /* register as sohib */
            $user->user_type = 'user';
            $user->npwp = ($parameter['npwp'] != '' || $parameter['npwp'] != null) ? $parameter['npwp'] : '-';
            $user->address = $parameter['address'];
        }
        
        if (array_key_exists('district', $parameter)) {
            $addressLayer = $parameter['district'];
            $addressLayers = explode(',', $addressLayer);
            $user->kecamatan = $addressLayers[0];
            $user->kota = $addressLayers[1];
            $user->provinsi = $addressLayers[2];
            $user->city_code = trim($addressLayers[1]);
        } else {
            $user->city_code = 'JKT';
        }

        $user->verified = false;
        $user->verification_token = Str::random(100);
        $user->save();

        if (array_key_exists('identityCard', $parameter)) {
            $photo = $request->file('identityCard');
            $userIdentity = UserIdentityCard::where(
                'user_id', '=', $user->id
            )->first();

            $filename = preg_replace('/\s+/', '',$user->fullname).'_identity_'.time().'.'.$photo->getClientOriginalExtension();
            $storeFile = Storage::putFileAs(
                'user_identity_assets', $photo, $filename
            );

            if ($storeFile) {
                if (is_null($userIdentity)) {
                    $userIdentity = new UserIdentityCard();
                    $userIdentity->user_id = $user->id;
                    $userIdentity->identity_card = $filename;
                    $userIdentity->save();
                } else {
                    $userIdentity->identity_card = $filename;
                    $userIdentity->save();
                }
            }
        }

        if (array_key_exists('identityCardSelfie', $parameter)) {
            $photo = $request->file('identityCardSelfie');
            $userIdentity = UserIdentityCard::where(
                'user_id', '=', $user->id
            )->first();

            $filename = preg_replace('/\s+/', '',$user->fullname).'_identity_selfie_'.time().'.'.$photo->getClientOriginalExtension();
            $storeFile = Storage::putFileAs(
                'user_identity_assets', $photo, $filename
            );

            if ($storeFile) {
                if (is_null($userIdentity)) {
                    $userIdentity = new UserIdentityCard();
                    $userIdentity->user_id = $user->id;
                    $userIdentity->identity_card_selfie = $filename;
                    $userIdentity->save();
                } else {
                    $userIdentity->identity_card_selfie = $filename;
                    $userIdentity->save();
                }
            }
        }

        $response = [
            'message' => 'Kamu berhasil mendaftar sebagai Sohib KirimAja! Silahkan verifikasi email anda terlebih dahulu. Anda dapat memeriksa email dari kami pada bagian Kotak Masuk atau Spam.',
            'user' => $user
        ];

        if (env('APP_ENV') == 'production') {
            Mail::to($user->email)->send(new UserConfirmationMail($user, route('user-verification', [$user->verification_token])));
        } 
        return response()->json($response);
    }

    /* user verification */
    public function userVerification(Request $request, $token)
    {
        $user = User::where('verification_token', $token)->first();
        if (is_null($user)) {
            return 'Not valid';
        } else {
            $user->verified = true;
            $user->verification_token = null;
            $user->save();

            return 'User verified';
        }
    }

    /* login */
    public function login(Request $request)
    {
        $parameter = $request->only('email', 'password');
        
        if (!$token = auth('api')->attempt($parameter)) {

            //20210308 - TID:pfj9NTWO - START - KIBAR
            $_param['activity']="login_fail";
            $_param['username']=$parameter['email'];
            $_param['param']='-';
            $this->logHistory($_param);

            $counter = \DB::select("select count(1) as total from log_activity where username=:username and activity='login_fail'  and type='frontend' and created_at >=:today and created_at < :nextday",['username'=>$parameter['email'],'today'=>date('Y-m-d'),'nextday'=>date('Y-m-d', strtotime(' +1 day'))]);

            if($counter > 5)
            {
                $user = User::where('email', $parameter['email'])->first();
                if(!empty($user))
                {
                    $user->suspend_status = 1;
                    //$user->save();
                }
            }
            //20210308 - TID:pfj9NTWO - END - KIBAR

            return response()->json(['message' => 'Email atau Password tidak sesuai.'], 401);
        }

        //20210308 - TID:pfj9NTWO - START - KIBAR
        if($token) {
            $user = User::where('email', $parameter['email'])->first();

            $_param['activity']="login_success";
            $_param['username']=$user->email;
            $_param['userid']=$user->id;
            $_param['param']='-';
            $this->logHistory($_param);

            if($user->suspend_status)
            {
                $_param['activity']="login_suspend";
                $_param['username']=$user->email;
                $_param['userid']=$user->id;
                $_param['param']='-';
                $this->logHistory($_param);

                return response()->json(['message' => 'Kami menangguhkan akun anda selama 1 jam karena berulang kali gagal login, silahkan coba login ulang nanti atau hubungi KirimAja Care (WA chat 0811-9766-554)'], 401);
            }
        }
        //20210308 - TID:pfj9NTWO - END - KIBAR

        // if(!auth()->user()->verified) {
        //     return response()->json(['message' => 'Please verify your account first.'], 401);
        // }
        return $this->respondWithToken($token);
    }

    /* request forget password */
    public function forgetPassword(Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = User::where('email', $request->input('email'))->first();
            if (is_null($user)) {
                //REMARK - BAYU
                return response()->json(['message' => 'Silakan cek email Anda untuk tahapan lebih lanjut, Cek folder spam Anda jika Anda belum menerima email']);
                //REMARK - BAYU
            }

            $forgetPassword = UserForgetPassword::where('user_id', $user->id)->first();
            if (!is_null($forgetPassword)) {
                if ($forgetPassword->valid) {
                    //REMARK - BAYU
                    return response()->json(['message' => 'Silakan cek email Anda untuk tahapan lebih lanjut, Cek folder spam Anda jika Anda belum menerima email']);
                    //REMARK - BAYU
                } else {
                    $forgetPassword->token = Str::random(100);
                    $forgetPassword->valid = true;
                    $forgetPassword->save();
                }
            } else {
                $forgetPassword = new UserForgetPassword();
                $forgetPassword->user_id = $user->id;
                $forgetPassword->token = Str::random(100);
                $forgetPassword->save();
            }

            $forgetPasswordLink = URL::secure(URL::route('webForgetPassword', ['challenge' => $forgetPassword->token], false));
            Mail::to($user->email)->send(new MailUserForgetPassword($user, $forgetPasswordLink));
            //REMARK - BAYU
            return response()->json(['message' => 'Silakan cek email Anda untuk tahapan lebih lanjut, Cek folder spam Anda jika Anda belum menerima email']);
            //REMARK - BAYU
        }
    }

    /* do forget password from web page */
    public function doForgetPassword(Request $request)
    {
        if ($request->isMethod('POST')) {
            $forgetPassword = UserForgetPassword::where([
                'token' => $request->input('challenge'),
                'valid' => true,
            ])->first();

            $user = User::find($forgetPassword->user_id);
            $user->password = Hash::make($request->input('repeatNewPassword'));
            $user->save();

            $forgetPassword->token = '';
            $forgetPassword->valid = false;
            $forgetPassword->save();

            return view('user.forget-password', [
                'passwordValidated' => true
            ]);
        } else {
            $token = $request->input('challenge');
            $forgetPassword = UserForgetPassword::where([
                'token' => $token,
                'valid' => true,
            ])->first();
            
            if (is_null($forgetPassword)) {
                return 'not found';
            }

            return view('user.forget-password');
        }
    }

    /* profile */
    public function me(Request $request)
    {
        if ($request->isMethod('GET')) {
            $user = User::with(['branchOffice', 'identityCard', 'wallet'])->where('id', auth()->user()->id)->first();

            if (!is_null($user->identityCard)) {
                if ($user->identityCard->identity_card != null || $user->identityCard->identity_card != '') {
                    $user['card'] = route('identityPhoto', ['type' => 'card']);
                    $user['cardlink'] = $user->identityCard->identity_card; // add link image t10 20210411
                }
                if ($user->identityCard->identity_card_selfie != null || $user->identityCard->identity_card_selfie != '') {
                    $user['selfie'] = route('identityPhoto', ['type' => 'selfie']);
                    $user['selfielink'] = $user->identityCard->identity_card_selfie; // add link image t10 20210411
                }
                if ($user->identityCard->profile_image != null || $user->identityCard->profile_image != '') {
                    $user['profileImage'] = route('identityPhoto', ['type' => 'profile']);
                    $user['profileImagelink'] = $user->identityCard->profile_image;// add link image t10 20210411
                }
            }

            unset($user['identityCard']);
            if (auth()->user()->user_type == 'customer') {
                $user['greetings'] = 'Hai, segera daftar jadi Sohib dan dapatkan komisi hingga 50%';
            } else {
                $user['greetings'] = 'Hai Sohib, '.$user->fullname.'! Ayo mulai bertransaksi!';
            }

            if (!is_null($user->wallet)) {
                if ($user->wallet->pin != null) {
                    $user->wallet->pin = true;
                } else {
                    $user->wallet->pin = false;
                }
            }

            return response()->json($user);
        } else {

            $parameter = $request->only('fullname', 'phone', 'address', 'cityCode', 'npwp', 'latitude', 'longitude', 'userType', 'district', 'openStatus');
            $user = User::find(auth()->user()->id);

            if (array_key_exists('openStatus',$parameter)) {
                $user->open_for_drop = $parameter['openStatus'];
                $user->save();
                return response()->json($user);
            }


            $addressLayer = null;
            $addressLayers = null;
            if (array_key_exists('district', $parameter)) {
                $addressLayer = $parameter['district'];
                $addressLayers = explode(',', $addressLayer);
            }

            $fullname=$parameter['fullname'];

            $user->fullname = $parameter['fullname'];
            $user->phone = $parameter['phone'];
            $user->address = $parameter['address'];
            $user->npwp = $parameter['npwp'];
            //$user->user_type = $parameter['userType']; //20210304 - TID: Fsq6U7Ps - START
            //if (array_key_exists('district', $parameter) && $user->user_type != 'customer') { //20210304 - TID: Fsq6U7Ps - START
            if (array_key_exists('district', $parameter) && $parameter['userType'] != 'customer') { //20210304 - TID: Fsq6U7Ps - START
                $user->kecamatan = $addressLayers[0];
                $user->kota = $addressLayers[1];
                $user->provinsi = $addressLayers[2];
                $user->city_code = trim($addressLayers[1]);
            }
            $user->latitude = $parameter['latitude'];
            $user->longitude = $parameter['longitude'];
            $user->save();

            /***
             * T10 Add update upload profil 20210411
             *
             */
            $profilfile=$request->file("profilphoto");

            if( isset($profilfile) && $request->get("profilphoto") !="nofile"){

                $photo = $request->file('profilphoto');

                $userIdentity = UserIdentityCard::where(
                    'user_id', '=', auth()->user()->id
                )->first();

                $filename = preg_replace('/\s+/', '',$fullname).'_profile_'.time().'.'.$photo->getClientOriginalExtension();

                $storeFile = Storage::putFileAs(
                    'public/user_identity_assets', $photo, $filename
                );

                if ($storeFile) {
                    if (is_null($userIdentity)) {
                        $userIdentity = new UserIdentityCard();
                        $userIdentity->user_id = auth()->user()->id;
                        $userIdentity->profile_image = $filename;
                        $userIdentity->save();
                    } else {
                        $userIdentity->profile_image = $filename;
                        $userIdentity->save();
                    }

                }
            }

            return response()->json($user);
        }

    }

    public function userProfileImage(Request $request)
    {
        if ($request->header('KA-APP-TOKEN') == 'FFF2AC89D1813AF69C64D56F8EEDC') {
            $userId = $request->input('id');
            $identity = UserIdentityCard::where('id', $userId)->first();
            if (!is_null($identity)) {
                if ($identity->profile_image != null || $identity->profile_image != '') {

                    $profileImage = storage_path('app/public/user_identity_assets/'.$identity->profile_image); //20210315 - TID: nNuQqFUJ - START
    
                    $type = File::mimeType($profileImage);
                    $realFile = File::get($profileImage);
    
                    $profileImage = Response::stream(function() use($realFile) {
                        echo $realFile;
                    }, 200, ["Content-Type"=> $type]);

                    return $profileImage;
                }
            }

            return response()->json(['message' => 'Ok'], 200);
        }
    }

    /******************************************
     * T10 20210409
     * Upload swaphoto & KTP untuk daftar subconsole di web retail
     */
    public function uploadprofilesubconsole(Request $request){
        $user = User::find($request->input('id') );
        $user->address = $request->input('address');
        $user->save();

        $userIdentity = UserIdentityCard::where(
            'user_id', '=',$request->input('id')
        )->first();

        $Ktp=$request->file('ktp');
        $swa=$request->file('swafoto');
        $filename = preg_replace('/\s+/', '',$request->input('id') ).'_profilsubconsole_'.time().'.'.$Ktp->getClientOriginalExtension();
        $KTPStore = Storage::putFileAs(
            'public/user_identity_assets', $Ktp, $filename
        );

        if ($KTPStore) {
            if (is_null($userIdentity)) {
                $userIdentity = new UserIdentityCard();
                $userIdentity->user_id = $request->input('id');
                $userIdentity->identity_card = $filename;
                $userIdentity->save();
            } else {
                $userIdentity->identity_card = $filename;
                $userIdentity->save();
            }

        }

        $filename = preg_replace('/\s+/', '',$request->input('id') ).'_profilsubconsole_'.time().'.'.$swa->getClientOriginalExtension();
        $SWaStore = Storage::putFileAs(
            'public/user_identity_assets', $swa, $filename
        );

        if ($SWaStore) {
            if (is_null($userIdentity)) {
                $userIdentity = new UserIdentityCard();
                $userIdentity->user_id = $request->input('id');
                $userIdentity->identity_card_selfie = $filename;
                $userIdentity->save();
            } else {
                $userIdentity->identity_card_selfie = $filename;
                $userIdentity->save();
            }

        }


        $user = User::find($request->input('id'));
        $user->approved = 2;
        $user->save();

    }
    /* retrieve & upload identity */
    public function identityPhoto(Request $request, $type = '')
    {
        if ($request->isMethod('POST')) {
            $photo = $request->file('file');
            $type = $request->input('type');

            $user = auth()->user();
            
            if ($type == 'identity') {
                $userIdentity = UserIdentityCard::where(
                    'user_id', '=', $user->id
                )->first();

                $filename = preg_replace('/\s+/', '',$user->fullname).'_identity_'.time().'.'.$photo->getClientOriginalExtension();
                $storeFile = Storage::putFileAs(
                    'public/user_identity_assets', $photo, $filename
                );

                if ($storeFile) {

                    if (is_null($userIdentity)) {
                        $userIdentity = new UserIdentityCard();
                        $userIdentity->user_id = $user->id;
                        $userIdentity->identity_card = $filename;
                        $userIdentity->save();
                    } else {
                        $userIdentity->identity_card = $filename;
                        $userIdentity->save();
                    }

                    return response()->json([
                        'message' => 'Data berhasil disimpan'
                    ], 200);
                }
            } else if ($type == 'identity_selfie') {
                $userIdentity = UserIdentityCard::where(
                    'user_id', '=', $user->id
                )->first();

                $filename = preg_replace('/\s+/', '',$user->fullname).'_identity_selfie_'.time().'.'.$photo->getClientOriginalExtension();
                $storeFile = Storage::putFileAs(
                    'public/user_identity_assets', $photo, $filename
                );

                if ($storeFile) {
                    if (is_null($userIdentity)) {
                        $userIdentity = new UserIdentityCard();
                        $userIdentity->user_id = $user->id;
                        $userIdentity->identity_card_selfie = $filename;
                        $userIdentity->save();
                    } else {
                        $userIdentity->identity_card_selfie = $filename;
                        $userIdentity->save();
                    }

                    return response()->json([
                        'message' => 'Data berhasil disimpan'
                    ], 200);
                }
            } else if ($type == 'profile') {
                $userIdentity = UserIdentityCard::where(
                    'user_id', '=', $user->id
                )->first();

                $filename = preg_replace('/\s+/', '',$user->fullname).'_profile_'.time().'.'.$photo->getClientOriginalExtension();
                $storeFile = Storage::putFileAs(
                    'public/user_identity_assets', $photo, $filename
                );

                if ($storeFile) {
                    if (is_null($userIdentity)) {
                        $userIdentity = new UserIdentityCard();
                        $userIdentity->user_id = $user->id;
                        $userIdentity->profile_image = $filename;
                        $userIdentity->save();
                    } else {
                        $userIdentity->profile_image = $filename;
                        $userIdentity->save();
                    }

                    return response()->json([
                        'message' => 'Data berhasil disimpan'
                    ], 200);
                }
            }
        } else {
            $userIdentity = UserIdentityCard::where(
                'user_id', '=', auth()->user()->id
            )->first();

            if ($type == 'card') {
                if ($userIdentity->identity_card != null || $userIdentity->identity_card != '') {
                    $identityCard = storage_path('app/public/user_identity_assets/'.$userIdentity->identity_card); //20210315 - TID: nNuQqFUJ - START
                    
                    $type = File::mimeType($identityCard);
                    $realFile = File::get($identityCard);
    
                    $identityCard = Response::stream(function() use($realFile) {
                        echo $realFile;
                    }, 200, ["Content-Type"=> $type]);
    
                    return $identityCard;
                }
            }
            
            if ($type == 'selfie') {
                if ($userIdentity->identity_card_selfie != null || $userIdentity->identity_card_selfie != '') {
                    $identityCardSelfie = storage_path('app/public/user_identity_assets/'.$userIdentity->identity_card_selfie); //20210315 - TID: nNuQqFUJ - START
    
                    $type = File::mimeType($identityCardSelfie);
                    $realFile = File::get($identityCardSelfie);
    
                    $identityCardSelfie = Response::stream(function() use($realFile) {
                        echo $realFile;
                    }, 200, ["Content-Type"=> $type]);

                    return $identityCardSelfie;
                }
            }

            if ($type == 'profile') {
                if ($userIdentity->profile_image != null || $userIdentity->profile_image != '') {
                    $profileImage = storage_path('app/public/user_identity_assets/'.$userIdentity->profile_image); //20210315 - TID: nNuQqFUJ - START
    
                    $type = File::mimeType($profileImage);
                    $realFile = File::get($profileImage);
    
                    $profileImage = Response::stream(function() use($realFile) {
                        echo $realFile;
                    }, 200, ["Content-Type"=> $type]);

                    return $profileImage;
                }
            }
        }
        
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL()
        ]);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    private function checkUser($email)
    {
        $user = User::where('email', $email)->first();
        return $user;
    }

    /* user announcement for the app */
    public function userAnnouncement(Request $request)
    {
        $announcement = Announcement::orderBy('id','desc')->first();
        return response()->json($announcement);
    }

    /* store user token firebase cloud messaging */
    public function firebaseToken(Request $req)
    {
        $param = $req->only('firebase_token', 'platform');

        //20210308 - TID : qyMBSbR5 - START
        $fcmToken = FirebaseToken::where('token', $param['firebase_token'])->orderBy('updated_at','desc')->get();
        // return response()->json($fcmToken, 201);exit;
        // return response()->json($fcmToken, 201);exit;
        if (sizeof($fcmToken) == 0) {
            // echo "masuk";
            $fcmToken = new FirebaseToken();
            $fcmToken->user_id = auth()->user()->id;
            $fcmToken->token = $param['firebase_token'];
            $fcmToken->platform = $param['platform'];
            $fcmToken->save();
        } else {
            // echo "engga";
            $count = 0;
            foreach($fcmToken as $fcm){
                if($count == 0){
                    $res = FirebaseToken::where('id', $fcm->id)->first();
                    $res->user_id = auth()->user()->id;
                    $res->save();
                }else{
                    $res = FirebaseToken::where('id', $fcm->id)->first();
                    $res->delete();
                }
                $count++;
            }
            // $fcmToken->user_id = auth()->user()->id;
            // $fcmToken->save();
        }
        //20210308 - TID : qyMBSbR5 - END


//        $fcmToken = FirebaseToken::where([
//            'user_id' => auth()->user()->id,
//            'platform' => $param['platform']
//        ])->first();
//
//        if (is_null($fcmToken)) {
//            $fcmToken = new FirebaseToken();
//            $fcmToken->user_id = auth()->user()->id;
//            $fcmToken->token = $param['firebase_token'];
//            $fcmToken->platform = $param['platform'];
//            $fcmToken->save();
//        } else {
//            $fcmToken->token = $param['firebase_token'];
//            $fcmToken->save();
//        }

        return response()->json(['message' => 'updated'], 201);
    }

    /* user address */
    public function userAddress(Request $req)
    {
        if ($req->isMethod('POST')) {
            $param = $req->only('alias','recepient','address','subdistrict','phone','zip','search');
            
            if ($req->has('search')) {
                //20210305 - TID:pfj9NTWO - START - KIBAR
                //$addresses = UserSavedAddress::whereRaw('alias','ilike', '%'.$param['search'].'%')->get();
                $addresses = UserSavedAddress::whereRaw('alias','ilike :search', ['search'=>'%'.$param['search'].'%'])->get();
                //20210305 - TID:pfj9NTWO - END - KIBAR
                return response()->json($addresses, 200);
            }
            $subdistrict = explode(",", $param['subdistrict']);

            $address = new UserSavedAddress();
            $address->user_id = auth()->user()->id;
            $address->address_alias = $param['alias'];
            $address->address_recepient = $param['recepient'];
            $address->address_address = $param['address'];
            $address->address_subdistrict = trim($subdistrict[0]);
            $address->address_district = trim($subdistrict[1]);
            $address->address_province = trim($subdistrict[2]);
            $address->address_phone = $param['phone'];
            $address->address_zip = $param['zip'];
            $address->save();

            return response()->json(['message' => 'success', 'id' => $address->id], 200);
        } 
        
        if ($req->isMethod('GET')) {
            $addresses = UserSavedAddress::where('user_id', auth()->user()->id)->get();
            return response()->json($addresses);
        }

        if ($req->isMethod('DELETE')) {
            $address = UserSavedAddress::where([
                'user_id' => auth()->user()->id,
                'id' => $req->input('id')
            ])->first();

            if (is_null($address)) {
                return response()->json([
                    'message' => 'address not found'
                ], 404);
            }

            $address->delete();

            return response()->json([
                'message' => 'address deleted'
            ], 200);
        }
    }

    /* DigiAsia Integration */
    public function walletRegister(Request $request)
    {
        $walletUser = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (!is_null($walletUser)) {
            return response()->json([
                'message' => 'Akun kamu telah terdaftar menggunakan dompet digital KirimAja.'
            ], 406);
        }
        $param = $request->only('phone','firstName','lastName');
        $param['email'] = auth()->user()->email;
        $param['name'] = auth()->user()->fullname;
        $digiService = new DigiAsiaService();
        $register = $digiService->registerUser($param);

        if ($register['code'] == 0) {      
            $walletUser = new WalletAccount();
            $walletUser->user_id = auth()->user()->id;
            $walletUser->phone_number = $param['phone'];
            $walletUser->first_name = $param['firstName'];
            $walletUser->last_name = $param['lastName'];
            $walletUser->account_number = $register['account-number'];
            $walletUser->partner_token = $register['partner-token'];
            $walletUser->save();
            
            return response()->json([
                'message' => 'Pendaftaran berhasil'
            ], 202);
        } else if ($register['code'] == 101) {
            $accountInfo = $digiService->accountInquiry($param);
            
            $walletUser = new WalletAccount();
            $walletUser->user_id = auth()->user()->id;
            $walletUser->phone_number = $param['phone'];
            $walletUser->first_name = $param['firstName'];
            $walletUser->last_name = $param['lastName'];
            $walletUser->account_number = $accountInfo['account-number'];
            $walletUser->partner_token = $accountInfo['partner-token'];
            $walletUser->save();

            return response()->json([
                'message' => 'Pendaftaran berhasil'
            ], 202);
        } else {
            return response()->json($register, 406);
        }
        
    }

    public function assignPIN(Request $request)
    {
        $walletUser = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (is_null($walletUser)) {
            return response()->json([
                'message' => 'please register your account for using wallet.'
            ], 406);
        }

        if ($walletUser->pin != null) {
            return response()->json([
                'message' => 'user already had a pin.'
            ], 406);
        }

        $pin = $request->input('pin');
        
        if (strlen((string) $pin) < 6) {
            return response()->json([
                'message' => 'please use 6 digits PIN.'
            ], 406);
        }

        $walletUser->pin = Hash::make($pin);
        $walletUser->save();

        return response()->json([
            'message' => 'PIN successfully set.'
        ], 202);
    }

    public function verifyPIN(Request $request)
    {
        $walletUser = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (is_null($walletUser)) {
            return response()->json([
                'message' => 'please register your account for using wallet.'
            ], 406);
        }

        $pin = $request->input('pin');
        
        if (strlen((string) $pin) < 6) {
            return response()->json([
                'message' => 'please use 6 digits PIN.'
            ], 406);
        }

        if (Hash::check($pin, $walletUser->pin)) {
            return response()->json([
                'message' => 'Success'
            ], 202);
        } else {
            return response()->json([
                'message' => 'Incorrect'
            ], 406);
        }
    }

    public function forgetPIN(Request $request)
    {
        $walletUser = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (is_null($walletUser)) {
            return response()->json([
                'message' => 'please register your account for using wallet.'
            ], 406);
        }

        if ($walletUser->pin == null) {
            return response()->json([
                'message' => 'not eligible.'
            ], 406);
        }

        if ($request->has('token')) {
            if (Hash::check($request->input('token'), $walletUser->forget_pin)) {
                $walletUser->pin = null;
                $walletUser->forget_pin = null;
                $walletUser->forget_pin_validity = null;
                $walletUser->save();

                return response()->json([
                    'message' => 'PIN successfully reset.'
                ], 202);
            } else {
                return response()->json([
                    'message' => 'forget pin incorrect.'
                ], 406);
            }
        }

        if ($walletUser->forget_pin != null) {
            return response()->json([
                'message' => 'already requested'
            ], 406);
        }

        $forgetPin = rand(100000, 999999);
        $walletUser->forget_pin = Hash::make($forgetPin);
        $walletUser->forget_pin_validity = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $walletUser->save();

        Mail::to(auth()->user()->email)->send(new UserFogetPINMail(auth()->user(), $forgetPin));

        return response()->json([
            'message' => 'Verfikasi kode sudah kami kirimkan ke email anda.'
        ], 200);
    }

    public function walletCheckNumber(Request $request)
    {
        $phone = $request->input('phone');
        $digiService = new DigiAsiaService();

        if ($request->has('otp') && $request->input('otp') != '') {
            $validate = $digiService->validateOTP($phone, $request->input('otp'));
            return response()->json($validate, 200);
        } else {
            $checkNumber = $digiService->requestOTP($phone);
            return response()->json($checkNumber, 200);
        }
    }

    public function walletAccountInquiry(Request $request)
    {
        $param = $request->all();
        $data = array();
        $data['phone'] = $param['phone'];
        $data['firstName'] = $param['firstName'];
        $data['lastName'] = $param['lastName'];

        $digiService = new DigiAsiaService();
        $account = $digiService->accountInquiry($param); 

        if ($account['code'] == 0) {
            $validate = $digiService->accountValidate($data['phone']);
            if ($validate['code'] == 0) {
                error_log($validate['account-number']);
                error_log($account['partner-token']);
                WalletAccount::updateOrCreate(
                    [
                        'user_id' => auth()->user()->id, 
                        'phone_number' => $data['phone']
                    ],
                    [
                        'account_number' => $validate['account-number'],
                        'partner_token' => $account['partner-token'],
                    ]
                );
            }
        }

        unset($account['account-number']);

        return response()->json($account, 200);
    }

    public function walletAccountValidate(Request $request)
    {
        $phone = $request->input('phone');
        $digiService = new DigiAsiaService();
        $account = $digiService->accountValidate($phone); 

        return response()->json($account, 200);
    }

    public function walletPay(Request $request)
    {
        $param = $request->only('bookingCode');

        //20210305 - TID:pfj9NTWO - START - KIBAR
        //$booking = Booking::with('details', 'payment')->whereRaw("UPPER(booking_code) = '".$param['bookingCode']."'")->first();
        $bc = $param['bookingCode'];
        $booking = Booking::with('details', 'payment')->whereRaw("UPPER(booking_code) = :bookingCode",['bookingCode'=> $bc])->first();
        //20210305 - TID:pfj9NTWO - END - KIBAR
        if (is_null($booking)) {
            return response()->json(['message' => 'booking not found'], 406);
        }

        $paymentRequest = PaymentRequest::with('payment')->where('booking_id', $booking->id)->orderby('id', 'desc')->first();
        if (!is_null($paymentRequest) && $paymentRequest->payment->paid) {
            return response()->json(['message' => 'booking already paid'], 406);
        }

        $account = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (is_null($account)) {
            return response()->json(['message' => 'account not found, please get account info first'], 200);
        }

        $digiService = new DigiAsiaService();
        $amount = ($booking->payment->transaction_amount - $booking->payment->transaction_comission_amount) + $booking->payment->transaction_tax;
        $payment = $digiService->payment($account->account_number, $amount, $booking);
        
        /* if payment success, paid the bill */
        if ($payment['code'] == 0) {
            $paymentRequest = PaymentRequest::with('payment')->where('booking_id', $booking->id)->orderby('id', 'desc')->first();
            $paymentRequest->paid_at = new DateTime();
            $paymentRequest->paid_channel = '00';
            $paymentRequest->va_number = $payment['response-id'];
            $paymentRequest->transaction_amount = $amount;
            $paymentRequest->save();

            $paymentRequest->payment->paid = true;
            $paymentRequest->payment->paid_at = new DateTime();
            $paymentRequest->payment->paid_channel = '00';
            $paymentRequest->payment->paid_response = $payment['code'];
            $paymentRequest->payment->save();

            $ajcBooked = AJCBookingLog::where('booking_id', $booking->id)->first();
            if (is_null($ajcBooked)) {
                $ajcController = new AJCController();
                $ajcController->storeBooking($booking->id);
                event(new OrderReceiptEvent($booking));
            }
        }

        return response()->json($payment, 200);
    }

    public function walletTransaction(Request $request)
    {
        $account = WalletAccount::where('user_id', auth()->user()->id)->first();
        if (is_null($account)) {
            return response()->json(['message' => 'account not found, please get account info first'], 200);
        }

        $digiService = new DigiAsiaService();
        $payment = $digiService->transactions($account->account_number, $request->input('start'), $request->input('end'));
        return response()->json($payment, 200);
    }
    //20210226 - TID: Fsq6U7Ps - START
    public function subconsoleUpgrade(Request $request)
    {
        if ($request->isMethod('GET')) {

        } else {
            // t10
            $user = User::with(['identityCard'])->find(auth()->user()->id);
            $parameter = $request->only('req','address','district','npwp');

            if(empty($user->identityCard->identity_card) || empty($user->identityCard->identity_card_selfie) ){
                return response()->json(['message' => 'Lengkapi semua persyaratan pendaftaran SubConsole'], 406);

            }


            $addressLayer = null;
            $addressLayers = null;
            if (array_key_exists('district', $parameter)) {
                $addressLayer = $parameter['district'];
                $addressLayers = explode(',', $addressLayer);
            }

            if (array_key_exists('district', $parameter)) {
                $user->kecamatan = $addressLayers[0];
                $user->kota = $addressLayers[1];
                $user->provinsi = $addressLayers[2];
                $user->city_code = trim($addressLayers[1]);
            }
            // end t10

            $user->address = $parameter['address'];
            $user->npwp = $parameter['npwp'];
            $user->approved = 2;
            $user->save();
            return response()->json($user);
        }

    }
    //20210226 - TID: Fsq6U7Ps - END

    //20210308 - TID:pfj9NTWO - START - KIBAR
    public function logHistory($param){
        $Log = new Log();
        $Log->username = $param['username'];
        $Log->type = 'frontend';
        $Log->activity = $param['activity'];
        $Log->param = $param['param'];
        $Log->save();
        return ;

    }
    //20210308 - TID:pfj9NTWO - END - KIBAR
}
