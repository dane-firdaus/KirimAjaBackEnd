<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// User Route
Route::get('verification/{token}', ['as' => 'user-verification', 'uses' => 'UserController@userVerification']);

// Route::get('email_confirm', ['uses' => 'UserController@testEmail']);
// Route::get('order_booking', ['uses' => 'UserController@testOrderBooking']);
Route::get('welcomeMessage', ['uses' => 'UserController@welcomeMessage']);
Route::get('toc', ['uses' => 'UserController@termsConditions']);
Route::get('subConsoleTerms', ['uses' => 'UserController@subConsoleTerms']);
Route::get('faq', ['uses' => 'UserController@faqPage']);

Route::post('register', ['uses' => 'UserController@register']);
Route::post('login', ['uses' => 'UserController@login']);
Route::post('forget-password', ['uses' => 'UserController@forgetPassword']);
Route::match(['GET', 'POST'], 'me', ['uses' => 'UserController@me']);
Route::match(['GET', 'POST', 'DELETE'], 'me/address', ['uses' => 'UserController@userAddress']);
Route::match(['GET', 'POST'], 'subconsoleupgrade', ['uses' => 'UserController@subconsoleUpgrade']); //20210301 - TID:uhRBUCiI - START

Route::post('me/wallet', ['uses' => 'UserController@walletAccountInquiry']);
Route::post('me/wallet/phone-challenge', ['uses' => 'UserController@walletCheckNumber']);
Route::post('me/wallet/register', ['uses' => 'UserController@walletRegister']);
Route::post('me/wallet/register/pin', ['uses' => 'UserController@assignPIN']);
Route::post('me/wallet/forget/pin', ['uses' => 'UserController@forgetPIN']);
Route::post('me/wallet/pin', ['uses' => 'UserController@verifyPIN']);

Route::post('/daftarsubconsoleprofil', ['uses' => 'UserController@uploadprofilesubconsole']);  // 20210407  add t10 daftar suubconsole dari profil

Route::post('me/identity', ['uses' => 'UserController@identityPhoto']);
Route::get('me/identity/{type}', ['as' => 'identityPhoto', 'uses' => 'UserController@identityPhoto']);
Route::match(['GET', 'POST'], 'announcement', ['uses' => 'UserController@userAnnouncement']);
Route::get('refresh', ['uses' => 'UserController@refresh']);
Route::post('log', ['uses' => 'UserController@firebaseToken']);

Route::get('user/profile', ['uses' => 'UserController@userProfileImage']);

Route::group(['prefix' => 'guest'], function () {
    Route::post('destination', ['uses' => 'CustomerController@getDestination']);
    Route::post('subconsole', ['uses' => 'CustomerController@getSohibSubConsole']);
    Route::post('check-rate', ['uses' => 'CustomerController@getCheckRate']);
    Route::post('tracking', ['uses' => 'CustomerController@tracking']);
    Route::post('booking', ['uses' => 'CustomerController@booking']);
});

/* booking routes */
Route::group(['prefix' => 'booking'], function () {
    Route::post('register', ['uses' => 'BookingController@registerBooking']);
    Route::post('new', ['uses' => 'BookingController@booking']);
    Route::get('my', ['uses' => 'BookingController@myBooking']);
    Route::post('find', ['uses' => 'BookingController@findMyBooking']);
    Route::get('detail', ['uses' => 'BookingController@getBooking']);
    Route::post('detail', ['uses' => 'BookingController@deleteBooking']);
    Route::get('invoice', ['uses' => 'BookingController@getInvoice']);
    
    Route::post('edit', ['uses' => 'BookingController@editBooking']);
    Route::post('tracking', ['uses' => 'BookingController@tracking']);
    Route::post('rate', ['uses' => 'BookingController@getPricelist']);
    Route::post('check-rate', ['uses' => 'BookingController@getCheckRate']);
    
    Route::get('my-subconsole', ['uses' => 'BookingController@mySubconsole']);
    Route::get('my-subconsole/{id}', ['uses' => 'BookingController@mySubconsoleDetail']);
    Route::get('counter-subconsole', ['uses' => 'BookingController@counterSubconsole']); //20210322 - TID: EG6o8HSx - START
    Route::post('my-subconsole/update', ['uses' => 'BookingController@mySubconsoleUpdate']);

    /* this route use for sohib or sub-console to get, acceptance and correction booking from customer */
    Route::get('my-offer', ['uses' => 'BookingController@myBookingOffer']);
    Route::post('my-offer/acceptance', ['uses' => 'BookingController@bookingOfferAccaptance']);
    Route::post('verification', ['uses' => 'BookingController@verificationBooking']);
});

Route::group(['prefix' => 'marketplace'], function () {
    Route::group(['prefix' => 'food'], function () {
        Route::get('/', ['uses' => 'FoodMarketplaceController@getAllFood']);
        Route::post('new', ['uses' => 'FoodMarketplaceController@newFood']);
    });

    Route::post('order', ['uses' => 'MarketplaceOrderController@placeOrder']);
    Route::get('payment', ['uses' => 'PaymentController@marketplacePayment']);
    Route::post('payment/notify', ['uses' => 'PaymentController@marketplacePayment']);
});

/* payment routes */
Route::group(['prefix' => 'payment'], function () {
    Route::get('cart', ['uses' => 'PaymentController@getCart']);
    Route::post('cart', ['uses' => 'PaymentController@addCart']);
    Route::delete('cart', ['uses' => 'PaymentController@deleteCart']);
    Route::get('checkout', ['uses' => 'PaymentController@payCart']);
    Route::post('transaction', ['uses' => 'PaymentController@myTransaction']);
    Route::post('commission', ['uses' => 'PaymentController@myCommission']);
    Route::match(['GET', 'POST'], 'option', ['uses' => 'PaymentController@paymentOption']);
    Route::match(['GET', 'POST'], 'charge', ['uses' => 'PaymentController@chargePayment']);
    Route::match(['GET', 'POST'], 'pay', ['as' => 'paymentRequest', 'uses' => 'PaymentController@chargeTransaction']);
    Route::match(['GET', 'POST'], 'notify', ['uses' => 'PaymentController@notifyPayment']);
    Route::match(['GET', 'POST'], 'redirect', ['uses' => 'PaymentController@redirectPayment']);

    Route::post('promoCode', ['uses' => 'PaymentController@checkPromo']);

    Route::get('va', ['uses' => 'PaymentController@paymentVa']);
    Route::get('proof', ['uses' => 'PaymentController@getPaymentProof']);

    Route::post('wallet', ['uses' => 'UserController@walletPay']);
    Route::get('wallet/transaction', ['uses' => 'UserController@walletTransaction']);

    Route::post('bookings', ['uses' => 'PaymentController@bookings']);
    //20210224 - TID: 3B23WByr - START
    Route::post('getPromo', ['uses' => 'PaymentController@getPromo']);
    //20210224 - TID: 3B23WByr - END
});

/* master routes */
Route::group(['prefix' => 'master'], function () {
    Route::post('appCheck', ['uses' => 'MasterController@appCheck']);
    Route::get('branch', ['uses' => 'BookingController@getMasterAJCBranch']);
    Route::get('branchOffice', ['uses' => 'MasterController@getBranchOffice']);
    Route::get('destination', ['uses' => 'BookingController@getMasterAJCDestination']);
    Route::post('destination', ['uses' => 'BookingController@getDestination']);
    Route::get('deliveryPoint', ['uses' => 'MasterController@getDeliveryPoint']);
    Route::post('deliveryPoint', ['uses' => 'MasterController@storeDeliveryPoint']);
    //02032021 - TID: dLdzR8rs START
    Route::post('getcodebycity', ['uses' => 'MasterController@getCodeByCity']);
    //02032021 - TID: dLdzR8rs END
    Route::get('promos', ['uses' => 'MasterController@getPromo']);
    Route::get('commodities', ['uses' => 'MasterController@getCommodities']);
    Route::post('commodities', ['uses' => 'MasterController@newCommodities']);
    //Route::post('promos', ['uses' => 'MasterController@getPromo']);
});

/* dashboard */
Route::group(['prefix' => 'dashboard'], function () {
    Route::get('users', ['uses' => 'DashboardController@getUser']);
    Route::match(['GET', 'POST'], 'user/{id}', ['uses' => 'DashboardController@userDetail']);
    Route::get('bookings', ['uses' => 'DashboardController@getBooking']);
    Route::get('booking/{id}', ['uses' => 'DashboardController@getBookingDetail']);
    Route::get('bookingbyDate', ['uses' => 'DashboardController@getBookingbyDate']); 

    Route::get('exceedBooking', ['uses' => 'DashboardController@getExceedBooking']);
    Route::post('exceedBooking/update', ['uses' => 'DashboardController@editExceedBooking']);
    Route::post('tracking', ['uses' => 'DashboardController@trackingBooking']);

    Route::get('agent-by-cities', ['uses' => 'DashboardController@getTotalAgentInCities']);
    Route::get('agent-by-cities/{id}', ['uses' => 'DashboardController@getAgentByCities']);
    Route::get('agent-by-airport', ['uses' => 'DashboardController@getAgentByAirport']);
    Route::post('city/new', ['uses' => 'DashboardController@storeCityCoordinate']);

    /* Data analytics */
    Route::group(['prefix' => 'analytics'], function () {
        Route::get('bo-performance', ['uses' => 'DataAnalyticController@perfomanceDetailByBranchOffice']);
        Route::get('user-performance', ['uses' => 'DataAnalyticController@perfomanceTransactionUser']);
    });
    
    /* Troubleshoot */
    Route::group(['prefix' => 'troubleshoot'], function () {
        Route::post('awb-booking', ['uses' => 'TroubleshootController@issuedConnote']);
        Route::post('checkPayment', ['uses' => 'TroubleshootController@checkPayment']);
        Route::post('updatePayment', ['uses' => 'TroubleshootController@updatePaymentStatus']);
    });

    /* For Mobile */
    Route::post('secure', ['uses' => 'DashboardController@loginIntra']);
    Route::post('ltds', ['uses' => 'DashboardController@loginDashboard']);
    Route::post('dash-user', ['uses' => 'DashboardController@createDashboardUser']);
    Route::get('overview', ['uses' => 'DashboardController@getPerformaceOverview']);
    Route::get('payment/overview', ['uses' => 'DashboardController@getIncomeOverview']);
    Route::get('users/acquisition', ['uses' => 'DashboardController@userAcquisition']);
    Route::get('users/acquisition/type', ['uses' => 'DashboardController@userAcquisitionByUserType']);
    Route::get('users/active', ['uses' => 'DashboardController@activeUser']);
    Route::get('users/performance/booking', ['uses' => 'DashboardController@userBookingPerformance']);
    Route::get('users/performance/department', ['uses' => 'DashboardController@userDepartmentPerformance']);
    Route::get('bookings/trend', ['uses' => 'DashboardController@getBookingTrend']);
    Route::post('manualConfirmation', ['uses' => 'PaymentController@manualConfirmation']);

    /* Payment check */
    Route::get('payment/request', ['uses' => 'DashboardController@getPaymentRequest']);
    Route::post('payment/check', ['uses' => 'DashboardController@checkPaymentStatus']);

    /* migration */
    Route::post('mig/booking', ['uses' => 'DashboardController@importBookingData']);
    Route::post('mig/pricing', ['uses' => 'MasterController@importDistrictPrice']);

    /* partner management */
    Route::get('corporation/list', ['uses' => 'DashboardController@listCorporation']);
    Route::post('corporation/new', ['uses' => 'DashboardController@newCorporate']);
    Route::post('corporation/user/new', ['uses' => 'DashboardController@newCorporateLogin']);

    //Route::get('partner', ['uses' => 'DashboardController@listCorporation']);
    Route::post('partner/new', ['uses' => 'DashboardController@newPartner']);
    Route::get('voucher', ['uses' => 'DashboardController@getVouchers']);
    Route::post('voucher/new', ['uses' => 'DashboardController@newVoucher']);

    /* crm */
    // Route::get('crm', ['uses' => '']);
});

Route::group(['prefix' => 'corporate'], function () {
    Route::post('login', ['uses' => 'PartnerController@login']);
    Route::get('account', ['uses' => 'PartnerController@corporateProfile']);
    Route::get('dropPoint', ['uses' => 'PartnerController@getDeliveryPoint']);
    Route::post('directory', ['uses' => 'PartnerController@getMasterLocation']);
    Route::post('price', ['uses' => 'PartnerController@getPricing']);
    Route::post('booking', ['uses' => 'PartnerController@preBooking']);
    Route::post('booking/confirm', ['uses' => 'PartnerController@finalBooking']);
    Route::get('transaction/booking', ['uses' => 'PartnerController@myBooking']);
    Route::get('transaction/booking/{bookingCode}', ['uses' => 'PartnerController@bookingDetail']);
    Route::get('invoice', ['uses' => 'PartnerController@myInvoice']);
});

Route::group(['domain' => '{corporate}.kirimaja.id'], function () {
    Route::group(['prefix' => 'corporate'], function () {
        Route::post('login', ['uses' => 'PartnerController@login']);
        Route::get('account', ['uses' => 'PartnerController@corporateProfile']);
        Route::get('dropPoint', ['uses' => 'PartnerController@getDeliveryPoint']);
        Route::post('price', ['uses' => 'PartnerController@getPricing']);
        Route::post('booking', ['uses' => 'PartnerController@preBooking']);
        Route::post('booking/confirm', ['uses' => 'PartnerController@finalBooking']);
        Route::get('transaction/booking', ['uses' => 'PartnerController@myBooking']);
        Route::get('transaction/booking/{bookingCode}', ['uses' => 'PartnerController@bookingDetail']);
        Route::get('invoice', ['uses' => 'PartnerController@myInvoice']);
    }); 
});

Route::group(['prefix' => 'aerogroup'], function () {
    Route::post('shipment-notify', ['uses' => 'AJCController@shipmentNotification']);
});

Route::post('recon/store', ['uses' => 'ReconController@storeData']);
Route::get('recon/report', ['uses' => 'ReconController@reporting']);

//20210215 - TID: u532OC7c - START
Route::group(['prefix' => 'partners'], function () {
    Route::post('tokenRetrieval', ['uses' => 'APIPartnersController@login']);
    Route::post('findCity', ['uses' => 'APIPartnersController@getDestination']);
    Route::post('doReservation', ['uses' => 'APIPartnersController@booking']);
    Route::post('myReservation', ['uses' => 'APIPartnersController@myBooking']);
    Route::post('getReservation', ['uses' => 'APIPartnersController@findMyBooking']);
    Route::post('detailReservation', ['uses' => 'APIPartnersController@getBooking']);
    Route::post('doTracking', ['uses' => 'APIPartnersController@tracking']);
    Route::post('checkRate', ['uses' => 'APIPartnersController@getCheckRate']);
});

//20210215 - TID: u532OC7c - END

/*** backoffice 20210215 - TID: u532OC7c - START [T11]
 *****************************************************************************************************************************************/
Route::group(['prefix' => 'backoffice'], function () {
    Route::post('register', ['uses' => 'APIBackoffice@register','as' => 'BackofficeRegister']);
    Route::post('login', ['uses' => 'APIBackoffice@login','as' => 'BackofficeLogin']);
    Route::post('forget-password', ['uses' => 'APIBackoffice@forgetPassword','as' => 'BackofficeForgetPassword']);
    Route::get('user', ['uses' => 'APIBackoffice@userbackoffice','as' => 'userbackoffice']);
    Route::get('subconsole', ['uses' => 'APIBackoffice@usersubconsole','as' => 'usersubconsole']);
    Route::match(['GET', 'POST'], 'user-active', ['uses' => 'APIBackoffice@useractive','as' => 'activeuser']);

    Route::get('promo', ['uses' => 'APIBackoffice@promo','as' => 'Backofficepromo']);
    Route::post('Promo-save', ['uses' => 'APIBackoffice@promoSave','as' => 'Backofficepromo-save']);
    Route::get('promo-page/{id}', ['uses' => 'APIBackoffice@pagepromo', 'as' => 'Backofficepromo page promo']);
    Route::get('PromoID', ['uses' => 'APIBackoffice@pagepromoID', 'as' => 'BackofficepromoID page promo']);
    Route::post('log', ['uses' => 'APIBackoffice@log', 'as' => 'Backoffice log']);
    Route::post('aprsubconsole', ['uses' => 'APIBackoffice@aprsubconsole', 'as' => 'Backoffice accsubconsole']);
    Route::post('apruseroffice', ['uses' => 'APIBackoffice@apruseroffice', 'as' => 'Backoffice apruseroffice']);
    Route::match(['GET', 'POST'], 'officeuser-active', ['uses' => 'APIBackoffice@officeuseractive','as' => 'activeoffice']);
    Route::get('sendEmailVerificationUseconsole', ['uses' => 'APIBackoffice@Sendverificationuser','as' => 'Send Verification Email']);
    Route::match(['GET', 'POST'],'me', ['uses' => 'APIBackoffice@me','as' => 'me profil']); // t10 20210308 add jwt
    Route::get('booking-detail', ['uses' => 'APIBackoffice@getBooking']);
    Route::get('GetKodeBooking', ['uses' => 'APIBackoffice@GetKodeBooking']);

    //20210323 - TID: U9LgjemB - KIBAR
    Route::match(['GET', 'POST'],'connoteTroubleshoot', ['uses' => 'APIBackoffice@connoteTroubleshoot','as' => 'connoteTroubleshoot']); // paymnet abnormal 20210323
    Route::match(['GET', 'POST'],'connoteInvoiceTroubleshoot', ['uses' => 'APIBackoffice@connoteInvoiceTroubleshoot','as' => 'connoteInvoiceTroubleshoot']);
    Route::match(['GET', 'POST'],'connoteInvoiceDetailTroubleshoot', ['uses' => 'APIBackoffice@connoteInvoiceDetailTroubleshoot','as' => 'connoteInvoiceDetailTroubleshoot']);

    Route::post('generateConnote', ['uses' => 'APIBackoffice@generateConnote']);
    Route::get('reportConnoteTroubleshoot', ['uses' => 'APIBackoffice@reportConnoteTroubleshoot']);
    Route::get('findInvoiceTroubleshoot', ['uses' => 'APIBackoffice@FindconnoteInvoiceTroubleshoot']);
    Route::post('generatePaymentCart', ['uses' => 'APIBackoffice@generatePaymentCart']);
    Route::post('generatePaymentProof', ['uses' => 'APIBackoffice@generatePaymentProof']);
    Route::post('generateConnoteInvoice', ['uses' => 'APIBackoffice@generateConnoteInvoice']);

    Route::match(['GET', 'POST'],'voucher', ['uses' => 'APIBackoffice@voucher','as' => 'voucher']); // paymnet abnormal 20210323
    Route::get('table-voucher', ['uses' => 'APIBackoffice@tablevoucher']); // t10 voucher table
    Route::get('detail-voucher', ['uses' => 'APIBackoffice@detailvoucher']); // t10 voucher detail pop up
    Route::post('hapus-voucher', ['uses' => 'APIBackoffice@hapusvoucher']); // t10 voucher detail pop up


    //20210323 - TID: U9LgjemB - KIBAR
});

/*** backoffice 20210215 - TID: u532OC7c - End [T11]
 *****************************************************************************************************************************************/

Route::get('getHeaders', function (Request $request)
{
    Log::info(json_encode(\Request::header()));
    Log::info(json_encode(getallheaders()));
    $header = getallheaders();

    if (strpos($header['User-Agent'], 'com.garuda-indonesia.GA-DTD') && strpos($header['User-Agent'], 'build:34')) {
        return $header['User-Agent'];
    }
    
});

Route::get('mode', function () {
    // echo env('APP_ENV');
    echo phpinfo();
});
