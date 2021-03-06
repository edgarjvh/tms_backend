<?php

namespace App\Http\Controllers;

use App\Carrier;
use App\Contact;
use App\CarrierContact;
use App\Customer;
use App\FactoringCompany;
use App\FactoringCompanyContact;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function getContacts(Request $request): \Illuminate\Http\JsonResponse
    {

        $customer_id = isset($request->customer_id) ? trim($request->customer_id) : 0;
        $first_name = isset($request->first_name) ? trim($request->first_name) : '';
        $last_name = isset($request->last_name) ? trim($request->last_name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $phone = isset($request->phone) ? trim($request->phone) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        if ($customer_id == 0){
            $contacts = Contact::whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('customer')
                ->has('customer')
                ->get();
        }else{
            $contacts = Contact::whereRaw("1 = 1")
                ->whereRaw("customer_id = $customer_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('customer')
                ->has('customer')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function customerContactsSearch(Request $request): \Illuminate\Http\JsonResponse
    {

        $customer_id = isset($request->search[0]['data']) ? trim($request->search[0]['data']) : 0;
        $first_name = isset($request->search[1]['data']) ? trim($request->search[1]['data']) : '';
        $last_name = isset($request->search[2]['data']) ? trim($request->search[2]['data']) : '';
        $address1 = isset($request->search[3]['data']) ? trim($request->search[3]['data']) : '';
        $address2 = isset($request->search[4]['data']) ? trim($request->search[4]['data']) : '';
        $city = isset($request->search[5]['data']) ? trim($request->search[5]['data']) : '';
        $state = isset($request->search[6]['data']) ? trim($request->search[6]['data']) : '';
        $phone = isset($request->search[7]['data']) ? trim($request->search[7]['data']) : '';
        $email = isset($request->search[8]['data']) ? trim($request->search[8]['data']) : '';

        if ($customer_id == 0){
            $contacts = Contact::whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('customer')
                ->has('customer')
                ->get();
        }else{
            $contacts = Contact::whereRaw("1 = 1")
                ->whereRaw("customer_id = $customer_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('customer')
                ->has('customer')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function carrierContactsSearch(Request $request){

        $carrier_id = isset($request->search[0]['data']) ? trim($request->search[0]['data']) : 0;
        $first_name = isset($request->search[1]['data']) ? trim($request->search[1]['data']) : '';
        $last_name = isset($request->search[2]['data']) ? trim($request->search[2]['data']) : '';
        $address1 = isset($request->search[3]['data']) ? trim($request->search[3]['data']) : '';
        $address2 = isset($request->search[4]['data']) ? trim($request->search[4]['data']) : '';
        $city = isset($request->search[5]['data']) ? trim($request->search[5]['data']) : '';
        $state = isset($request->search[6]['data']) ? trim($request->search[6]['data']) : '';
        $phone = isset($request->search[7]['data']) ? trim($request->search[7]['data']) : '';
        $email = isset($request->search[8]['data']) ? trim($request->search[8]['data']) : '';

        if ($carrier_id == 0){
            $contacts = CarrierContact::whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('carrier')
                ->has('carrier')
                ->get();
        }else{
            $contacts = CarrierContact::whereRaw("1 = 1")
                ->whereRaw("carrier_id = $carrier_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('carrier')
                ->has('carrier')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function getContactsByEmail(Request $request){
        $email = isset($request->email) ? trim($request->email) : '';

        $contacts = Contact::whereRaw("1 = 1")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function getContactsByEmailOrName(Request $request){
        $customer_id = isset($request->customer_id) ? trim($request->customer_id) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        error_log("customer_id = $customer_id");

        $contacts = Contact::whereRaw("((LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%') OR (LOWER(first_name) like '%$email%' or LOWER(last_name) like '%$email%'))")
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function contacts(Request $request)
    {
        $customer_id = isset($request->customer_id) ? trim($request->customer_id) : null;

        $contacts = Contact::whereRaw("1 = 1")
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function getContactById(Request $request)
    {
        $contact_id = $request->contact_id;
        $customer_id = $request->customer_id;

        $contact = Contact::where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->first();
        $contacts = Contact::where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function getContactsByCustomerId(Request $request)
    {
        $customer_id = $request->customer_id;
        $contacts = Contact::where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();
        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'contact' => null]);
    }

    public function saveContact(Request $request)
    {
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        $curContact = Contact::where('id', $contact_id)->first();
        $customer_id = $request->customer_id;

        $customer = Customer::where('id', $customer_id)->first();

        $prefix = isset($request->prefix) ? $request->prefix : ($curContact ? $curContact->prefix : '');
        $first_name = isset($request->first_name) ? $request->first_name : ($curContact ? $curContact->first_name : '');
        $middle_name = isset($request->middle_name) ? $request->middle_name : ($curContact ? $curContact->middle_name : '');
        $last_name = isset($request->last_name) ? $request->last_name : ($curContact ? $curContact->last_name : '');
        $suffix = isset($request->suffix) ? $request->suffix : ($curContact ? $curContact->suffix : '');
        $title = isset($request->title) ? $request->title : ($curContact ? $curContact->title : '');
        $department = isset($request->department) ? $request->department : ($curContact ? $curContact->department : '');
        $email_work = isset($request->email_work) ? $request->email_work : ($curContact ? $curContact->email_work : '');
        $email_personal = isset($request->email_personal) ? $request->email_personal : ($curContact ? $curContact->email_personal : '');
        $email_other = isset($request->email_other) ? $request->email_other : ($curContact ? $curContact->email_other : '');
        $primary_email = isset($request->primary_email) ? $request->primary_email : ($curContact ? $curContact->primary_email : 'work');
        $phone_work = isset($request->phone_work) ? $request->phone_work : ($curContact ? $curContact->phone_work : '');
        $phone_work_fax = isset($request->phone_work_fax) ? $request->phone_work_fax : ($curContact ? $curContact->phone_work_fax : '');
        $phone_mobile = isset($request->phone_mobile) ? $request->phone_mobile : ($curContact ? $curContact->phone_mobile : '');
        $phone_direct = isset($request->phone_direct) ? $request->phone_direct : ($curContact ? $curContact->phone_direct : '');
        $phone_other = isset($request->phone_other) ? $request->phone_other : ($curContact ? $curContact->phone_other : '');
        $primary_phone = isset($request->primary_phone) ? $request->primary_phone : ($curContact ? $curContact->primary_phone : 'work');
        $phone_ext = isset($request->phone_ext) ? $request->phone_ext : ($curContact ? $curContact->phone_ext : '');
        $country = isset($request->country) ? $request->country : ($curContact ? $curContact->country : '');
        $address1 = isset($request->address1) ? $request->address1 : ($curContact ? $curContact->address1 : $customer->address1);
        $address2 = isset($request->address2) ? $request->address2 : ($curContact ? $curContact->address2 : $customer->address2);
        $city = isset($request->city) ? $request->city : ($curContact ? $curContact->city : $customer->city);
        $state = isset($request->state) ? $request->state : ($curContact ? $curContact->state : $customer->state);
        $zip_code = isset($request->zip_code) ? $request->zip_code : ($curContact ? $curContact->zip_code : $customer->zip);
        $birthday = isset($request->birthday) ? $request->birthday : ($curContact ? $curContact->birthday : '');
        $website = isset($request->website) ? $request->website : ($curContact ? $curContact->website : '');
        $notes = isset($request->notes) ? $request->notes : ($curContact ? $curContact->notes : '');
        $automatic_emails_to = isset($request->automatic_emails_to) ? $request->automatic_emails_to : ($curContact ? $curContact->automatic_emails_to : '');
        $automatic_emails_cc = isset($request->automatic_emails_cc) ? $request->automatic_emails_cc : ($curContact ? $curContact->automatic_emails_cc : '');
        $automatic_emails_bcc = isset($request->automatic_emails_bcc) ? $request->automatic_emails_bcc : ($curContact ? $curContact->automatic_emails_bcc : '');
        $is_primary = isset($request->is_primary) ? $request->is_primary : ($curContact ? $curContact->is_primary : 0);
        $is_online = isset($request->is_online) ? $request->is_online : ($curContact ? $curContact->is_online : 0);
        $automatic_emails_booked_load = isset($request->automatic_emails_booked_load) ? $request->automatic_emails_booked_load : ($curContact ? $curContact->automatic_emails_booked_load : 0);
        $automatic_emails_check_calls = isset($request->automatic_emails_check_calls) ? $request->automatic_emails_check_calls : ($curContact ? $curContact->automatic_emails_check_calls : 0);
        $automatic_emails_carrier_arrival_shipper = isset($request->automatic_emails_carrier_arrival_shipper) ? $request->automatic_emails_carrier_arrival_shipper : ($curContact ? $curContact->automatic_emails_carrier_arrival_shipper : 0);
        $automatic_emails_carrier_arrival_consignee = isset($request->automatic_emails_carrier_arrival_consignee) ? $request->automatic_emails_carrier_arrival_consignee : ($curContact ? $curContact->automatic_emails_carrier_arrival_consignee : 0);
        $automatic_emails_loaded = isset($request->automatic_emails_loaded) ? $request->automatic_emails_loaded : ($curContact ? $curContact->automatic_emails_loaded : 0);
        $automatic_emails_empty = isset($request->automatic_emails_empty) ? $request->automatic_emails_empty : ($curContact ? $curContact->automatic_emails_empty : 0);

        $is_primary = (int) $is_primary;





        $contact = Contact::updateOrCreate([
            'id' => $contact_id
        ],
            [
                'customer_id' => $customer_id,
                'prefix' => $prefix,
                'first_name' => trim($first_name),
                'middle_name' => trim($middle_name),
                'last_name' => trim($last_name),
                'suffix' => $suffix,
                'title' => $title,
                'department' => $department,
                'email_work' => $email_work,
                'email_personal' => $email_personal,
                'email_other' => $email_other,
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => $country,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => $website,
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
                'automatic_emails_to' => $automatic_emails_to,
                'automatic_emails_cc' => $automatic_emails_cc,
                'automatic_emails_bcc' => $automatic_emails_bcc,
                'automatic_emails_booked_load' => $automatic_emails_booked_load,
                'automatic_emails_check_calls' => $automatic_emails_check_calls,
                'automatic_emails_carrier_arrival_shipper' => $automatic_emails_carrier_arrival_shipper,
                'automatic_emails_carrier_arrival_consignee' => $automatic_emails_carrier_arrival_consignee,
                'automatic_emails_loaded' => $automatic_emails_loaded,
                'automatic_emails_empty' => $automatic_emails_empty
            ]);

        if ($is_primary === 1){
            Customer::where('id', $customer_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        }

        $newContact = Contact::where('id', $contact->id)
            ->with('customer')
            ->has('customer')
            ->first();

        $contacts = Contact::where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    public function uploadAvatar(Request $request)
    {
        $contact_id = $_POST['contact_id'];
        $customer_id = $request->customer_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = Contact::where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar){
            unlink(public_path('avatars/' . $cur_avatar));
        }

        Contact::where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = Contact::where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->first();

        $contacts = Contact::where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function removeAvatar(Request $request){
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        $customer_id = $request->customer_id;

        $contact = Contact::where('id', $contact_id)->first();

        unlink(public_path('avatars/' . $contact->avatar));

        Contact::where('id', $contact_id)->update([
            'avatar' => ''
        ]);

        $contact = Contact::where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->first();

        $contacts = Contact::where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function deleteContact(Request $request){
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        
        $contact = Contact::where('id', $contact_id)->first();

        Contact::where('id', $contact_id)->delete();
        $contacts = Contact::where('customer_id', $contact->customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }





    public function getCarrierContacts(Request $request){

        $first_name = isset($request->first_name) ? trim($request->first_name) : '';
        $last_name = isset($request->last_name) ? trim($request->last_name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $phone = isset($request->phone) ? trim($request->phone) : '';
        $email = isset($request->email) ? trim($request->email) : '';

        $contacts = CarrierContact::whereRaw("1 = 1")
            ->whereRaw("LOWER(first_name) like '%$first_name%'")
            ->whereRaw("LOWER(last_name) like '%$last_name%'")
            ->whereRaw("LOWER(address1) like '%$address1%'")
            ->whereRaw("LOWER(address2) like '%$address2%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name', 'ASC')
            ->with('carrier')
            ->has('carrier')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function carrierContacts(Request $request)
    {
        $carrier_id = isset($request->carrier_id) ? trim($request->carrier_id) : null;

        $contacts = CarrierContact::whereRaw("1 = 1")
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function getCarrierContactById(Request $request)
    {
        $contact_id = $request->contact_id;
        $carrier_id = $request->carrier_id;

        $contact = CarrierContact::where('id', $contact_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->first();
        $contacts = CarrierContact::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function getContactsByCarrierId(Request $request)
    {
        $carrier_id = $request->carrier_id;
        $contacts = CarrierContact::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->get();
        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'contact' => null]);
    }

    public function saveCarrierContact(Request $request)
    {
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        $curContact = CarrierContact::where('id', $contact_id)->first();
        $carrier_id = $request->carrier_id;

        $prefix = isset($request->prefix) ? $request->prefix : ($curContact ? $curContact->prefix : '');
        $first_name = isset($request->first_name) ? $request->first_name : ($curContact ? $curContact->first_name : '');
        $middle_name = isset($request->middle_name) ? $request->middle_name : ($curContact ? $curContact->middle_name : '');
        $last_name = isset($request->last_name) ? $request->last_name : ($curContact ? $curContact->last_name : '');
        $suffix = isset($request->suffix) ? $request->suffix : ($curContact ? $curContact->suffix : '');
        $title = isset($request->title) ? $request->title : ($curContact ? $curContact->title : '');
        $department = isset($request->department) ? $request->department : ($curContact ? $curContact->department : '');
        $email_work = isset($request->email_work) ? $request->email_work : ($curContact ? $curContact->email_work : '');
        $email_personal = isset($request->email_personal) ? $request->email_personal : ($curContact ? $curContact->email_personal : '');
        $email_other = isset($request->email_other) ? $request->email_other : ($curContact ? $curContact->email_other : '');
        $primary_email = isset($request->primary_email) ? $request->primary_email : ($curContact ? $curContact->primary_email : 'work');
        $phone_work = isset($request->phone_work) ? $request->phone_work : ($curContact ? $curContact->phone_work : '');
        $phone_work_fax = isset($request->phone_work_fax) ? $request->phone_work_fax : ($curContact ? $curContact->phone_work_fax : '');
        $phone_mobile = isset($request->phone_mobile) ? $request->phone_mobile : ($curContact ? $curContact->phone_mobile : '');
        $phone_direct = isset($request->phone_direct) ? $request->phone_direct : ($curContact ? $curContact->phone_direct : '');
        $phone_other = isset($request->phone_other) ? $request->phone_other : ($curContact ? $curContact->phone_other : '');
        $primary_phone = isset($request->primary_phone) ? $request->primary_phone : ($curContact ? $curContact->primary_phone : 'work');
        $phone_ext = isset($request->phone_ext) ? $request->phone_ext : ($curContact ? $curContact->phone_ext : '');
        $country = isset($request->country) ? $request->country : ($curContact ? $curContact->country : '');
        $address1 = isset($request->address1) ? $request->address1 : ($curContact ? $curContact->address1 : '');
        $address2 = isset($request->address2) ? $request->address2 : ($curContact ? $curContact->address2 : '');
        $city = isset($request->city) ? $request->city : ($curContact ? $curContact->city : '');
        $state = isset($request->state) ? $request->state : ($curContact ? $curContact->state : '');
        $zip_code = isset($request->zip_code) ? $request->zip_code : ($curContact ? $curContact->zip_code : '');
        $birthday = isset($request->birthday) ? $request->birthday : ($curContact ? $curContact->birthday : '');
        $website = isset($request->website) ? $request->website : ($curContact ? $curContact->website : '');
        $notes = isset($request->notes) ? $request->notes : ($curContact ? $curContact->notes : '');
        $automatic_emails_to = isset($request->automatic_emails_to) ? $request->automatic_emails_to : ($curContact ? $curContact->automatic_emails_to : '');
        $automatic_emails_cc = isset($request->automatic_emails_cc) ? $request->automatic_emails_cc : ($curContact ? $curContact->automatic_emails_cc : '');
        $automatic_emails_bcc = isset($request->automatic_emails_bcc) ? $request->automatic_emails_bcc : ($curContact ? $curContact->automatic_emails_bcc : '');
        $is_primary = isset($request->is_primary) ? $request->is_primary : ($curContact ? $curContact->is_primary : 0);
        $is_online = isset($request->is_online) ? $request->is_online : ($curContact ? $curContact->is_online : 0);
        $automatic_emails_booked_load = isset($request->automatic_emails_booked_load) ? $request->automatic_emails_booked_load : ($curContact ? $curContact->automatic_emails_booked_load : 0);
        $automatic_emails_check_calls = isset($request->automatic_emails_check_calls) ? $request->automatic_emails_check_calls : ($curContact ? $curContact->automatic_emails_check_calls : 0);
        $automatic_emails_carrier_arrival_shipper = isset($request->automatic_emails_carrier_arrival_shipper) ? $request->automatic_emails_carrier_arrival_shipper : ($curContact ? $curContact->automatic_emails_carrier_arrival_shipper : 0);
        $automatic_emails_carrier_arrival_consignee = isset($request->automatic_emails_carrier_arrival_consignee) ? $request->automatic_emails_carrier_arrival_consignee : ($curContact ? $curContact->automatic_emails_carrier_arrival_consignee : 0);
        $automatic_emails_loaded = isset($request->automatic_emails_loaded) ? $request->automatic_emails_loaded : ($curContact ? $curContact->automatic_emails_loaded : 0);
        $automatic_emails_empty = isset($request->automatic_emails_empty) ? $request->automatic_emails_empty : ($curContact ? $curContact->automatic_emails_empty : 0);

        $is_primary = (int) $is_primary;

        $contact = CarrierContact::updateOrCreate([
            'id' => $contact_id
        ],
            [
                'carrier_id' => $carrier_id,
                'prefix' => $prefix,
                'first_name' => trim($first_name),
                'middle_name' => trim($middle_name),
                'last_name' => trim($last_name),
                'suffix' => $suffix,
                'title' => $title,
                'department' => $department,
                'email_work' => $email_work,
                'email_personal' => $email_personal,
                'email_other' => $email_other,
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => $country,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => $website,
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
                'automatic_emails_to' => $automatic_emails_to,
                'automatic_emails_cc' => $automatic_emails_cc,
                'automatic_emails_bcc' => $automatic_emails_bcc,
                'automatic_emails_booked_load' => $automatic_emails_booked_load,
                'automatic_emails_check_calls' => $automatic_emails_check_calls,
                'automatic_emails_carrier_arrival_shipper' => $automatic_emails_carrier_arrival_shipper,
                'automatic_emails_carrier_arrival_consignee' => $automatic_emails_carrier_arrival_consignee,
                'automatic_emails_loaded' => $automatic_emails_loaded,
                'automatic_emails_empty' => $automatic_emails_empty
            ]);

        if ($is_primary === 1){
            Carrier::where('id', $carrier_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        }

        $newContact = CarrierContact::where('id', $contact->id)
            ->with('carrier')
            ->has('carrier')
            ->first();

        $contacts = CarrierContact::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    public function uploadCarrierAvatar(Request $request)
    {
        $contact_id = $_POST['contact_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = CarrierContact::where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar){
            unlink(public_path('avatars/' . $cur_avatar));
        }

        CarrierContact::where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = CarrierContact::where('id', $contact_id)
            ->with('carrier')
            ->has('carrier')
            ->first();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact]);
    }

    public function removeCarrierAvatar(Request $request){
        $contact_id = $request->contact_id;

        $contact = CarrierContact::where('id', $contact_id)->first();

        unlink(public_path('avatars/' . $contact->avatar));

        CarrierContact::where('id', $contact_id)->update([
            'avatar' => null
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function deleteCarrierContact(Request $request){
        $contact_id = $request->id;

        $contact = CarrierContact::where('id', $contact_id)->first();

        CarrierContact::where('id', $contact_id)->delete();
        $contacts = CarrierContact::where('carrier_id', $contact->carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }


    public function saveFactoringCompanyContact(Request $request)
    {
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        $curContact = FactoringCompanyContact::where('id', $contact_id)->first();
        $factoring_company_id = $request->factoring_company_id;

        $prefix = isset($request->prefix) ? $request->prefix : ($curContact ? $curContact->prefix : '');
        $first_name = isset($request->first_name) ? $request->first_name : ($curContact ? $curContact->first_name : '');
        $middle_name = isset($request->middle_name) ? $request->middle_name : ($curContact ? $curContact->middle_name : '');
        $last_name = isset($request->last_name) ? $request->last_name : ($curContact ? $curContact->last_name : '');
        $suffix = isset($request->suffix) ? $request->suffix : ($curContact ? $curContact->suffix : '');
        $title = isset($request->title) ? $request->title : ($curContact ? $curContact->title : '');
        $department = isset($request->department) ? $request->department : ($curContact ? $curContact->department : '');
        $email_work = isset($request->email_work) ? $request->email_work : ($curContact ? $curContact->email_work : '');
        $email_personal = isset($request->email_personal) ? $request->email_personal : ($curContact ? $curContact->email_personal : '');
        $email_other = isset($request->email_other) ? $request->email_other : ($curContact ? $curContact->email_other : '');
        $primary_email = isset($request->primary_email) ? $request->primary_email : ($curContact ? $curContact->primary_email : 'work');
        $phone_work = isset($request->phone_work) ? $request->phone_work : ($curContact ? $curContact->phone_work : '');
        $phone_work_fax = isset($request->phone_work_fax) ? $request->phone_work_fax : ($curContact ? $curContact->phone_work_fax : '');
        $phone_mobile = isset($request->phone_mobile) ? $request->phone_mobile : ($curContact ? $curContact->phone_mobile : '');
        $phone_direct = isset($request->phone_direct) ? $request->phone_direct : ($curContact ? $curContact->phone_direct : '');
        $phone_other = isset($request->phone_other) ? $request->phone_other : ($curContact ? $curContact->phone_other : '');
        $primary_phone = isset($request->primary_phone) ? $request->primary_phone : ($curContact ? $curContact->primary_phone : 'work');
        $phone_ext = isset($request->phone_ext) ? $request->phone_ext : ($curContact ? $curContact->phone_ext : '');
        $country = isset($request->country) ? $request->country : ($curContact ? $curContact->country : '');
        $address1 = isset($request->address1) ? $request->address1 : ($curContact ? $curContact->address1 : '');
        $address2 = isset($request->address2) ? $request->address2 : ($curContact ? $curContact->address2 : '');
        $city = isset($request->city) ? $request->city : ($curContact ? $curContact->city : '');
        $state = isset($request->state) ? $request->state : ($curContact ? $curContact->state : '');
        $zip_code = isset($request->zip_code) ? $request->zip_code : ($curContact ? $curContact->zip_code : '');
        $birthday = isset($request->birthday) ? $request->birthday : ($curContact ? $curContact->birthday : '');
        $website = isset($request->website) ? $request->website : ($curContact ? $curContact->website : '');
        $notes = isset($request->notes) ? $request->notes : ($curContact ? $curContact->notes : '');
        $automatic_emails_to = isset($request->automatic_emails_to) ? $request->automatic_emails_to : ($curContact ? $curContact->automatic_emails_to : '');
        $automatic_emails_cc = isset($request->automatic_emails_cc) ? $request->automatic_emails_cc : ($curContact ? $curContact->automatic_emails_cc : '');
        $automatic_emails_bcc = isset($request->automatic_emails_bcc) ? $request->automatic_emails_bcc : ($curContact ? $curContact->automatic_emails_bcc : '');
        $is_primary = isset($request->is_primary) ? $request->is_primary : ($curContact ? $curContact->is_primary : 0);
        $is_online = isset($request->is_online) ? $request->is_online : ($curContact ? $curContact->is_online : 0);
        $automatic_emails_booked_load = isset($request->automatic_emails_booked_load) ? $request->automatic_emails_booked_load : ($curContact ? $curContact->automatic_emails_booked_load : 0);
        $automatic_emails_check_calls = isset($request->automatic_emails_check_calls) ? $request->automatic_emails_check_calls : ($curContact ? $curContact->automatic_emails_check_calls : 0);
        $automatic_emails_carrier_arrival_shipper = isset($request->automatic_emails_carrier_arrival_shipper) ? $request->automatic_emails_carrier_arrival_shipper : ($curContact ? $curContact->automatic_emails_carrier_arrival_shipper : 0);
        $automatic_emails_carrier_arrival_consignee = isset($request->automatic_emails_carrier_arrival_consignee) ? $request->automatic_emails_carrier_arrival_consignee : ($curContact ? $curContact->automatic_emails_carrier_arrival_consignee : 0);
        $automatic_emails_loaded = isset($request->automatic_emails_loaded) ? $request->automatic_emails_loaded : ($curContact ? $curContact->automatic_emails_loaded : 0);
        $automatic_emails_empty = isset($request->automatic_emails_empty) ? $request->automatic_emails_empty : ($curContact ? $curContact->automatic_emails_empty : 0);

        $is_primary = (int) $is_primary;

        $contact = FactoringCompanyContact::updateOrCreate([
            'id' => $contact_id
        ],
            [
                'factoring_company_id' => $factoring_company_id,
                'prefix' => $prefix,
                'first_name' => trim($first_name),
                'middle_name' => trim($middle_name),
                'last_name' => trim($last_name),
                'suffix' => $suffix,
                'title' => $title,
                'department' => $department,
                'email_work' => $email_work,
                'email_personal' => $email_personal,
                'email_other' => $email_other,
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => $country,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => $website,
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
                'automatic_emails_to' => $automatic_emails_to,
                'automatic_emails_cc' => $automatic_emails_cc,
                'automatic_emails_bcc' => $automatic_emails_bcc,
                'automatic_emails_booked_load' => $automatic_emails_booked_load,
                'automatic_emails_check_calls' => $automatic_emails_check_calls,
                'automatic_emails_carrier_arrival_shipper' => $automatic_emails_carrier_arrival_shipper,
                'automatic_emails_carrier_arrival_consignee' => $automatic_emails_carrier_arrival_consignee,
                'automatic_emails_loaded' => $automatic_emails_loaded,
                'automatic_emails_empty' => $automatic_emails_empty
            ]);

        if ($is_primary === 1){
            FactoringCompany::where('id', $factoring_company_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        }

        $newContact = FactoringCompanyContact::where('id', $contact->id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->first();

        $contacts = FactoringCompanyContact::where('factoring_company_id', $factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    public function uploadFactoringCompanyAvatar(Request $request)
    {
        $contact_id = $_POST['contact_id'];
        $factoring_company_id = $_POST['factoring_company_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = FactoringCompanyContact::where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar){
            unlink(public_path('avatars/' . $cur_avatar));
        }

        FactoringCompanyContact::where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = FactoringCompanyContact::where('id', $contact_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->first();

        $contacts = FactoringCompanyContact::where('factoring_company_id', $factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name', 'ASC')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function removeFactoringCompanyAvatar(Request $request){
        $contact_id = isset($request->contact_id) ? $request->contact_id : (isset($request->id) ? $request->id : 0);
        $factoring_company_id = $request->factoring_company_id;

        $contact = FactoringCompanyContact::where('id', $contact_id)->first();

        unlink(public_path('avatars/' . $contact->avatar));

        $contact = FactoringCompanyContact::where('id', $contact_id)->update([
            'avatar' => null
        ]);

        $contacts = FactoringCompanyContact::where('factoring_company_id', $factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact,'contacts' => $contacts]);
    }

    public function deleteFactoringCompanyContact(Request $request){
        $contact_id = $request->id;

        $contact = FactoringCompanyContact::where('id', $contact_id)->first();

        FactoringCompanyContact::where('id', $contact_id)->delete();
        $contacts = FactoringCompanyContact::where('factoring_company_id', $contact->factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function factoringCompanyContactsSearch(Request $request): \Illuminate\Http\JsonResponse
    {

        $factoring_company_id = isset($request->search[0]['data']) ? trim($request->search[0]['data']) : 0;
        $first_name = isset($request->search[1]['data']) ? trim($request->search[1]['data']) : '';
        $last_name = isset($request->search[2]['data']) ? trim($request->search[2]['data']) : '';
        $address1 = isset($request->search[3]['data']) ? trim($request->search[3]['data']) : '';
        $address2 = isset($request->search[4]['data']) ? trim($request->search[4]['data']) : '';
        $city = isset($request->search[5]['data']) ? trim($request->search[5]['data']) : '';
        $state = isset($request->search[6]['data']) ? trim($request->search[6]['data']) : '';
        $phone = isset($request->search[7]['data']) ? trim($request->search[7]['data']) : '';
        $email = isset($request->search[8]['data']) ? trim($request->search[8]['data']) : '';

        if ($factoring_company_id == 0){
            $contacts = FactoringCompanyContact::whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('factoring_company')
                ->has('factoring_company')
                ->get();
        }else{
            $contacts = FactoringCompanyContact::whereRaw("1 = 1")
                ->whereRaw("factoring_company_id = $factoring_company_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name', 'ASC')
                ->with('factoring_company')
                ->has('factoring_company')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }
}
