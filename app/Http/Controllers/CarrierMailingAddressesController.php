<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
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
        $CARRIER = new Carrier();

        $carrier_id = $request->carrier_id ?? 0;
        $id = $request->id ?? 0;
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
            $curMailingAddress = $CARRIER_MAILING_ADDRESS->where('id', $id)->first();
            $code = strtolower($code);

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if (($curMailingAddress->code . ($curMailingAddress->code_number === 0 ? '' : $curMailingAddress->code_number)) !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $carriers = $CARRIER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                        ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("carrier") as type')
                        ->orderBy('code')
                        ->orderBy('code_number')
                        ->get();
                    $mailing_addresses = $CARRIER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                        ->where('id', '<>', $id)
                        ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
                        ->orderBy('code')
                        ->orderBy('code_number')
                        ->get();

                    $collection = $carriers->merge($mailing_addresses)->toArray();

                    usort($collection, function ($a, $b) {
                        if ($a['code'] == $b['code']){
                            return $a['code_number'] - $b['code_number'];
                        }

                        return strcmp(strtolower($a['code']), strtolower($b['code']));
                    });

                    if (count($collection) > 0) {
                        $max_code_number = $collection[count($collection) - 1]['code_number'];
                        $code_number = $max_code_number + 1;
                    }else{
                        $code_number = 0;
                    }
                }
            } else {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $carriers = $CARRIER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                    ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("customer") as type')
                    ->orderBy('code')
                    ->orderBy('code_number')
                    ->get();
                $mailing_addresses = $CARRIER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                    ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
                    ->orderBy('code')
                    ->orderBy('code_number')
                    ->get();

                $collection = $carriers->merge($mailing_addresses)->toArray();

                usort($collection, function ($a, $b) {
                    if ($a['code'] == $b['code']){
                        return $a['code_number'] - $b['code_number'];
                    }

                    return strcmp(strtolower($a['code']), strtolower($b['code']));
                });

                if (count($collection) > 0) {
                    $max_code_number = $collection[count($collection) - 1]['code_number'];
                    $code_number = $max_code_number + 1;
                }else{
                    $code_number = 0;
                }
            }

            $mailing = $CARRIER_MAILING_ADDRESS->updateOrCreate([
                'carrier_id' => $carrier_id
            ],
                [
                    'code' => strtoupper($code),
                    'code_number' => $code_number,
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
                    'mailing_contact_primary_email' => $mailing_contact_primary_email,
                ]);

            $newMailingAddress = $CARRIER_MAILING_ADDRESS->where('id', $mailing->id)->with(['mailing_contact'])->first();

            $CARRIER->updateOrCreate([
                'id' => $carrier_id
            ], [
                'mailing_address_id' => $mailing->id
            ]);

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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierMailingAddressByCode(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();
        $CARRIER_MAILING_ADDRESS = new CarrierMailingAddress();
        $code = strtolower($request->code ?? '');


        $carriers = $CARRIER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
            ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("carrier") as type')
            ->with(['contacts'])
            ->orderBy('code')
            ->orderBy('code_number')
            ->get();
        $mailing_addresses = $CARRIER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
            ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
            ->orderBy('code')
            ->orderBy('code_number')
            ->get();

        $collection = $carriers->merge($mailing_addresses)->toArray();

        usort($collection, function ($a, $b) {
            if ($a['code'] == $b['code']){
                return $a['code_number'] - $b['code_number'];
            }

            return strcmp(strtolower($a['code']), strtolower($b['code']));
        });

        return response()->json(['result' => 'OK', 'mailing_address' => $collection]);
    }
}
