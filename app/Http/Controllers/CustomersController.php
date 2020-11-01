<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function customers(Request $json)
    {
        $code = isset($json->code) ? trim($json->code) : '';
        $name = isset($json->name) ? trim($json->name) : '';
        $city = isset($json->city) ? trim($json->city) : '';
        $state = isset($json->state) ? trim($json->state) : '';
        $zip = isset($json->zip) ? trim($json->zip) : '';
        $contact_name = isset($json->contact_name) ? trim($json->contact_name) : '';
        $contact_phone = isset($json->contact_phone) ? trim($json->contact_phone) : '';
        $email = isset($json->email) ? trim($json->email) : '';

        $customers = Customer::whereRaw("1 = 1")
            ->whereRaw("code like '%$code%'")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("city like '%$city%'")
            ->whereRaw("state like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("contact_name like '%$contact_name%'")
            ->whereRaw("contact_phone like '%$contact_phone%'")
            ->whereRaw("email like '%$email%'")
            ->orderBy('code', 'ASC')
            ->orderBy('code_number', 'ASC')->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    public function saveCustomer(Request $request){
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
        $contact_phone_ext = isset($request->contact_phone_ext) ? trim($request->contact_phone_ext) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        $curCustomer = Customer::where('id', $id)->first();

        if ($curCustomer){
            if($curCustomer->code === $code){
                $code = $curCustomer->code;
                $code_number = $curCustomer->code_number;
            }else{
                $codeExist = Customer::where('id', '<>', $id)
                    ->where('code', $code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0){
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }
        }else{
            $codeExist = Customer::where('id', '!=', $id)
                ->where('code', $code)
                ->orderBy('id', 'asc')->get();

            if (count($codeExist) > 0){
                $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
            }
        }

        $customer = Customer::updateOrCreate([
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
            'ext' => $contact_phone_ext,
            'email' => $email
        ]);

        return response()->json(['result' => 'OK', 'customer' => $customer]);
    }
}
