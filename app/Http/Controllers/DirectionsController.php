<?php

namespace App\Http\Controllers;

use App\Direction;
use Illuminate\Http\Request;

class DirectionsController extends Controller
{
    public function directions(Request $request){
        $directions = Direction::whereRaw("1 = 1")->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }

    public function saveDirection(Request $request){
        $direction_text = $request->direction;
        $direction_user = $request->user;
        $direction_datetime = $request->datetime;

        $direction = new Direction();
        $direction->direction = $direction_text;
        $direction->user = $direction_user;
        $direction->date_time = $direction_datetime;
        $direction->save();

        $directions = Direction::whereRaw("1 = 1")->get();

        return response()->json(['result' => 'OK', 'direction' => $direction, 'directions' => $directions]);
    }
}
