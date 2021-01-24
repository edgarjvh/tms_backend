<?php

namespace App\Http\Controllers;

use App\Insurance;
use App\InsuranceType;
use Illuminate\Http\Request;

class InsuranceTypesController extends Controller
{
    public function getInsuranceTypes(){
        $types = InsuranceType::whereRaw("1 = 1")->orderBy('name', 'ASC')->get();
        $companies = Insurance::orderBy('company', 'ASC')->get(['id', 'company']);
        return response()->json(['result' => 'OK', 'types' => $types, 'companies' => $companies]);
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
