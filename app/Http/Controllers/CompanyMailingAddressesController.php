<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\CompanyMailingAddress;

class CompanyMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyMailingAddress(Request $request) :JsonResponse
    {
        $COMPANY_MAILING_ADDRESS = new CompanyMailingAddress();

        $company_id = $request->company_id ?? 0;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $main_phone_number = $request->main_phone_number ?? '';
        $main_fax_number = $request->main_fax_number ?? '';
        $website = $request->website ?? '';

        if ($company_id > 0){
            $curMailingAddress = $COMPANY_MAILING_ADDRESS->where('company_id', $company_id)->first();

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if ($curMailingAddress->code !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $codeExist = $COMPANY_MAILING_ADDRESS->where('company_id', '<>', $company_id)
                        ->where('code', $code)->get();

                    if (count($codeExist) > 0) {
                        $max_code_number = $COMPANY_MAILING_ADDRESS->where('code', $code)->max('code_number');
                        $code_number = $max_code_number + 1;
                    }
                }
            } else {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $codeExist = $COMPANY_MAILING_ADDRESS->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $COMPANY_MAILING_ADDRESS->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                }
            }

            $COMPANY_MAILING_ADDRESS->updateOrCreate([
                'company_id' => $company_id
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
                    'main_phone_number' => $main_phone_number,
                    'main_fax_number' => $main_fax_number,
                    'website' => $website,
                ]);

            $newMailingAddress = $COMPANY_MAILING_ADDRESS->where('company_id', $company_id)->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO COMPANY']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyMailingAddress(Request $request) : JsonResponse
    {
        $COMPANY_MAILING_ADDRESS = new CompanyMailingAddress();

        $company_id = $request->company_id ?? 0;

        $mailing_address = $COMPANY_MAILING_ADDRESS->where('company_id', $company_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
