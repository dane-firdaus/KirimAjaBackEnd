<?php

namespace App\Http\Controllers;

use App\Food;
use App\MarketplaceOrder;
use App\MarketplaceOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketplaceOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT');
    }

    public function placeOrder(Request $request)
    {
        $parameter = $request->only('orderType', 'orders', 'shipment');

        $totalCost = 0;

        for ($i=0; $i < count($parameter['orders']); $i++) { 
            $item = $parameter['orders'][$i];
            $food = Food::find($item['foodId']);
            $totalCost += $food->price * $item['quantity'];
        }

        $marketplace = new MarketplaceOrder();
        $marketplace->user_id = auth()->user()->id;
        $marketplace->booking = Str::random(10);
        $marketplace->product_type = $parameter['orderType'];
        $marketplace->total_cost = $totalCost;
        $marketplace->shipment_cost = $parameter['shipment']['cost'];
        $marketplace->shipment_name = $parameter['shipment']['name'];
        $marketplace->shipment_phone = $parameter['shipment']['phone'];
        $marketplace->shipment_address = $parameter['shipment']['address'];
        $marketplace->shipment_city = $parameter['shipment']['city'];
        $marketplace->shipment_zip_code = $parameter['shipment']['zipcode'];
        $marketplace->save();

        for ($i=0; $i < count($parameter['orders']); $i++) { 
            $item = $parameter['orders'][$i];
            $food = Food::find($item['foodId']);
            
            $detail = new MarketplaceOrderDetail();
            $detail->transaction_id = $marketplace->id;
            $detail->product_id = $food->id;
            $detail->product_price = $food->price;
            $detail->quantity = $item['quantity'];
            $detail->save();
        }

        return response()->json($marketplace);
    }


}
