<?php

namespace App\Http\Controllers;

use App\Models\Mileage;
use Illuminate\Http\Request;

class MileagesController extends Controller
{
    public function getMileage(Request $request){
        $point1 = isset($request->point1) ? $request->point1 : '';
        $point2 = isset($request->point2) ? $request->point2 : '';

        if ($point1 === '' && $point2 === ''){
            return response()->json(['result' => 'NOT VALID']);
        }else{
            $mileage1 = Mileage::where(['point1' => $point1, 'point2' => $point2])->first();

            if ($mileage1){
                return response()->json(['result' => 'OK', 'mileage' => $mileage1]);
            }else{
                $mileage2 = Mileage::where(['point1' => $point2, 'point2' => $point1])->first();

                if ($mileage2){
                    return response()->json(['result' => 'OK', 'mileage' => $mileage2]);
                }else{
                    return response()->json(['result' => 'NOT FOUND']);
                }
            }
        }
    }

    public function saveMileage(Request $request){
        $point1 = isset($request->point1) ? $request->point1 : '';
        $point2 = isset($request->point2) ? $request->point2 : '';
        $distance = isset($request->distance) ? $request->distance : 0;

        if ($point1 === '' && $point2 === ''){
            return response()->json(['result' => 'NOT VALID']);
        }else{
            $mileage = new Mileage();
            $mileage->point1 = $point1;
            $mileage->point2 = $point2;
            $mileage->distance = $distance;
            $mileage->save();

            return response()->json(['result' => 'OK', 'mileage' => $mileage]);
        }
    }
}
