<?php

namespace App\Http\Controllers;

use App\Models\CustomerHour;
use Illuminate\Http\Request;

class HoursController extends Controller
{
    public function getCustomerHours(Request $request)
    {
        $customer_id = $request->customer_id;

        $customer_hours = CustomerHour::where('customer_id', $customer_id)->first();

        return response()->json(['result' => 'OK', 'customer_hours' => $customer_hours]);
    }

    public function saveCustomerHours(Request $request)
    {
        $customer_id = isset($request->customer_id) ? trim($request->customer_id) : '';
        $hours_open = isset($request->hours_open) ? trim($request->hours_open) : '';
        $hours_close = isset($request->hours_close) ? trim($request->hours_close) : '';
        $delivery_hours_open = isset($request->delivery_hours_open) ? trim($request->delivery_hours_open) : '';
        $delivery_hours_close = isset($request->delivery_hours_close) ? trim($request->delivery_hours_close) : '';
        $hours_open2 = isset($request->hours_open2) ? trim($request->hours_open2) : '';
        $hours_close2 = isset($request->hours_close2) ? trim($request->hours_close2) : '';
        $delivery_hours_open2 = isset($request->delivery_hours_open2) ? trim($request->delivery_hours_open2) : '';
        $delivery_hours_close2 = isset($request->delivery_hours_close2) ? trim($request->delivery_hours_close2) : '';

        $cur_hours = CustomerHour::where('customer_id', $customer_id)->first();

        $customer_hours = CustomerHour::updateOrCreate([
            'id' => $cur_hours ? $cur_hours->id : 0
        ], [
            'customer_id' => $customer_id,
            'hours_open' => $hours_open,
            'hours_open2' => $hours_open2,
            'hours_close' => $hours_close,
            'hours_close2' => $hours_close2,
            'delivery_hours_open' => $delivery_hours_open,
            'delivery_hours_open2' => $delivery_hours_open2,
            'delivery_hours_close' => $delivery_hours_close,
            'delivery_hours_close2' => $delivery_hours_close2
        ]);

        $customer_hours = CustomerHour::where('customer_id', $customer_id)->first();

        return response()->json(['result' => 'OK', 'customer_hours' => $customer_hours]);
    }
}
