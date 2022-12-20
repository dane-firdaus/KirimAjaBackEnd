<?php

namespace App\Http\Controllers;

use App\Food;
use Illuminate\Http\Request;

class FoodMarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('APITokenJWT');
    }
    
    public function getAllFood(Request $request)
    {
        $foods = Food::orderBy('id', 'desc')->get();

        return response()->json($foods);
    }

    public function newFood(Request $request)
    {
        $parameter = $request->only('name', 'description', 'storeId', 'storeName', 'locationName', 'locationCode', 'price', 'minWeight', 'isRecommend');

        $food = new Food();
        $food->name = $parameter['name'];
        $food->description = $parameter['description'];
        $food->store_id = $parameter['storeId'];
        $food->store_name = $parameter['storeName'];
        $food->location_city_name = $parameter['locationName'];
        $food->location_city_code = $parameter['locationCode'];
        $food->price = $parameter['price'];
        $food->min_weight = $parameter['minWeight'];
        $food->is_recommend = $parameter['isRecommend'];
        $food->save();

        return response()->json($food);
    }

    public function payment(Request $request)
    {
        # code...
    }
}
