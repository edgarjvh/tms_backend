<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerMailingAddress;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerMailingAddress(Request $request): JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();
        $CUSTOMER = new Customer();

        $customer_id = $request->customer_id ?? 0;
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
        $bill_to_code = $request->bill_to_code ?? null;
        $bill_to_code_number = $request->bill_to_code_number ?? null;
        $division = $request->division ?? '';
        $agent_code = strtoupper($request->agent_code ?? '');
        $salesman = $request->salesman ?? '';
        $fid = $request->fid ?? '';
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';
        $division_id = ($request->division_id ?? 0) === 0 ? null : $request->division_id;

        if ($customer_id > 0) {
            $curMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('id', $id)->first();
            $code = strtolower($code);

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if (($curMailingAddress->code . ($curMailingAddress->code_number === 0 ? '' : $curMailingAddress->code_number)) !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number

                    $customers = $CUSTOMER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                        ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("customer") as type')
                        ->orderBy('code')
                        ->orderBy('code_number')
                        ->get();
                    $mailing_addresses = $CUSTOMER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                        ->where('id', '<>', $id)
                        ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
                        ->orderBy('code')
                        ->orderBy('code_number')
                        ->get();

                    $collection = $customers->merge($mailing_addresses)->toArray();

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
                $customers = $CUSTOMER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                    ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("customer") as type')
                    ->orderBy('code')
                    ->orderBy('code_number')
                    ->get();
                $mailing_addresses = $CUSTOMER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
                    ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
                    ->orderBy('code')
                    ->orderBy('code_number')
                    ->get();

                $collection = $customers->merge($mailing_addresses)->toArray();

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

            $mailing = $CUSTOMER_MAILING_ADDRESS->updateOrCreate([
                'id' => $id
            ],
                [
                    'customer_id' => $customer_id,
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
                    'bill_to_code' => strtoupper($bill_to_code),
                    'bill_to_code_number' => $bill_to_code_number,
                    'agent_code' => strtoupper($agent_code),
                    'salesman' => $salesman,
                    'fid' => $fid,
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email,
                    'division_id' => $division_id
                ]);

            $newMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('id', $mailing->id)->with(['mailing_contact', 'division'])->first();

            $CUSTOMER->updateOrCreate([
                'id' => $customer_id
            ], [
                'mailing_address_id' => $mailing->id
            ]);

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        } else {
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerMailingAddress(Request $request): JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();

        $customer_id = $request->customer_id ?? 0;

        $mailing_address = $CUSTOMER_MAILING_ADDRESS->where('customer_id', $customer_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerMailingAddressByCode(Request $request): JsonResponse
    {
        $CUSTOMER = new Customer();
        $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();
        $code = strtolower($request->code ?? '');


        $customers = $CUSTOMER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
            ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("customer") as type')
            ->orderBy('code')
            ->orderBy('code_number')
            ->get();
        $mailing_addresses = $CUSTOMER_MAILING_ADDRESS->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
            ->selectRaw('id,code,code_number,name,address1,address2,city,state,zip,contact_name,contact_phone,ext,email,concat("mailing") as type')
            ->orderBy('code')
            ->orderBy('code_number')
            ->get();

        $collection = $customers->merge($mailing_addresses)->toArray();

        usort($collection, function ($a, $b) {
            if ($a['code'] == $b['code']){
                return $a['code_number'] - $b['code_number'];
            }

            return strcmp(strtolower($a['code']), strtolower($b['code']));
        });

        return response()->json(['result' => 'OK', 'mailing_address' => $collection]);
    }
}
