<?php

namespace App\Http\Controllers;

use App\Direction;
use Illuminate\Http\Request;

class DirectionsController extends Controller
{
    public function directions(Request $request){
        $customer_id = $request->customer_id;

        $directions = Direction::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }

    public function saveDirection(Request $request){
        $direction_id = $request->direction_id;
        $customer_id = $request->customer_id;
        $direction_text = $request->direction;
        $direction_user = $request->user;
        $direction_datetime = $request->datetime;

        $direction = Direction::updateOrCreate([
            'id' => $direction_id
        ],[
           'customer_id' => $customer_id,
           'direction' => $direction_text,
           'user' => $direction_user,
           'date_time' => $direction_datetime
        ]);

        $directions = Direction::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'direction' => $direction, 'directions' => $directions]);
    }

    public function saveCustomerDirection(Request $request){
        $id = $request->id;
        $customer_id = $request->customer_id;
        $direction_text = $request->direction;
        $direction_user = $request->user;
        $direction_datetime = $request->date_time;

        $direction = Direction::updateOrCreate([
            'id' => $id
        ],[
           'customer_id' => $customer_id,
           'direction' => $direction_text,
           'user' => $direction_user,
           'date_time' => $direction_datetime
        ]);

        $directions = Direction::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'direction' => $direction, 'directions' => $directions]);
    }

    public function deleteDirection(Request $request){
        $direction_id = $request->direction_id;
        $customer_id = $request->customer_id;

        Direction::where('id', $direction_id)->delete();

        $directions = Direction::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }

    public function deleteCustomerDirection(Request $request){
        $direction_id = $request->id;
        $customer_id = $request->customer_id;

        Direction::where('id', $direction_id)->delete();

        $directions = Direction::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }
}
