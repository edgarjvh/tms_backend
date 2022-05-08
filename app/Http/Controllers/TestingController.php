<?php

namespace App\Http\Controllers;

use App\Models\FactoringCompany;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function testMaxValue(Request $request){
        $code = isset($request->code) ? $request->code : '';
        $code_number = isset($request->code_number) ? $request->code_number : 0;

        $maxValue = FactoringCompany::where('code', $code)->max('code_number');

        if (!$maxValue){
            $maxValue = 0;
        }

        return response()->json(['result' => 'OK', 'max_value' => $maxValue]);
    }

    public function testFactoringCompany(Request $request){
        $id = isset($request->id) ? $request->id : 0;
        $curFactoringCompany = FactoringCompany::where('id', $id)->first();
        $exist = false;

        if ($curFactoringCompany){
            $exist = true;
        }

        $type = gettype($id);
        return response()->json(['result' => 'OK', 'data' => $type]);
    }
}
