<?php

namespace App\Http\Controllers;

use App\AutomaticEmail;
use App\Contact;
use App\Customer;
use App\CustomerHour;
use App\Direction;
use App\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $mailing_code = isset($request->mailing_code) ? trim($request->mailing_code) : '';
        $mailing_code_number = 0;
        $mailing_old_code = isset($request->mailing_old_code) ? trim($request->mailing_old_code) : '';
        $mailing_name = isset($request->mailing_name) ? trim($request->mailing_name) : '';
        $mailing_address1 = isset($request->mailing_address1) ? trim($request->mailing_address1) : '';
        $mailing_address2 = isset($request->mailing_address2) ? trim($request->mailing_address2) : '';
        $mailing_city = isset($request->mailing_city) ? trim($request->mailing_city) : '';
        $mailing_state = isset($request->mailing_state) ? trim($request->mailing_state) : '';
        $mailing_zip = isset($request->mailing_zip) ? trim($request->mailing_zip) : '';
        $mailing_contact_name = isset($request->mailing_contact_name) ? trim($request->mailing_contact_name) : '';
        $mailing_contact_phone = isset($request->mailing_contact_phone) ? trim($request->mailing_contact_phone) : '';
        $mailing_contact_phone_ext = isset($request->mailing_contact_phone_ext) ? trim($request->mailing_contact_phone_ext) : '';
        $mailing_email = isset($request->mailing_email) ? trim($request->mailing_email) : '';
        $mailing_bill_to = isset($request->mailing_bill_to) ? trim($request->mailing_bill_to) : '';
        $mailing_division = isset($request->mailing_division) ? trim($request->mailing_division) : '';
        $mailing_agent_code = isset($request->mailing_agent_code) ? trim($request->mailing_agent_code) : '';
        $mailing_salesman = isset($request->mailing_salesman) ? trim($request->mailing_salesman) : '';
        $mailing_fid = isset($request->mailing_fid) ? trim($request->mailing_fid) : '';

        $curCustomer = Customer::where('id', $id)->first();

        error_log('1');
        if ($curCustomer){
            error_log('2');
            // si es el mismo codigo y numero
            if(($curCustomer->code . ($curCustomer->code_number === 0 ? "" : $curCustomer->code_number)) === $code){
                error_log('3');
                $code_number = $curCustomer->code_number;
            }else{
                error_log('4');
                // verificamos si hay otro registro con el mismo codigo
                $codeExist = Customer::where('id', '<>', $id)
                    ->where('code', $curCustomer->code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0){
                    error_log('5');
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }

            if ($mailing_code !== ''){
                error_log('6');
                if ($curCustomer->code === $mailing_code){
                    error_log('7');
                    $mailing_code_number = $curCustomer->code_number;
                }else{
                    error_log('8');
                    $mailing_codeExist = Customer::where('id', '<>', $id)
                        ->where('mailing_code', $mailing_code)
                        ->orderBy('mailing_code_number', 'asc')->get();

                    if (count($mailing_codeExist) > 0){
                        error_log('9');
                        $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                    }
                }
            }

        }else{
            error_log('10');
            // verificamos si existe un customer con el codigo
            $codeExist = Customer::where('id', '<>', $id)
                ->where('code', $code)
                ->orderBy('id', 'asc')->get();

            if (count($codeExist) > 0){
                error_log('11');
                $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || (trim($contact_phone) === '' && trim($email) === '')){
            $contact_name = '';
            $contact_phone = '';
            $contact_phone_ext = '';
            $email = '';
            $with_contact = false;
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
            'email' => $email,
            'mailing_code' => $mailing_code,
            'mailing_code_number' => $mailing_code_number,
            'mailing_name' => $mailing_name,
            'mailing_address1' => $mailing_address1,
            'mailing_address2' => $mailing_address2,
            'mailing_city' => $mailing_city,
            'mailing_state' => $mailing_state,
            'mailing_zip' => $mailing_zip,
            'mailing_contact_name' => $mailing_contact_name,
            'mailing_contact_phone' => $mailing_contact_phone,
            'mailing_ext' => $mailing_contact_phone_ext,
            'mailing_email' => $mailing_email,
            'mailing_bill_to' => $mailing_bill_to !== '' ? $mailing_code : '',
            'mailing_division' => $mailing_division,
            'mailing_agent_code' => $mailing_agent_code,
            'mailing_salesman' => $mailing_salesman,
            'mailing_fid' => $mailing_fid
        ]);

        if ($with_contact){
            $contacts = Contact::where('customer_id', $customer->id)->get();

            $contact_name_splitted = explode(" ", $contact_name);
            $contact_first = $contact_name_splitted[0];
            $contact_last = '';

            if (count($contact_name_splitted) > 0){
                for ($i = 1; $i < count($contact_name_splitted); $i++){
                    $contact_last .= $contact_name_splitted[$i] . " ";
                }
            }

            $contact_last = trim($contact_last);

            if (count($contacts) === 0){
                $contact = new Contact();
                $contact->customer_id = $customer->id;
                $contact->first_name = $contact_first;
                $contact->last_name = $contact_last;
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $contact_phone_ext;
                $contact->email_work = $email;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = $city;
                $contact->state = $state;
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->save();

                Customer::where('id', $customer->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            }
        }

        return response()->json(['result' => 'OK', 'customer' => $customer]);
    }

    public function getCustomerPayload(Request $request){
        $customer_id = $request->customer_id;

        $contacts = Contact::where('customer_id', $customer_id)->orderBy('last_name', 'asc')->get();
        $notes = Note::where('customer_id', $customer_id)->get();
        $directions = Direction::where('customer_id', $customer_id)->get();
        $customer_hours = CustomerHour::where('customer_id', $customer_id)->first();
        $automatic_emails = AutomaticEmail::where('customer_id', $customer_id)->first();

        return response()->json([
            'result' => 'OK',
            'contacts' => $contacts,
            'notes' => $notes,
            'directions' => $directions,
            'customer_hours' => $customer_hours,
            'automatic_emails' => $automatic_emails
        ]);
    }

    public function getFullCustomers(Request $request){
        $customers = Customer::with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes'])->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }
}
