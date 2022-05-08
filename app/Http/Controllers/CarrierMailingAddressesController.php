<?php

namespace App\Http\Controllers;

use App\Models\CarrierMailingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CarrierMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierMailingAddress(Request $request) : JsonResponse
    {
        $CARRIER_MAILING_ADDRESS = new CarrierMailingAddress();

        $carrier_id = $request->carrier_id ?? 0;
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

        if ($carrier_id > 0){
            $curMailingAddress = $CARRIER_MAILING_ADDRESS->where('carrier_id', $carrier_id)->first();

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if ($curMailingAddress->code !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $codeExist = $CARRIER_MAILING_ADDRESS->where('carrier_id', '<>', $carrier_id)
                        ->where('code', $code)->get();

                    if (count($codeExist) > 0) {
                        $max_code_number = $CARRIER_MAILING_ADDRESS->where('code', $code)->max('code_number');
                        $code_number = $max_code_number + 1;
                    }
                }
            } else {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $codeExist = $CARRIER_MAILING_ADDRESS->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $CARRIER_MAILING_ADDRESS->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                }
            }

            $CARRIER_MAILING_ADDRESS->updateOrCreate([
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
                    'email' => $email,
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email,
                ]);

            $newMailingAddress = $CARRIER_MAILING_ADDRESS->where('carrier_id', $carrier_id)->with(['mailing_contact'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO CARRIER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierMailingAddress(Request $request) : JsonResponse
    {
        $CARRIER_MAILING_ADDRESS = new CarrierMailingAddress();

        $carrier_id = $request->carrier_id ?? 0;

        $mailing_address = $CARRIER_MAILING_ADDRESS->where('carrier_id', $carrier_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
