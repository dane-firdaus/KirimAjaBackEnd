<?php

namespace App\Http\Controllers;

use App\RptConnoteTroubleshoot;//20210323 - TID: U9LgjemB - KIBAR
use App\RptInvoiceTroubleshoot;//20210323 - TID: U9LgjemB - KIBAR
use App\PaymentCart;//20210323 - TID: U9LgjemB - KIBAR
use Illuminate\Support\Facades\DB;
use Adldap\Adldap;
use App\Events\OrderReceiptEvent;
use App\FirebaseToken;
use App\Mail\UserSubconsoleConfirmationMail;
use App\Mail\UserConfirmationMail;
use App\Mail\UserSubconsoleVerificationMaill;
use App\Mail\UserFogetPINMail;
use App\Mail\UserForgetPassword as MailUserForgetPassword;
use App\Payment;
use App\AJCBookingLog;
use App\PaymentRequest; //20210323 - TID: U9LgjemB - KIBAR
use App\Backoffice;
use App\Booking;
use App\VoucherUsage; //20210224 - START
use App\VourcherData; //20210224 - START
use App\VourcherDetail; //20210229 - START Detail voucer t10
use App\BookingDetail;
use App\BookingExceed;
use App\UserIdentityCard;
use App\User;
use App\Promo;
use App\Log;
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

class APIBackoffice extends Controller
{
    public function __construct()
    {
        // ,'promoSave' aprsubconsole , apruseroffice , officeuseractive
        $this->middleware('APITokenBackoffice', ['except' => [
            'login','register','forgetPassword','userbackoffice','useractive','promo','pagepromo','pagepromoID','log','usersubconsole','Sendverificationuser','connoteTroubleshoot','connoteInvoiceTroubleshoot','getBooking','GetKodeBooking','generateConnote','FindconnoteInvoiceTroubleshoot','generatePaymentCart','generatePaymentProof' //20210323 - TID: U9LgjemB - KIBAR
        ]
        ]);
    }

    public function register(Request $request)
    {
        $parameter = $request->only('fullname', 'email', 'password', 'username','type','role');

        if ($this->checkUser($parameter['email']) != null) {
            return response()->json(['message' => 'Mohon maaf, pengguna sudah terdaftar.'], 406);
        }


        $Backoffice = new Backoffice();
        $Backoffice->fullname = $parameter['fullname'];
        $Backoffice->email = strtolower($parameter['email']);
        $Backoffice->password = Hash::make($parameter['password']);
        $Backoffice->username = $parameter['username'];
        $Backoffice->type = $parameter['type'];
        $Backoffice->role = $parameter['role'];
        $Backoffice->active_status = 1;  // only active status 20210401 register di dalam


        $Backoffice->save();


        $response = [
            'message' => 'Kamu berhasil mendaftar sebagai User Backoffice Silahkan verifikasi email anda terlebih dahulu. Anda dapat memeriksa email dari kami pada bagian Kotak Masuk atau Spam.',
            'user' => $Backoffice
        ];

        // if (env('APP_ENV') == 'production') {

        //     Mail::to($Backoffice->email)->send(new UserConfirmationMail($Backoffice, route('user-verification', [$Backoffice->verification_token])));
        // }
        return response()->json($response);
    }

    private function checkUser($email)
    {
        $Backoffice = Backoffice::where('email', $email)->first();
        return $Backoffice;
    }


    public function login(Request $request)
    {
        $parameter = $request->only('email', 'password');
        $token = auth('backoffice-users')->attempt($parameter);

        if (!$token = auth('backoffice-users')->attempt($parameter)) {

            // $counter = \DB::select("select count(1) as total from log_activity where username=:username and activity='login_fail'  and type='backoffice' and created_at >=:today and created_at < :nextday",['username'=>$parameter['email'],'today'=>date('Y-m-d'),'nextday'=>date('Y-m-d', strtotime(' +1 day'))]);
            // if($counter > 5)
            // {
            //     // $user = Backoffice::where('email', $parameter['email'])->first();
            //     // $user->suspend_status = 1;
            //     // $user->save();
            // }

            $_param['activity']="login_fail";
            $_param['username']=$parameter['email'];
            $_param['param']='-';
            $_param['userid']='';
            $this->logHistory($_param);


            return response()->json(['message' => 'Maaf, username atau password yang Kamu masukan salah'], 401);
        }

        if($token){
            // if(auth('backoffice-users')->user()->suspend_status)
            // {
            //     $_param['activity']="login_suspend";
            //     $_param['username']=auth('backoffice-users')->user()->email;
            //     $_param['userid']=auth('backoffice-users')->user()->id;
            //     $_param['param']='-';
            //     $this->logHistory($_param);

            //     return response()->json(['message' => 'Your account is suspended because too many failed login, please contact our KirimAja Care'], 401);
            // }else{

            //     $_param['activity']="login_success";
            //     $_param['username']=auth('backoffice-users')->user()->email;
            //     $_param['userid']=auth('backoffice-users')->user()->id;
            //     $_param['param']='-';
            //     $this->logHistory($_param);
            // }
            if(auth('backoffice-users')->user()->active_status==0 ){
                return response()->json(['message' => 'Maaf status user anda belum di setujui,silahkan kontak superadmin.'], 401);

            }

            if(auth('backoffice-users')->user()->active_status==2 ){
                return response()->json(['message' => 'Maaf status user sudah di non-aktifkan kan,silahkan kontak superadmin.'], 401);

            }

            $_param['activity']="login_success";
            $_param['username']=auth('backoffice-users')->user()->email;
            $_param['userid']=auth('backoffice-users')->user()->id;
            $_param['param']='-';
            $this->logHistory($_param);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('backoffice-users')->factory()->getTTL()
        ]);
    }

    /***********
     * T10 20210406
     * Add filter type user subconsole / Req subconsole
     */
    public function usersubconsole(Request $request){
        $parameter = $request->only('page','limit','like','type');
        $type=$parameter['type'];
        $serch="";
        if(isset($parameter['like'])){

            $serch=$parameter['like'];
            $user = User::with(['identityCard'])
                ->where(['approved'=>$type])
                ->Where('fullname', 'ilike', '%' .$serch. '%')
                ->orwhere('email', 'ilike', '%' .$serch. '%')
                ->orderBy('id', 'desc')->paginate(10);
        }else{
            $user = User::with(['identityCard'])->where(['approved'=>$type])->orderBy('id', 'desc')->paginate(10);
            // $user = User::with(['identityCard'])->where(['approved'=>$type])->orwhere(['user_type'=>'subconsole'])->orderBy('id', 'desc')->paginate(10);
        }

        // $user = User::where(['user_type'=>'subconsole'])->orderBy('id', 'desc')->paginate(15);
        return response()->json($user);

    }

    public function userbackoffice(Request $request){
        $parameter = $request->only('page','limit','like');
        $serch="";
        if(isset($parameter['like'])){
            $serch=$parameter['like'];
            $userbackoffice = Backoffice::Where('fullname', 'ilike', '%' .$serch. '%')->orderBy('id', 'desc')->paginate(10);
        }else{
            $userbackoffice = Backoffice::Where('active_status','<','2')->orderBy('id', 'desc')->paginate(10);
        }

        // $userbackoffice = User::where(['user_type'=>'subconsole'])->orderBy('id', 'desc')->paginate(15);
        return response()->json($userbackoffice);

    }

    public function promo(Request $request){
        $parameter = $request->only('page','limit','like');
        $serch="";

        if(isset($parameter['like'])){
            $serch=$parameter['like'];
            $userbackoffice = Promo::where(['active'=>true])->Where('name', 'ilike', '%' .$serch. '%')->orderBy('id', 'desc')->paginate(15);
        }else{
            $userbackoffice = Promo::where(['active'=>true])->orderBy('id', 'desc')->paginate(15);
        }

        return response()->json($userbackoffice);

    }
    function UpdateDataPromo($param){
        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;

        DB::enableQueryLog();
        $url = str_replace('http://','https://',URL::to('/')).'/api/backoffice/promo-page/';

        $_data['name']=$param['name'];
        $_data['image']=$param['image'];

        $_data['description']=$param['description'];
        $_data['link']=$url.$param['name'];

        $publics="draf";
        if($param['post']>1){
            $publics="publish";
        }

        $_data['post']=$publics;


        Promo::where(['id'=> $param['id'] ])->update($_data);
        $quries =DB::getQueryLog();

        $_param['param']=json_encode($quries,true);
        $_param['activity']="update_promos:".$param['name'];
        $_param['userid']=$UserLog;
        $_param['username']=$Useremail;


        $this->logHistory($_param);

        return response()->json([
            'message' => 'Update berhasil'
        ], 200);
    }

    public function promoSave(Request $request){

        // dd(auth('backoffice-users')->user()->id);
        $url = str_replace('https://','http://',URL::to('/')).'/api/backoffice/promo-page/';

        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;

        DB::enableQueryLog();


        $parameter = $request->only('id','name', 'image', 'description', 'link','post');

        if(!empty($parameter['id'])){
            return $this->UpdateDataPromo($parameter);
        }else{


            if($parameter['post'] >1){
                $publics="publish";
            }else{
                $publics="draft";
            }
            $Promo = new Promo();
            $Promo->name = $parameter['name'];
            $Promo->image = $parameter['image'];
            $Promo->post = $publics;
            $Promo->description = $parameter['description'];
            $Promo->link =$url.$parameter['name']; //$parameter['link'];

            $Promo->save();

            $quries = DB::getQueryLog();
            $param['param']=json_encode($quries,true);
            $param['userid']=$UserLog;
            $param['username']=$Useremail;
            $param['activity']="save_promos:".$parameter['name'];
            $this->logHistory($param);


            $response = [
                'message' => 'Berhasil tambah promo.',
                'user' => $Promo
            ];

            return response()->json($response);
        }

    }

    public function pagepromo($id=''){
        $title=$id;
        $Promo=Promo::select('description')->where(['name'=> $title ])->first();
        $html ='<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="csrf-token" content="mDf7v9domBSQfoCCrT4ejNQFPSDFVfkML4ZYFfpg" />
            <title>KirimAja - Promo  '.$title.'</title>
            <link rel="shortcut icon" href="https://kirimaja.id/garuda_assets/images/icon.png"/>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
            
        </head>
        <style>
        .bg-light {
            background-color: #F7F9FC !important;
        }
            img{
                width: 100% !important;
            }
            #creditga{
                max-width: 300px;
                // font-size:14px !important;
            }  
            @media only screen and (max-width: 546px){
                #creditga{
                    max-width: 300px;
                    font-size:12px !important;
                }  
        }}
                
                body{
                    font-family: Arial;
                }
                
.footer-1 {
   position:fixed;
   bottom:0;
   width:100%;
}
          
        </style>
         <body class="bg-white">
        <div class="navbar-container">
              <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container" >
                 
                  <div class="collapse navbar-collapse order-3 order-lg-2 justify-content-lg-end" id="navigation-menu">
                  </div>
                </div>
              </nav>
            </div>
            <section><div class="page-section container align-items-center p-sm-2 bg-white">';

        $html .=$Promo->description;
        $html .='</div>
        </section>
        <br><br><br><br><br><br><br>
        <footer class="bg-primary-2 text-white links-white pb-1 footer-1 bg-dark">
            <div class="container">
              <div class="row">
                <div class="col">
                  <hr>
                </div>
              </div>
              <div class="row flex-column flex-lg-row align-items-center justify-content-center justify-content-lg-between text-center text-lg-left">
                <div class="col-auto">
                  <div class="d-flex flex-column flex-sm-row align-items-center text-small">
                    <div class="text-white" id="creditga">KirimAja by Aerojasa Cargo <br/> Garuda Indonesia Group
                    </div>
                  </div>
                </div>
                
          <div class="col-auto mt-2 mt-lg-0">
            <ul class="list-unstyled d-flex mb-0">
              <li class="mx-3">
                <a href="https://twitter.com/kirimaja_id" target="_blank" class="hover-fade-out">
                                           
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0)">
                <path d="M23.954 4.56897C23.069 4.95797 22.124 5.22297 21.129 5.34397C22.143 4.73297 22.923 3.76997 23.292 2.62097C22.341 3.17597 21.287 3.57997 20.165 3.80497C19.269 2.84597 17.992 2.24597 16.574 2.24597C13.857 2.24597 11.654 4.44897 11.654 7.16297C11.654 7.55297 11.699 7.92797 11.781 8.28697C7.691 8.09397 4.066 6.12997 1.64 3.16097C1.213 3.88297 0.974 4.72197 0.974 5.63597C0.974 7.34597 1.844 8.84897 3.162 9.73197C2.355 9.70597 1.596 9.48397 0.934 9.11597V9.17697C0.934 11.562 2.627 13.551 4.88 14.004C4.467 14.115 4.031 14.175 3.584 14.175C3.27 14.175 2.969 14.145 2.668 14.089C3.299 16.042 5.113 17.466 7.272 17.506C5.592 18.825 3.463 19.611 1.17 19.611C0.78 19.611 0.391 19.588 0 19.544C2.189 20.938 4.768 21.753 7.557 21.753C16.611 21.753 21.556 14.257 21.556 7.76697C21.556 7.55797 21.556 7.34697 21.541 7.13697C22.502 6.44797 23.341 5.57697 24.001 4.58897L23.954 4.56897Z" fill="#FFF"/>
                </g>
                <defs>
                <clipPath id="clip0">
                <rect width="24" height="24" fill="white"/>
                </clipPath>
                </defs>
                </svg>
     
                </a>
              </li>
              <li class="mx-3">
                <a href="https://www.instagram.com/kirimaja_id/" target="_blank" class="hover-fade-out">
                  
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 0C8.74 0 8.333 0.015 7.053 0.072C5.775 0.132 4.905 0.333 4.14 0.63C3.351 0.936 2.681 1.347 2.014 2.014C1.347 2.681 0.935 3.35 0.63 4.14C0.333 4.905 0.131 5.775 0.072 7.053C0.012 8.333 0 8.74 0 12C0 15.26 0.015 15.667 0.072 16.947C0.132 18.224 0.333 19.095 0.63 19.86C0.936 20.648 1.347 21.319 2.014 21.986C2.681 22.652 3.35 23.065 4.14 23.37C4.906 23.666 5.776 23.869 7.053 23.928C8.333 23.988 8.74 24 12 24C15.26 24 15.667 23.985 16.947 23.928C18.224 23.868 19.095 23.666 19.86 23.37C20.648 23.064 21.319 22.652 21.986 21.986C22.652 21.319 23.065 20.651 23.37 19.86C23.666 19.095 23.869 18.224 23.928 16.947C23.988 15.667 24 15.26 24 12C24 8.74 23.985 8.333 23.928 7.053C23.868 5.776 23.666 4.904 23.37 4.14C23.064 3.351 22.652 2.681 21.986 2.014C21.319 1.347 20.651 0.935 19.86 0.63C19.095 0.333 18.224 0.131 16.947 0.072C15.667 0.012 15.26 0 12 0ZM12 2.16C15.203 2.16 15.585 2.176 16.85 2.231C18.02 2.286 18.655 2.48 19.077 2.646C19.639 2.863 20.037 3.123 20.459 3.542C20.878 3.962 21.138 4.361 21.355 4.923C21.519 5.345 21.715 5.98 21.768 7.15C21.825 8.416 21.838 8.796 21.838 12C21.838 15.204 21.823 15.585 21.764 16.85C21.703 18.02 21.508 18.655 21.343 19.077C21.119 19.639 20.864 20.037 20.444 20.459C20.025 20.878 19.62 21.138 19.064 21.355C18.644 21.519 17.999 21.715 16.829 21.768C15.555 21.825 15.18 21.838 11.97 21.838C8.759 21.838 8.384 21.823 7.111 21.764C5.94 21.703 5.295 21.508 4.875 21.343C4.306 21.119 3.915 20.864 3.496 20.444C3.075 20.025 2.806 19.62 2.596 19.064C2.431 18.644 2.237 17.999 2.176 16.829C2.131 15.569 2.115 15.18 2.115 11.985C2.115 8.789 2.131 8.399 2.176 7.124C2.237 5.954 2.431 5.31 2.596 4.89C2.806 4.32 3.075 3.93 3.496 3.509C3.915 3.09 4.306 2.82 4.875 2.611C5.295 2.445 5.926 2.25 7.096 2.19C8.371 2.145 8.746 2.13 11.955 2.13L12 2.16ZM12 5.838C8.595 5.838 5.838 8.598 5.838 12C5.838 15.405 8.598 18.162 12 18.162C15.405 18.162 18.162 15.402 18.162 12C18.162 8.595 15.402 5.838 12 5.838ZM12 16C9.79 16 8 14.21 8 12C8 9.79 9.79 8 12 8C14.21 8 16 9.79 16 12C16 14.21 14.21 16 12 16ZM19.846 5.595C19.846 6.39 19.2 7.035 18.406 7.035C17.611 7.035 16.966 6.389 16.966 5.595C16.966 4.801 17.612 4.156 18.406 4.156C19.199 4.155 19.846 4.801 19.846 5.595Z" fill="#FFF"/>
                </svg>
                </a>
              </li>
              <li class="mx-3">
                <a href="https://web.facebook.com/kirimajaindonesia" target="_blank" class="hover-fade-out">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.789 23.9943V13.0493H17.3269L17.8566 8.78383H13.789V6.06047C13.789 4.82555 14.1192 3.98389 15.8248 3.98389L18 3.98284V0.167893C17.6236 0.1161 16.3325 0 14.8304 0C11.6942 0 9.54712 1.98771 9.54712 5.63825V8.78395H6V13.0494H9.54701V23.9944L13.789 23.9943Z" fill="#FFF"/>
                    </svg>

                </a>
              </li>
            </ul>
          </div>
              </div>
            </div>
          </footer>
    </body>
    </html>';
        return $html;

    }
    function Removepromosatus($param){
        $Promo=Promo::where(['id'=> $param ])->update(['active'=>false]) ;

        return $Promo;
    }

    public function pagepromoID(Request $request){
        $edit=$request->input('edit');

        if(isset($edit)){

            return $this->Removepromosatus($request->input('id'));

        }

        $id=$request->input('id');
        $Promo=Promo::where(['id'=> $id ])->first();
        return $Promo;

    }

    public function useractive(Request $request){
        $id=$request->input('id');
        $user = User::where(['id'=> $id ])->first();

        if (is_null($user)) {
            return response()->json([
                'message' => 'update gagal'
            ], 404);
        }

        $flag=1;
        $activity="Approval_subconsole:";
        $usertype='subconsole';
        if($user->flag > 0){
            $flag=0;
            $activity="Nonactive_subconsole:";
            $usertype='user';

        }

        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;
        DB::enableQueryLog();
        User::where(['id'=> $id ])->update([ 'flag' => $flag ,'user_type'=>$usertype]);

        $quries = DB::getQueryLog();
        $param['param']=json_encode($quries,true);
        $param['userid']=$UserLog;
        $param['username']=$Useremail;
        $param['activity']=$activity.$id;
        $this->logHistory($param);

        return response()->json([
            'message' => 'Update berhasil'
        ], 200);

    }

    public function officeuseractive(Request $request){
        $id=$request->input('id');
        $user = Backoffice::where(['id'=> $id ])->first();

        if (is_null($user)) {
            return response()->json([
                'message' => 'update gagal'
            ], 404);
        }

        $flag=2;
        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;

        DB::enableQueryLog();
        Backoffice::where(['id'=> $id ])->update([ 'active_status' => $flag]);

        $quries = DB::getQueryLog();
        $param['param']=json_encode($quries,true);
        $param['userid']=$UserLog;
        $param['username']=$Useremail;
        $param['activity']="Non_active_userbackoffice:".$id;
        $this->logHistory($param);


        return response()->json([
            'message' => 'Update berhasil'
        ], 200);

    }

    public function logHistory($param){
        $Log = new Log();
        $Log->userid = $param['userid'];
        $Log->username = $param['username'];
        $Log->type = 'backoffice';
        $Log->activity = $param['activity'];
        $Log->param = $param['param'];
        $Log->save();
        return ;

    }

    public function log(Request $request){
        $parameter = $request->only('username', 'activity', 'param');
        $Log = new Log();
        $Log->username = $parameter['username'];
        $Log->activity = $parameter['activity'];
        $Log->param = $parameter['param'];
        $Log->save();
        return ;

    }

    public function aprsubconsole(Request $request){
        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;

        $parameter = $request->only('user');
        $_user=json_decode($parameter['user'],true);


        foreach($_user  as $us){
            // dd($us);
            DB::enableQueryLog();
            $_data=array("user_type"=>"subconsole","approved"=>"1");
            User::where(['id'=> $us ])->update($_data);

            $quries = DB::getQueryLog();
            $param['param']=json_encode($quries,true);
            $param['username']=$Useremail;
            $param['userid']=$UserLog;
            $param['activity']="Approval_subconsole:".$us;
            $this->logHistory($param);

            $userMail = User::where( 'id', '=', $us)->first();
            Mail::to($userMail->email)->send(new UserSubconsoleConfirmationMail($userMail,"AB"));


        }


        $response = [
            'message' => 'Berhasil tambah promo.',
            'user' => "subconsole"
        ];



        return response()->json($response);
    }

    public function apruseroffice(Request $request){
        $UserLog=auth('backoffice-users')->user()->id;
        $Useremail=auth('backoffice-users')->user()->email;

        $parameter = $request->only('user','privilage');

        $_user=json_decode($parameter['user'],true);
        $_privilage=$parameter['privilage'];

        foreach($_user  as $us){

            DB::enableQueryLog();
            $_data=array("role"=>$_privilage,"active_status"=>"1");
            Backoffice::where(['id'=> $us ])->update($_data);

            $quries = DB::getQueryLog();
            $param['param']=json_encode($quries,true);
            $param['username']=$Useremail;
            $param['userid']=$UserLog;
            $param['activity']="Approval_userbackoffice:".$us;
            $this->logHistory($param);
        }


        $response = [
            'message' => 'Berhasil tambah promo.',
            'user' => "subconsole"
        ];

        return response()->json($response);
    }

    function Sendverificationuser(Request $request){
        $id=$request->input('id');
        $user = User::where(['id'=> $id ])->first();
        $Token=Hash::make($user->id."98");
        User::where(['id'=> $user->id ])->update(["password"=>$Token]);

        Mail::to($user->email)->send(new UserSubconsoleVerificationMaill($user,$user->id."98" ) );

        return response()->json([
            'message' => 'Anda berhasil kirim Email Verifikasi ke subconsole / Sohib'
        ], 200);

    }

    public function me(Request $request)
    {
        if ($request->isMethod('GET')) {
            $user = Backoffice::where('id', auth('backoffice-users')->user()->id)->first();
            return response()->json($user);
        }else{
            $UserLog=auth('backoffice-users')->user()->id;
            $Useremail=auth('backoffice-users')->user()->email;

            DB::enableQueryLog();

            $newPass=Hash::make($request->input('password'));
            $_data['email']=$request->input('email');
            $_data['fullname']=$request->input('fullname');
            $_data['username']=$request->input('username');
            $_data['password']=$newPass;


            Backoffice::where(['id'=>$UserLog ])->update($_data);
            $quries =DB::getQueryLog();

            $_param['param']=json_encode($quries,true);
            $_param['activity']="update Profil & User Pass :".$request->input('email');
            $_param['userid']=$UserLog;
            $_param['username']=$Useremail;
            $_param['suspend_status']=0;


            $this->logHistory($_param);

            return response()->json([
                'message' => '000'
            ], 200);
        }

    }


    function connoteTroubleshoot(Request $request) // paymnet cek t10 20210323
    {

        $_param['activity']="get list booking - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('page','limit','like');
        // $serch="";
        // if(isset($parameter['like'])){
        //     $serch=$parameter['like'];
        //     $serch="and like ajc.awb '%$serch%'";
        //     // $user = User::with(['identityCard'])->where(['user_type'=>'subconsole'])->Where('fullname', 'ilike', '%' .$serch. '%')->orderBy('id', 'desc')->paginate(10);
        // }else{
        //     // $user = User::with(['identityCard'])->where(['approved'=>'2'])->orwhere(['user_type'=>'subconsole'])->orderBy('id', 'desc')->paginate(10);
        // }

        if ($request->isMethod('GET')) {
//            $Payment = \DB::select("select ka.booking_code,ajc.booking_id,ajc.awb,pay.transaction_amount,pay.transaction_tax,pay.paid
//                                from trx_payment as pay
//                                inner join trx_ajc_booking as ajc  on ajc.booking_id=pay.booking_id
//                                inner join trx_booking as ka  on ka.id=pay.booking_id
//                                where (ajc.awb =0 and pay.paid=true) OR  (ajc.awb !=0 and pay.paid=true)");
            $Payment = \DB::select("select ka.booking_code,ka.created_at as booking_date,ajc.booking_id,ajc.awb,pay.transaction_amount,pay.transaction_tax,pay.paid
                                from trx_payment as pay 
                                inner join trx_ajc_booking as ajc  on ajc.booking_id=pay.booking_id
                                inner join trx_booking as ka  on ka.id=pay.booking_id
                                where (ajc.awb =0 and pay.paid=true)
                                order by ka.created_at desc");
            return response()->json($Payment);
        }else{

            $parameter = $request->only('code');
            dd($parameter['code']);

        }

        // DB::enableQueryLog();

        //  $Payment = Payment::with(['ajcbooking'])->where(['paid'=>true,'awb'=>'0'])->orderBy('updated_at', 'desc')->paginate(10);
        // $quries = DB::getQueryLog();
        //  dd($quries);

        // $user = User::where(['user_type'=>'subconsole'])->orderBy('id', 'desc')->paginate(15);
    }

    function FindconnoteInvoiceTroubleshoot(Request $request) // paymnet cek t10 20210323
    {
        $_param['activity']="find by invoice  - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('code','noncarting');

        if ($request->isMethod('GET')) {

            if(isset($parameter['noncarting'])){

                $pay_req = PaymentRequest::where('transid',$parameter['code'])->whereNull(['cart_ids'])->first();

                $pay=Payment::with(['Booking'])->where('booking_id',$pay_req->booking_id)->first();


                $return[$pay->id]=array(
                    'idbooking'=>$pay->Booking->id,
                    'codebooking'=>strtoupper($pay->Booking->booking_code),
                    'invoice'=>$pay_req->transid,
                    'paid'=>$pay->paid,
                );

                $get_invoice_troubleshoot = RptInvoiceTroubleshoot::where('invoice',$pay_req->transid)->first();
                if(!empty($get_invoice_troubleshoot->payment_proof))
                {
                    $return[$pay->id]['proof'] = $get_invoice_troubleshoot->payment_proof;
                }


            }else{
                $pay_req = PaymentRequest::where('transid',$parameter['code'])->first();
                $get_invoice_troubleshoot = RptInvoiceTroubleshoot::where('invoice',$pay_req->transid)->first();

                // $booking = Booking::with(['payment','paymentRequest'])->whereRaw('UPPER(booking_code) = ?', $bookingCode)->first();

                $carting = PaymentCart::with(['Booking'])->whereIn('id',json_decode($pay_req->cart_ids))->get();
                $return=[];

                foreach(json_decode($pay_req->cart_ids) as $a){
                    $return[$a]=array(
                        'idbooking'=>"",
                        'codebooking'=>"",
                        'invoice'=>"",
                        'paid'=>"",
                        'payment_proof'=>""
                    );
                }


                foreach($carting as $cart)
                {

                    $pay=Payment::where('booking_id',$cart->Booking->id)->first();
                    $return[$cart->id]=array(
                        'idbooking'=>$cart->Booking->id,
                        'codebooking'=>strtoupper($cart->Booking->booking_code),
                        'invoice'=>$pay_req->transid,
                        'paid'=>$pay->paid,
                    );
                    if(!empty($get_invoice_troubleshoot->payment_proof))
                    {
                        $return[$cart->id]['proof'] = $get_invoice_troubleshoot->payment_proof;
                    }
                }
            }



            return response()->json($return);
        }
    }


    function connoteInvoiceTroubleshoot(Request $request) // paymnet cek t10 20210323
    {
        if ($request->isMethod('GET')) {
            $pay_req = PaymentRequest::whereNotNull(['cart_ids','paid_channel','va_number','status_code'])->whereNull(['booking_id'])->get();

            $o=0;
            $return = array();
            foreach($pay_req as $reqs)
            {
                $return[$o] = new \stdClass();
                $return[$o]->invoice = $reqs->transid;
                $return[$o]->va= $reqs->va_number;
                $return[$o]->created_at = $reqs->created_at;
                $o++;
            }
            return response()->json($return);
        }
    }

    function connoteInvoiceDetailTroubleshoot(Request $request) // paymnet cek t10 20210323
    {
        echo $request->input('inv');
        if ($request->isMethod('POST')) {
            $pay_req = PaymentRequest::whereNotNull(['cart_ids','paid_channel','va_number','status_code'])->whereNull(['booking_id'])->where(['id'=>$request->input('inv')])->get();

            $o=0;
            $return = array();
            foreach($pay_req as $reqs)
            {
                $cartIds = json_decode($reqs->cart_ids, true);
                foreach ($cartIds as $id) {
                    $cart = PaymentCart::with(['booking:id,booking_code', 'booking.payment'])->where([
                        'id' => $id,
                    ])->orderBy('id','desc')->first();

                    if(!empty($cart))
                    {
                        if(!$cart->paid)
                        {
                            print_r($cart->booking_code);
                            $return[$o] = new \stdClass();
                            $return[$o]->invoice = $reqs->transid;
                            $return[$o]->va= $reqs->va_number;
                            $return[$o]->channel = $reqs->paid_channel;
                            $return[$o]->booking_id = $cart->booking_id;
                            $return[$o]->paid_status = $cart->paid;
                            $o++;
                        }

                    }
                }
            }
            return response()->json($return);
        }else{

            $parameter = $request->only('code');
            dd($parameter['code']);

        }
    }
    public function getBooking(Request $request)
    {
        $_param['activity']="find booking";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $booking = Booking::with([
            'details.commodity:id,commodity_name',
            'paymentRequest',  //20210330 - TID: U9LgjemB - KIBAR
            'payment',
            'deliveryPoint',
            'subConsole:id,fullname,address,kecamatan,phone',
            'subConsole.identityCard:id,user_id,profile_image',
            'validInfo:id,booking_id,valid','shipment', 'exceed'])->where(['id' => $request->input('id')])->first();

        if (is_null($booking)) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        //20210224 - TID: 3B23WByr - START
        $voucherInfo = VoucherUsage::with(['master'])->where('booking_id', $booking->id)->first();

        if (!is_null($voucherInfo)) {
            $booking->voucher_info=$voucherInfo->master->voucher_code;
            $booking->voucher_value=$voucherInfo->master->voucher_value;
        }

        //20210224 - TID: 3B23WByr - END

        //20210323 - TID: U9LgjemB - KIBAR
        $RptTroubleshoot = RptConnoteTroubleshoot::where('booking_id', $booking->id)->first();
        if (!is_null($RptTroubleshoot)) {
            $booking->connote_troubleshoot_email=$RptTroubleshoot->email;
            $booking->connote_troubleshoot_date=$RptTroubleshoot->created_at;
        }
        //20210323 - TID: U9LgjemB - KIBAR

        //20210330 - TID: U9LgjemB - KIBAR
        if(!empty($booking->paymentRequest))
        {
            if(count($booking->paymentRequest) > 0)
            {
                $dt = (count($booking->paymentRequest) > 1) ? count($booking->paymentRequest)-1 : 0;
                $get_invoice_troubleshoot = RptInvoiceTroubleshoot::where('invoice',$booking->paymentRequest[$dt]->transid)->first();
                if(!empty($get_invoice_troubleshoot) && count($booking->paymentRequest) > 0) {
                    $booking->paymentRequest[$dt]->payment_proof = $get_invoice_troubleshoot->payment_proof;
                }
            }
        }
        //20210330 - TID: U9LgjemB - KIBAR

        return response()->json($booking);
    }


    public function GetKodeBooking(Request $request)
    {
        $_param['activity']="get booking - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $booking = Booking::whereRaw('UPPER(booking_code) = ?', $request->input('code'))->first();

        // where(['booking_code' => $request->input('code')])
        return response()->json($booking->id);
    }
    //20210323 - TID: U9LgjemB - KIBAR
    public function generateConnote(Request $request)
    {
        $_param['activity']="generate connote menu 1 - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);


        $parameter = $request->only('code');
        $_code=json_decode($parameter['code'],true);

        foreach($_code as $code)
        {
            $booking = Booking::with(['shipment','payment','paymentRequest'])->where(['id' => $code])->first();
            if (is_null($booking)) {
                return response()->json([
                    'message' => 'booking not found'
                ], 200);
            }

            $carting = PaymentCart::where('booking_id', $booking->id)->first();

//            if (($booking->paymentRequest == null || count($booking->paymentRequest) == 0) && (!empty($carting->booking_id))) {
//                return response()->json([
//                    'message' => 'booking not yet request for the payment',
//                    'booking' => $booking
//                ], 200);
//            }

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


                if(!empty($storeBooking->detail->cnote_no))
                {
                    $RptTroubleshoot = new RptConnoteTroubleshoot();
                    $RptTroubleshoot->booking_id = $booking->id;
                    $RptTroubleshoot->booking_code = $booking->booking_code;
                    $RptTroubleshoot->awb = $storeBooking->detail->cnote_no;
                    $RptTroubleshoot->user_id = auth('backoffice-users')->user()->id;
                    $RptTroubleshoot->email = auth('backoffice-users')->user()->email;
                    $RptTroubleshoot->save();
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

                if(!empty($storeBooking->detail->cnote_no))
                {
                    $RptTroubleshoot = new RptConnoteTroubleshoot();
                    $RptTroubleshoot->booking_id = $booking->id;
                    $RptTroubleshoot->booking_code = $booking->booking_code;
                    $RptTroubleshoot->awb = $storeBooking->detail->cnote_no;
                    $RptTroubleshoot->user_id = auth('backoffice-users')->user()->id;
                    $RptTroubleshoot->email = auth('backoffice-users')->user()->email;
                    $RptTroubleshoot->save();
                }
            }
        }

        return response()->json([
            'message' => 'success rebooking awb',
        ], 200);
    }
    function reportConnoteTroubleshoot(Request $request) // paymnet cek t10 20210323
    {

        $_param['activity']="report troubleshoot - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('page','limit','like','filter');

        if ($request->isMethod('GET')) {

            if( $request->input('filter') ){

                $_filter=explode("to",$request->input('filter'));
                $Payment = RptConnoteTroubleshoot::with('proof')->whereDate('created_at', '>=', $_filter[0])->whereDate('created_at', '<=', $_filter[1])->orderBy('created_at', 'DESC')->get();
            }else{
                $Payment = RptConnoteTroubleshoot::with('proof')->whereDate('created_at', '>=', '2021-03-22')->orderBy('created_at', 'DESC')->get();
            }

            // if(!empty($parameter['filter']  )
            // {

            //     $_filter=explode("to",$request->input('filter'));
            //
            // }else{
            //     $Payment = RptTroubleshoot::whereDate('created_at', '>=', '2021-03-22')->get();
            // }
            return response()->json($Payment);
        }else{

            $parameter = $request->only('code');
            dd($parameter['code']);

        }

    }

    function generatePaymentCart(Request $request)
    {

        $_param['activity']="generate cart payment case 3 - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('code_booking');
        $_code=json_decode($parameter['code_booking'],true);

        foreach($_code as $k => $v) {
            $booking = Booking::whereRaw('UPPER(booking_code) = ?', $v)->first();


//            if (is_null($booking) || count($booking) == 0) {
//                return response()->json(['message' => 'booking not found'], 406);
//            }

            //20210323 - TID: U9LgjemB - KIBAR
            PaymentCart::updateOrCreate(
                ['id' => $k,'user_id' => $booking->user_id, 'booking_id' => $booking->id],
                ['id' => $k,'user_id' => $booking->user_id, 'booking_id' => $booking->id]
            );
            //20210323 - TID: U9LgjemB - KIBAR
        }
        return response()->json(['message' => 'ok'], 200);
    }
    function generatePaymentProof(Request $request)
    {
        $_param['activity']="generate payment proof - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        if ($request->isMethod('POST')) {

            $invoice = $request->input('invoice');
            $filename = $request->input('filename');
            $file = preg_replace('/\s+/', '',$invoice).'_invoice_'.time().'.'.$filename;
//            $storeFile = Storage::putFileAs(
//                'public/user_identity_assets', $photo, $filename
//            );

            $RptTroubleshoot = new RptInvoiceTroubleshoot();
            $RptTroubleshoot->invoice = $invoice;
            $RptTroubleshoot->payment_proof = $file;
            $RptTroubleshoot->user_id = auth('backoffice-users')->user()->id;
            $RptTroubleshoot->save();

        }

        return response()->json(['message' => 'ok','filename'=>$file], 200);
    }
    public function generateConnoteInvoice(Request $request)
    {
        $_param['activity']="generate connote by invoice - connote troubleshoot";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $code = $request->input('code');
        $invoice = $request->input('invoice');

        $booking = Booking::with(['shipment','payment'])->whereRaw('UPPER(booking_code) = ?', $request->input('code'))->first();

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

        $pay_req = PaymentRequest::where('transid',$invoice)->first();

        $booking->payment->paid = true;
        $booking->payment->paid_at = new DateTime();
        $booking->payment->paid_channel = $pay_req->paid_channel;
        $booking->payment->paid_response = 0;
        $booking->payment->save();

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

            if(!empty($storeBooking->detail->cnote_no))
            {
                event(new OrderReceiptEvent($booking));

                $RptTroubleshoot = new RptConnoteTroubleshoot();
                $RptTroubleshoot->booking_id = $booking->id;
                $RptTroubleshoot->booking_code = $booking->booking_code;
                $RptTroubleshoot->awb = $storeBooking->detail->cnote_no;
                $RptTroubleshoot->user_id = auth('backoffice-users')->user()->id;
                $RptTroubleshoot->email = auth('backoffice-users')->user()->email;
                $RptTroubleshoot->invoice = $pay_req->transid;
                $RptTroubleshoot->save();
            }

            return response()->json([
                'message' => 'ok',
            ], 200);
        }


        return response()->json([
            'message' => 'ok',
        ], 200);
    }
    //20210323 - TID: U9LgjemB - KIBAR

    // voucher t10 20210329
    function voucher(Request $request) //
    {

        // $parameter = $request->only('page','limit','like');
        // $request->input('code')
        if ($request->isMethod('GET')) {
            // $Payment = \DB::select("select ka.booking_code,ka.created_at as booking_date,ajc.booking_id,ajc.awb,pay.transaction_amount,pay.transaction_tax,pay.paid
            //                     from trx_payment as pay
            //                     inner join trx_ajc_booking as ajc  on ajc.booking_id=pay.booking_id
            //                     inner join trx_booking as ka  on ka.id=pay.booking_id
            //                     where (ajc.awb =0 and pay.paid=true)
            //                     order by ka.created_at desc");
            //                     return response()->json($Payment);
        }else{



            if($request->input('submit')=="save"){
                $_cvoucher=VourcherData::where('voucher_code', $request->input('voucher_code'))->first();

                if($_cvoucher){
                    return response()->json([
                        'message' => '0001',
                    ], 201);
                }
            }

            if($request->input('submit')=="save"){
                $_param['activity']="Create Code Voucher -".$request->input('voucher_code');
                $_param['username']=auth('backoffice-users')->user()->email;
                $_param['userid']=auth('backoffice-users')->user()->id;
                $_param['param']='-';
                $this->logHistory($_param);
            }else{
                $_param['activity']="Update Code Voucher -".$request->input('voucher_code');
                $_param['username']=auth('backoffice-users')->user()->email;
                $_param['userid']=auth('backoffice-users')->user()->id;
                $_param['param']='-';
                $this->logHistory($_param);
            }

            // t10 target user 1 all 2 single 3 multiple 20210401
            $target=0;
            if($request->input('user_target')==0){
                $target=1;
            }else{
                if(sizeof(json_decode($request->input('row_target'),true ))>1){
                    $target=3;
                }else{
                    $target=2;
                }
            }

            if($request->input('voucher_type') && $request->input('voucher_type')=='percentage')
            {
                if($request->input('voucher_value') <= 0 || $request->input('voucher_value') > 100)
                {
                    return response()->json([
                        'message' => '0002',
                    ], 201);
                }
            }


            if($request->input('submit')=="save"){
                $voucher=new VourcherData();
                $voucher->partner_id="2"; //  KirimAja Aerojasa Cargo;
                $voucher->voucher_code=$request->input('voucher_code');
                $voucher->voucher_name=$request->input('voucher_name');
                $voucher->voucher_value=$request->input('voucher_value');
                $voucher->voucher_type=$request->input('voucher_type');
                $voucher->start_date=$request->input('start_date');
                $voucher->end_date=$request->input('end_date');
                $voucher->one_time_usage=$request->input('one_time_usage');
                $voucher->quota=$request->input('quota');
                $voucher->quota_unlimited=$request->input('quota_unlimited');
                $voucher->budget_limit=$request->input('budget_limit');
                $voucher->budget_unlimited=$request->input('budget_unlimited');
                $voucher->user_target=$target;
                $voucher->save();
            }else{
                $data=array(
                    'voucher_name'=>$request->input('voucher_name'),
                    'voucher_value'=>$request->input('voucher_value'),
                    'voucher_type'=>$request->input('voucher_type'),
                    'start_date'=>$request->input('start_date'),
                    'end_date'=>$request->input('end_date'),
                    'one_time_usage'=>$request->input('one_time_usage'),
                    'quota'=>$request->input('quota'),
                    'quota_unlimited'=>$request->input('quota_unlimited'),
                    'budget_limit'=>$request->input('budget_limit'),
                    'budget_unlimited'=>$request->input('budget_unlimited'),
                    'user_target'=>$target
                );

                $update=VourcherData::where(['id'=> $request->input('id') ])->update($data);

                $detail=VourcherDetail::where('voucher_id', $request->input('id'))->delete();

            }


            foreach( json_decode($request->input('row_target'),true ) as  $key=>$data ){

                if(!empty($data[0])){
                    $user = User::where('email', $data[0])->first();



                    if($user){
                        $voucherDetail=new VourcherDetail();

                        if($request->input('submit')=="save"){
                            $voucherDetail->voucher_id=$voucher->id;
                        }else{
                            $voucherDetail->voucher_id=$request->input('id');
                        }

                        $voucherDetail->user_id= $user->id;
                        $voucherDetail->save();
                    } }

            }

            return response()->json([
                'message' => 'ok',
            ], 200);

        }

    }

    function tablevoucher(Request $request) // voucher  cek t10 20210329
    {
        $_param['activity']="View Table-voucher  -";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('code');
        if ($request->isMethod('GET')) {
            $voucher = VourcherData::orderBy('id', 'desc')->get();

            // $carting = PaymentCart::with(['Booking'])->whereIn('id',json_decode($pay_req->cart_ids))->get();
            $return=[];

            foreach($voucher as $a){
                $return[]=array(
                    'id'=>$a->id,
                    'codevoucher'=>$a->voucher_code,
                    'namavoucher'=>$a->voucher_name,
                    'valuevoucher'=>number_format($a->voucher_value,0,',','.'),
                    'typevoucher'=>$a->voucher_type,
                    'startvoucher'=>date('d-m-Y H:i:s',strtotime($a->start_date)), // t10 20210401 format data
                    'endvoucher'=>date('d-m-Y H:i:s',strtotime($a->end_date)),  // t10 20210401 format data
                    'onetimeusage'=>$a->one_time_usage,
                    'quota'=>number_format($a->quota,0,',','.'),
                    'quota_unlimited'=>number_format($a->quota_unlimited,0,',','.'),
                    'budget_limit'=>number_format($a->budget_limit,0,',','.'),
                    'budget_unlimited'=>number_format($a->budget_unlimited,0,',','.'),
                    'usertarget'=>$a->user_target,
                    'active'=>$a->active_status,
                );
            }


            return response()->json($return);
        }
    }

    function detailvoucher(Request $request) // voucher  cek t10 20210329
    {
        $_param['activity']="View Detail-voucher  -";
        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $parameter = $request->only('code');
        if ($request->isMethod('GET')) {

            // $voucher = VourcherDetail::where('voucher_id', $parameter['code'])->first();
            $return=[];
            $voucher = VourcherData::where('id', $parameter['code'])->first();

            $return['data']=array(
                'id'=>$voucher->id,
                'codevoucher'=>$voucher->voucher_code,
                'namavoucher'=>$voucher->voucher_name,
                'valuevoucher'=>number_format($voucher->voucher_value,0,',','.'),
                'typevoucher'=>$voucher->voucher_type,
                'startvoucher'=>$voucher->start_date,
                'endvoucher'=>$voucher->end_date,
                'onetimeusage'=>$voucher->one_time_usage,
                'quota'=>number_format($voucher->quota,0,',','.'),
                'quota_unlimited'=>number_format($voucher->quota_unlimited,0,',','.'),
                'budget_limit'=>number_format($voucher->budget_limit,0,',','.'),
                'budget_unlimited'=>number_format($voucher->budget_unlimited,0,',','.'),
                'usertarget'=>$voucher->user_target,
                'sumpakai'=>VoucherUsage::where('voucher_id', $voucher->id)->sum('transaction_voucher_amount'), // 20210331 sum pemakaian t10
                'countpakai'=>VoucherUsage::where('voucher_id', $voucher->id)->count('transaction_voucher_amount'), // 20210406 count pemakaian t10
            );
            $detail=VourcherDetail::where('voucher_id', $parameter['code'])->get();
            $return['detail']=[];
            foreach($detail as $d){
                $User=User::where('id', $d->user_id)->first();
                $return['detail'][]=array(
                    'user'=>$User->fullname,
                    'email'=>$User->email,
                );
            }
            return response()->json($return);
        }
    }

    function hapusvoucher(Request $request){  // hapus voucher 20210331t10
        $parameter = $request->only('code','active');

        $active=($parameter['active']==1?0:1);
        $_param['activity']=($parameter['active']==1?"Non Active ":"Re Active ")."Voucer code  -".$parameter['code'];

        $_param['username']=auth('backoffice-users')->user()->email;
        $_param['userid']=auth('backoffice-users')->user()->id;
        $_param['param']='-';
        $this->logHistory($_param);

        $voucher = VourcherData::where(['voucher_code'=> $parameter['code'] ])->update(['active_status'=>$active]);

        return response()->json([
            'message' => '0000',
        ], 200);

    }


}
