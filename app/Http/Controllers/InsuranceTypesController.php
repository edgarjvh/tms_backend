<?php

namespace App\Http\Controllers;

use App\InsuranceType;
use Illuminate\Http\Request;

class InsuranceTypesController extends Controller
{
    public function getInsuranceTypes(){
        $types = InsuranceType::whereRaw("1 = 1")->get();

        return response()->json(['result' => 'OK', 'types' => $types]);
    }

    public function saveInsuranceType(Request $request){
        $id = $request->id;
        $name = $request->name;

        $type = InsuranceType::updateOrCreate([
            'id' => $id
        ], [
            'name' => $name
        ]);

        return response()->json(['result' => 'OK', 'type' => $type]);
    }

    public function deleteInsuranceType(Request $request){
        $id = $request->id;

        $type = InsuranceType::where('id', $id)->delete();

        return response()->json(['result' => 'OK', 'type' => $type]);
    }
}
