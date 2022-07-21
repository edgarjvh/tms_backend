<?php

namespace App\Http\Controllers;

use App\Models\CustomerMailingAddress;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerMailingAddress(Request $request) :JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();

        $customer_id = $request->customer_id ?? 0;
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
        $agent_code = $request->agent_code ?? '';
        $salesman = $request->salesman ?? '';
        $fid = $request->fid ?? '';
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';
        $division_id = ($request->division_id ?? 0) === 0 ? null : $request->division_id;

        if ($customer_id > 0){
            $curMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('customer_id', $customer_id)->first();

            if ($curMailingAddress) {
                // si no es el mismo codigo
                if ($curMailingAddress->code !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $codeExist = $CUSTOMER_MAILING_ADDRESS->where('customer_id', '<>', $customer_id)
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
                'customer_id' => $customer_id
            ],
                [
                    'code' => $code,
                    'code_number' => $code === '' ? 0 : $code_number,
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
                    'bill_to_code' => $bill_to_code,
                    'bill_to_code_number' => $bill_to_code_number,
                    'agent_code' => $agent_code,
                    'salesman' => $salesman,
                    'fid' => $fid,
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email,
                    'division_id' => $division_id
                ]);

            $newMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('customer_id', $customer_id)->with(['mailing_contact', 'division'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerMailingAddress(Request $request) : JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();

        $customer_id = $request->customer_id ?? 0;

        $mailing_address = $CUSTOMER_MAILING_ADDRESS->where('customer_id', $customer_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
