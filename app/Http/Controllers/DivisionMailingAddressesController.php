<?php

namespace App\Http\Controllers;

use App\Models\DivisionMailingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionMailingAddress(Request $request) :JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new DivisionMailingAddress();

        $division_id = $request->division_id ?? 0;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $ext = $request->ext ?? '';
        $email = $request->email ?? '';
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';

        if ($division_id > 0){
            $curMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('division_id', $division_id)->first();

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if ($curMailingAddress->code !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $codeExist = $CUSTOMER_MAILING_ADDRESS->where('division_id', '<>', $division_id)
                        ->where('code', $code)->get();

                    if (count($codeExist) > 0) {
                        $max_code_number = $CUSTOMER_MAILING_ADDRESS->where('code', $code)->max('code_number');
                        $code_number = $max_code_number + 1;
                    }
                }
            } else {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $codeExist = $CUSTOMER_MAILING_ADDRESS->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $CUSTOMER_MAILING_ADDRESS->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                }
            }

            $CUSTOMER_MAILING_ADDRESS->updateOrCreate([
                'division_id' => $division_id
            ],
                [
                    'code' => strtoupper($code),
                    'code_number' => $code === '' ? 0 : $code_number,
                    'name' => ucwords($name),
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => ucwords($city),
                    'state' => strtoupper($state),
                    'zip' => $zip,
                    'contact_name' => ucwords($contact_name),
                    'contact_phone' => $contact_phone,
                    'ext' => $ext,
                    'email' => strtolower($email),
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email
                ]);

            $newMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('division_id', $division_id)->with(['mailing_contact', 'division'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO DIVISION']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDivisionMailingAddress(Request $request) : JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new DivisionMailingAddress();

        $division_id = $request->division_id ?? 0;

        $mailing_address = $CUSTOMER_MAILING_ADDRESS->where('division_id', $division_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
