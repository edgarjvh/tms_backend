<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsurancesController extends Controller
{
    public function getInsurances()
    {
        $insurances = Insurance::orderBy('company')->with('insurance_type')->get();
        return response()->json(['result' => 'OK', 'insurances' => $insurances]);
    }

    public function saveInsurance(Request $request)
    {
        $id = $request->id ?? null;
        $carrier_id = $request->carrier_id ?? null;
        $insurance_type_id = $request->insurance_type_id ?? null;
        $company = $request->company ?? '';
        $expiration_date = $request->expiration_date ?? '';
        $amount = $request->amount ?? '';
        $deductible = $request->deductible ?? '';
        $notes = $request->notes ?? '';

        $INSURANCE = new Insurance();
        $CARRIER = new Carrier();

        $current_insurance = $INSURANCE->where('id', $id)->first();
        $current_carrier = $CARRIER->where('id', $carrier_id)->first();

        if ($current_insurance) {
            if ($current_insurance->expiration_date !== $expiration_date) {
                if ($current_carrier->insurance_flag === 0) {
                    $CARRIER->where('id', $carrier_id)->update(['insurance_flag' => 1]);
                } else if ($current_carrier->insurance_flag === 1) {
                    $CARRIER->where('id', $carrier_id)->update(['insurance_flag' => 0]);
                }
            }
        }

        if ($carrier_id > 0) {
            $insurance = $INSURANCE->updateOrCreate([
                'id' => $id
            ], [
                'carrier_id' => $carrier_id,
                'insurance_type_id' => $insurance_type_id,
                'company' => $company,
                'expiration_date' => $expiration_date,
                'amount' => $amount,
                'deductible' => $deductible,
                'notes' => $notes,
            ]);

            $insurance = $INSURANCE->where('id', $insurance->id)->with('insurance_type')->first();
            $insurances = $INSURANCE->where('carrier_id', $carrier_id)->with('insurance_type')->get();
            $companies = $INSURANCE->orderBy('company')->get(['id', 'company']);

            return response()->json(['result' => 'OK', 'insurance' => $insurance, 'insurances' => $insurances, 'companies' => $companies]);
        } else {
            return response()->json(['result' => 'NO CARRIER']);
        }
    }

    public function deleteInsurance(Request $request)
    {
        $id = $request->id;

        $insurance = Insurance::where('id', $id)->delete();

        return response()->json(['result' => 'OK', 'insurance' => $insurance]);
    }

    public function getInsuranceCompanies(Request $request)
    {
        $company = isset($request->company) ? $request->company : '';

        $companies = DB::table('carrier_insurances')
            ->select('company')
            ->whereRaw("1 = 1")
            ->whereRaw("LOWER(company) LIKE '%$company%'")
            ->groupBy('company')
            ->orderBy('company')
            ->get();

        return response()->json(['result' => 'OK', 'companies' => $companies]);
    }
}
