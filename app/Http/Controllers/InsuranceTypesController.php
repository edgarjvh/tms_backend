<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use App\Models\InsuranceType;
use Illuminate\Http\Request;

class InsuranceTypesController extends Controller
{
    public function getInsuranceTypes(Request $request){
        $name = isset($request->name) ? trim($request->name) : '';

        $types = InsuranceType::whereRaw("1 = 1")
            ->whereRaw("name like '$name%'")
            ->orderBy('name')
            ->get();

        $companies = Insurance::orderBy('company')->get(['id', 'company']);
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
