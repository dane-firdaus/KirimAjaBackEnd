<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::match(['GET', 'POST'], 'forget-password', ['as' => 'webForgetPassword', 'uses' => 'UserController@doForgetPassword']);
Route::get('/cart_redirect_page', function () {
    return view('payment_cart_redirect');
});