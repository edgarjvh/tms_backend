<?php

namespace App\Http\Controllers;

use App\CarrierMailingAddress;
use Illuminate\Http\Request;

class CarrierMailingAddressesController extends Controller
{
    public function saveCarrierMailingAddress(Request $request){
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
        $mailing_contact_id = isset($request->mailing_contact_id) ? trim($request->mailing_contact_id) : 0;
        $mailing_contact_primary_phone = isset($request->mailing_contact_primary_phone) ? trim($request->mailing_contact_primary_phone) : 'work';
        $mailing_contact_primary_email = isset($request->mailing_contact_primary_email) ? trim($request->mailing_contact_primary_email) : 'work';

        if ($carrier_id > 0){

            if ($id > 0 ){
                $curMailingAddress = CarrierMailingAddress::where('id', $id)->first();

                // si es el mismo codigo y numero
                if (($curMailingAddress->code . ($curMailingAddress->code_number === 0 ? "" : $curMailingAddress->code_number)) === $code) {
                    $code_number = $curMailingAddress->code_number;
                } else {
                    // verificamos si hay otro registro con el mismo codigo
                    $codeExist = CarrierMailingAddress::where('id', '<>', $id)
                        ->where('code', $curMailingAddress->code)
                        ->orderBy('id', 'asc')->get();

                    if (count($codeExist) > 0) {
                        $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                    }
                }
            }elseif ($id === 0){
                // verificamos si existe un carrier con el codigo
                $codeExist = CarrierMailingAddress::where('carrier_id', '<>', $carrier_id)
                    ->where('code', $code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }

            $mailingAddress = CarrierMailingAddress::updateOrCreate([
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

            $newMailingAddress = CarrierMailingAddress::where('carrier_id', $carrier_id)->with(['mailing_contact'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }        
    }

    public function deleteCarrierMailingAddress(Request $request){
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;

        $mailing_address = CarrierMailingAddress::where('carrier_id', $carrier_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
