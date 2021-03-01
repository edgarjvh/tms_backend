<?php

namespace App\Http\Controllers;

use App\Carrier;
use App\FactoringCompany;
use Illuminate\Http\Request;

class FactoringCompaniesController extends Controller
{
    public function getFactoringCompanies(Request $request)
    {
        $code = isset($request->code) ? trim($request->code) : '';
        $name = isset($request->name) ? trim($request->name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $zip = isset($request->zip) ? trim($request->zip) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        $factoring_companies = FactoringCompany::whereRaw("1 = 1")
            ->whereRaw("code like '%$code%'")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("address1 like '%$address1%'")
            ->whereRaw("address2 like '%$address2%'")
            ->whereRaw("city like '%$city%'")
            ->whereRaw("state like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("email like '%$email%'")
            ->orderBy('code', 'ASC')
            ->orderBy('code_number', 'ASC')->get();

        return response()->json(['result' => 'OK', 'factoring_companies' => $factoring_companies]);
    }

    public function factoringCompanySearch(Request $request)
    {
        // error_log($request->search[0]['data']);
        $name = isset($request->search[0]['data']) ? trim($request->search[0]['data']) : '';
        $address1 = isset($request->search[1]['data']) ? trim($request->search[1]['data']) : '';
        $address2 = isset($request->search[2]['data']) ? trim($request->search[2]['data']) : '';
        $city = isset($request->search[3]['data']) ? trim($request->search[3]['data']) : '';
        $state = isset($request->search[4]['data']) ? trim($request->search[4]['data']) : '';
        $zip = isset($request->search[5]['data']) ? trim($request->search[5]['data']) : '';
        $email = isset($request->search[6]['data']) ? trim($request->search[6]['data']) : '';

        $factoring_companies = FactoringCompany::whereRaw("1 = 1")
            // ->whereRaw("code like '%$code%'")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->whereRaw("LOWER(address1) like '%$address1%'")
            ->whereRaw("LOWER(address2) like '%$address2%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("LOWER(email) like '%$email%'")
            ->orderBy('code', 'ASC')
            ->orderBy('code_number', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'factoring_companies' => $factoring_companies]);
    }

    public function saveCarrierFactoringCompany(Request $request){
        $id = isset($request->id) ? $request->id : 0;
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;
        $code = isset($request->code) ? trim($request->code) : '';
        $code_number = 0;
        $old_code = isset($request->old_code) ? trim($request->old_code) : '';
        $name = isset($request->name) ? trim($request->name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $zip = isset($request->zip) ? trim($request->zip) : '';
        $contact_name = isset($request->contact_name) ? trim($request->contact_name) : '';
        $contact_phone = isset($request->contact_phone) ? trim($request->contact_phone) : '';
        $ext = isset($request->ext) ? trim($request->ext) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        if ($carrier_id > 0){

            if ($id > 0 ){
                $curFactoringCompany = FactoringCompany::where('id', $id)->first();

                // si es el mismo codigo y numero
                if (($curFactoringCompany->code . ($curFactoringCompany->code_number === 0 ? "" : $curFactoringCompany->code_number)) === $code) {
                    $code_number = $curFactoringCompany->code_number;
                } else {
                    // verificamos si hay otro registro con el mismo codigo
                    $codeExist = FactoringCompany::where('id', '<>', $id)
                        ->where('code', $curFactoringCompany->code)
                        ->orderBy('id', 'asc')->get();

                    if (count($codeExist) > 0) {
                        $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                    }
                }
            }elseif ($id === 0){
                // verificamos si existe un carrier con el codigo
                $codeExist = FactoringCompany::where('carrier_id', '<>', $carrier_id)
                    ->where('code', $code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }

            $factoring_company = FactoringCompany::updateOrCreate([
                'carrier_id' => $carrier_id
            ],
                [
                    'code' => $code,
                    'code_number' => $code_number,
                    'name' => $name,
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'ext' => $ext,
                    'email' => $email
                ]);

            return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
        }
    }

    public function deleteCarrierFactoringCompany(Request $request){
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;

        $factoring_company = FactoringCompany::where('carrier_id', $carrier_id)->delete();

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }

    public function saveFactoringCompany(Request $request)
    {
        $id = isset($request->id) ? trim($request->id) : '';
        $code = isset($request->code) ? trim($request->code) : '';
        $code_number = 0;
        $old_code = isset($request->old_code) ? trim($request->old_code) : '';
        $name = isset($request->name) ? trim($request->name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $zip = isset($request->zip) ? trim($request->zip) : '';
        $contact_name = isset($request->contact_name) ? trim($request->contact_name) : '';
        $contact_phone = isset($request->contact_phone) ? trim($request->contact_phone) : '';
        $ext = isset($request->ext) ? trim($request->ext) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        $curFactoringCompany = FactoringCompany::where('id', $id)->first();

        if ($curFactoringCompany) {
            // si es el mismo codigo y numero
            if (($curFactoringCompany->code . ($curFactoringCompany->code_number === 0 ? "" : $curFactoringCompany->code_number)) === $code) {
                $code_number = $curFactoringCompany->code_number;
            } else {
                // verificamos si hay otro registro con el mismo codigo
                $codeExist = FactoringCompany::where('id', '<>', $id)
                    ->where('code', $curFactoringCompany->code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }
        } else {
            // verificamos si existe un carrier con el codigo
            $codeExist = FactoringCompany::where('id', '<>', $id)
                ->where('code', $code)
                ->orderBy('id', 'asc')->get();

            if (count($codeExist) > 0){
                $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
            }
        }

        $factoring_company = FactoringCompany::updateOrCreate([
            'id' => $id
        ],
            [
                'code' => $code,
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $ext,
                'email' => $email
            ]);

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }

    public function deleteFactoringCompany(Request $request){
        $id = isset($request->id) ? trim($request->id) : '';

        $factoring_company = FactoringCompany::where('id', $id)->delete();
        Carrier::where('factoring_company_id', $id)->update([
            'factoring_company_id' => null
        ]);

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }
}
