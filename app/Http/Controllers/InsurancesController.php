<?php

namespace App\Http\Controllers;

use App\Insurance;
use Illuminate\Http\Request;

class InsurancesController extends Controller
{
    public function getInsurances(){
        $insurances = Insurance::orderBy('company', 'ASC')->get();
        return response()->json(['result' => 'OK', 'insurances' => $insurances]);
    }

    public function saveInsurance(Request $request){
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;
        $insurance_id = isset($request->insurance_id) ? $request->insurance_id : 0;
        $insurance_type_id = isset($request->insurance_type_id) ? $request->insurance_type_id : 0;
        $company = isset($request->company) ? $request->company : '';
        $expiration_date = isset($request->expiration_date) ? $request->expiration_date : '';
        $amount = isset($request->amount) ? $request->amount : '';
        $deductible = isset($request->deductible) ? $request->deductible : '';
        $notes = isset($request->notes) ? $request->notes : '';

        $insurance = Insurance::updateOrCreate([
            'id' => $insurance_id
        ], [
            'carrier_id' => $carrier_id,
            'insurance_type_id' => $insurance_type_id,
            'company' => $company,
            'expiration_date' => $expiration_date,
            'amount' => $amount,
            'deductible' => $deductible,
            'notes' => $notes,
        ]);

        $insurances = Insurance::where('carrier_id', $carrier_id)->with('insuranceType')->get();
        $companies = Insurance::orderBy('company', 'ASC')->get(['id', 'company']);

        return response()->json(['result' => 'OK', 'insurance' => $insurance, 'insurances' => $insurances, 'companies' => $companies]);
    }

    public function deleteInsurance(Request $request){
        $id = $request->id;

        $insurance = Insurance::where('id', $id)->delete();

        return response()->json(['result' => 'OK', 'insurance' => $insurance]);
    }
}
