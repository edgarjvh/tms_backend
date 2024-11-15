<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use App\Models\InsuranceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceTypesController extends Controller
{
    public function getInsuranceTypes(Request $request){
        $name = strtolower($request->name ?? '');

        $types = InsuranceType::whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')
            ->get();

        $companies = Insurance::orderBy('company')->get(['id', 'company']);
        return response()->json(['result' => 'OK', 'types' => $types, 'companies' => $companies]);
    }

    public function getInsuranceCompanies(Request $request) : JsonResponse
    {
        $company = $request->company ?? '';

        $companies = Insurance::whereRaw("LOWER(company) like '$company%'")
        ->orderBy('company')
        ->get(['id', 'company']);
        return response()->json(['result' => 'OK', 'companies' => $companies]);
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
