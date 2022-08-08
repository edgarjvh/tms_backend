<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Contact;
use App\Models\CarrierContact;
use App\Models\Customer;
use App\Models\FactoringCompany;
use App\Models\FactoringCompanyContact;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ContactsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContacts(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $customer_id = $request->customer_id ?? 0;
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';

        if ($customer_id == 0) {
            $contacts = $CUSTOMER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('customer')
                ->has('customer')
                ->get();
        } else {
            $contacts = $CUSTOMER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("customer_id = $customer_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('customer')
                ->has('customer')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerContactsSearch(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $customer_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($customer_id == 0) {
            $contacts = $CUSTOMER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('customer')
                ->has('customer')
                ->get();
        } else {
            $contacts = $CUSTOMER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("customer_id = $customer_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('customer')
                ->has('customer')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function carrierContactsSearch(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $carrier_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($carrier_id == 0) {
            $contacts = $CARRIER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('carrier')
                ->has('carrier')
                ->get();
        } else {
            $contacts = $CARRIER_CONTACT->whereRaw("1 = 1")
                ->whereRaw("carrier_id = $carrier_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('carrier')
                ->has('carrier')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByEmail(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $email = $request->email ?? '';

        $contacts = $CUSTOMER_CONTACT->whereRaw("1 = 1")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByEmailOrName(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $email = $request->email ?? '';

        $contacts = $CUSTOMER_CONTACT->whereRaw("((LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%') OR (LOWER(first_name) like '%$email%' or LOWER(last_name) like '%$email%'))")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @throws Exception
     */
    public function contacts(): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $contacts = $CUSTOMER_CONTACT->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactById(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $contact_id = $request->contact_id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->first();
        $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByCustomerId(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $customer_id = $request->customer_id;
        $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();
        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'contact' => null]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveContact(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();
        $CUSTOMER = new Customer();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $customer_id = $request->customer_id ?? 0;

        if ($customer_id > 0) {
            $curContact = $CUSTOMER_CONTACT->where('id', $contact_id)->first();

            $customer = $CUSTOMER->where('id', $customer_id)->first();

            $carrier_id = $curContact ? $curContact->carrier_id : null;
            $factoring_company_id = $curContact ? $curContact->factoring_company_id : null;
            $prefix = $request->prefix ?? ($curContact ? $curContact->prefix : '');
            $first_name = $request->first_name ?? ($curContact ? $curContact->first_name : '');
            $middle_name = $request->middle_name ?? ($curContact ? $curContact->middle_name : '');
            $last_name = $request->last_name ?? ($curContact ? $curContact->last_name : '');
            $suffix = $request->suffix ?? ($curContact ? $curContact->suffix : '');
            $title = $request->title ?? ($curContact ? $curContact->title : '');
            $department = $request->department ?? ($curContact ? $curContact->department : '');
            $email_work = $request->email_work ?? ($curContact ? $curContact->email_work : '');
            $email_personal = $request->email_personal ?? ($curContact ? $curContact->email_personal : '');
            $email_other = $request->email_other ?? ($curContact ? $curContact->email_other : '');
            $primary_email = $request->primary_email ?? ($curContact ? $curContact->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curContact ? $curContact->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curContact ? $curContact->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curContact ? $curContact->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curContact ? $curContact->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curContact ? $curContact->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curContact ? $curContact->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curContact ? $curContact->phone_ext : '');
            $country = $request->country ?? ($curContact ? $curContact->country : '');
            $address1 = $request->address1 ?? ($curContact ? $curContact->address1 : $customer->address1);
            $address2 = $request->address2 ?? ($curContact ? $curContact->address2 : $customer->address2);
            $city = $request->city ?? ($curContact ? $curContact->city : $customer->city);
            $state = $request->state ?? ($curContact ? $curContact->state : $customer->state);
            $zip_code = $request->zip_code ?? ($curContact ? $curContact->zip_code : $customer->zip);
            $birthday = $request->birthday ?? ($curContact ? $curContact->birthday : '');
            $website = $request->website ?? ($curContact ? $curContact->website : '');
            $notes = $request->notes ?? ($curContact ? $curContact->notes : '');
            $is_primary = $request->is_primary ?? ($curContact ? $curContact->is_primary : 0);
            $is_online = $request->is_online ?? ($curContact ? $curContact->is_online : 0);

            $is_primary = (int)$is_primary;

            $contact = $CUSTOMER_CONTACT->updateOrCreate([
                'id' => $contact_id
            ],
                [
                    'carrier_id' => $carrier_id,
                    'customer_id' => $customer_id,
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
                    'is_online' => $is_online
                ]);

            if ($is_primary === 1) {
                $CUSTOMER->where('id', $customer_id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } else {
                $CUSTOMER->where(['id' => $customer_id, 'primary_contact_id' => $contact->id])->update([
                    'primary_contact_id' => null
                ]);
            }

            $newContact = $CUSTOMER_CONTACT->where('id', $contact->id)
                ->with('customer')
                ->has('customer')
                ->first();

            $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)
                ->with('customer')
                ->has('customer')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts, 'work' => $request->phone_work]);
        } else {
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $contact_id = $_POST['contact_id'];
        $customer_id = $request->customer_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            try {
                unlink(public_path('avatars/' . $cur_avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $CUSTOMER_CONTACT->where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->first();

        $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $customer_id = $request->customer_id;

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)->first();

        try {
            unlink(public_path('avatars/' . $contact->avatar));
        } catch (Throwable | Exception $e) {
        }

        $CUSTOMER_CONTACT->where('id', $contact_id)->update([
            'avatar' => ''
        ]);

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)
            ->with('customer')
            ->has('customer')
            ->first();

        $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteContact(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);

        $contact = $CUSTOMER_CONTACT->where('id', $contact_id)->first();

        $CUSTOMER_CONTACT->where('id', $contact_id)->delete();
        $contacts = $CUSTOMER_CONTACT->where('customer_id', $contact->customer_id)
            ->with('customer')
            ->has('customer')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierContacts(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';

        $contacts = $CARRIER_CONTACT->whereRaw("1 = 1")
            ->has('carrier')
            ->whereRaw("LOWER(first_name) like '%$first_name%'")
            ->whereRaw("LOWER(last_name) like '%$last_name%'")
            ->whereRaw("LOWER(address1) like '%$address1%'")
            ->whereRaw("LOWER(address2) like '%$address2%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name')
            ->with('carrier')
            ->has('carrier')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @throws Exception
     */
    public function carrierContacts(): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $contacts = $CARRIER_CONTACT->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierContactById(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $contact_id = $request->contact_id ?? 0;
        $carrier_id = $request->carrier_id ?? 0;

        $contact = $CARRIER_CONTACT->where('id', $contact_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->first();
        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByCarrierId(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $carrier_id = $request->carrier_id ?? 0;

        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'contact' => null]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierContact(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new CarrierContact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $carrier_id = $request->carrier_id ?? 0;

        if ($carrier_id > 0){
            $curContact = $CARRIER_CONTACT->where('id', $contact_id)->first();

            $customer_id = $curContact ? $curContact->customer_id : null;
            $factoring_company_id = $curContact ? $curContact->factoring_company_id : null;
            $prefix = $request->prefix ?? ($curContact ? $curContact->prefix : '');
            $first_name = $request->first_name ?? ($curContact ? $curContact->first_name : '');
            $middle_name = $request->middle_name ?? ($curContact ? $curContact->middle_name : '');
            $last_name = $request->last_name ?? ($curContact ? $curContact->last_name : '');
            $suffix = $request->suffix ?? ($curContact ? $curContact->suffix : '');
            $title = $request->title ?? ($curContact ? $curContact->title : '');
            $department = $request->department ?? ($curContact ? $curContact->department : '');
            $email_work = $request->email_work ?? ($curContact ? $curContact->email_work : '');
            $email_personal = $request->email_personal ?? ($curContact ? $curContact->email_personal : '');
            $email_other = $request->email_other ?? ($curContact ? $curContact->email_other : '');
            $primary_email = $request->primary_email ?? ($curContact ? $curContact->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curContact ? $curContact->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curContact ? $curContact->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curContact ? $curContact->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curContact ? $curContact->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curContact ? $curContact->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curContact ? $curContact->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curContact ? $curContact->phone_ext : '');
            $country = $request->country ?? ($curContact ? $curContact->country : '');
            $address1 = $request->address1 ?? ($curContact ? $curContact->address1 : '');
            $address2 = $request->address2 ?? ($curContact ? $curContact->address2 : '');
            $city = $request->city ?? ($curContact ? $curContact->city : '');
            $state = $request->state ?? ($curContact ? $curContact->state : '');
            $zip_code = $request->zip_code ?? ($curContact ? $curContact->zip_code : '');
            $birthday = $request->birthday ?? ($curContact ? $curContact->birthday : '');
            $website = $request->website ?? ($curContact ? $curContact->website : '');
            $notes = $request->notes ?? ($curContact ? $curContact->notes : '');
            $is_primary = $request->is_primary ?? ($curContact ? $curContact->is_primary : 0);
            $is_online = $request->is_online ?? ($curContact ? $curContact->is_online : 0);

            $is_primary = (int)$is_primary;

            $contact = $CARRIER_CONTACT->updateOrCreate([
                'id' => $contact_id
            ],
                [
                    'carrier_id' => $carrier_id,
                    'customer_id' => $customer_id,
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
                    'is_online' => $is_online
                ]);

            if ($is_primary === 1) {
                $CARRIER->where('id', $carrier_id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            }

            $newContact = $CARRIER_CONTACT->where('id', $contact->id)
                ->with('carrier')
                ->has('carrier')
                ->first();

            $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)
                ->with('carrier')
                ->has('carrier')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
        }else{
            return response()->json(['result' => 'NO CARRIER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadCarrierAvatar(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $contact_id = $_POST['contact_id'];
        $carrier_id = $request->carrier_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $CARRIER_CONTACT->where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            try {
                unlink(public_path('avatars/' . $cur_avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $CARRIER_CONTACT->where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = $CARRIER_CONTACT->where('id', $contact_id)
            ->with('carrier')
            ->has('carrier')
            ->first();

        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCarrierAvatar(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $carrier_id = $request->carrier_id;

        $contact = $CARRIER_CONTACT->where('id', $contact_id)->first();

        try {
            unlink(public_path('avatars/' . $contact->avatar));
        } catch (Throwable | Exception $e) {
        }

        $CARRIER_CONTACT->where('id', $contact_id)->update([
            'avatar' => ''
        ]);

        $contact = $CARRIER_CONTACT->where('id', $contact_id)
            ->with('carrier')
            ->has('carrier')
            ->first();

        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierContact(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $contact_id = $request->id;

        $contact = $CARRIER_CONTACT->where('id', $contact_id)->first();

        $CARRIER_CONTACT->where('id', $contact_id)->delete();

        $contacts = $CARRIER_CONTACT->where('carrier_id', $contact->carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFactoringCompanyContact(Request $request): JsonResponse
    {
        $FACTORING_COMPANY = new FactoringCompany();
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $factoring_company_id = $request->factoring_company_id;

        if ($factoring_company_id > 0){
            $curContact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->first();

            $carrier_id = $curContact ? $curContact->carrier_id : null;
            $customer_id = $curContact ? $curContact->customer_id : null;
            $prefix = $request->prefix ?? ($curContact ? $curContact->prefix : '');
            $first_name = $request->first_name ?? ($curContact ? $curContact->first_name : '');
            $middle_name = $request->middle_name ?? ($curContact ? $curContact->middle_name : '');
            $last_name = $request->last_name ?? ($curContact ? $curContact->last_name : '');
            $suffix = $request->suffix ?? ($curContact ? $curContact->suffix : '');
            $title = $request->title ?? ($curContact ? $curContact->title : '');
            $department = $request->department ?? ($curContact ? $curContact->department : '');
            $email_work = $request->email_work ?? ($curContact ? $curContact->email_work : '');
            $email_personal = $request->email_personal ?? ($curContact ? $curContact->email_personal : '');
            $email_other = $request->email_other ?? ($curContact ? $curContact->email_other : '');
            $primary_email = $request->primary_email ?? ($curContact ? $curContact->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curContact ? $curContact->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curContact ? $curContact->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curContact ? $curContact->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curContact ? $curContact->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curContact ? $curContact->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curContact ? $curContact->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curContact ? $curContact->phone_ext : '');
            $country = $request->country ?? ($curContact ? $curContact->country : '');
            $address1 = $request->address1 ?? ($curContact ? $curContact->address1 : '');
            $address2 = $request->address2 ?? ($curContact ? $curContact->address2 : '');
            $city = $request->city ?? ($curContact ? $curContact->city : '');
            $state = $request->state ?? ($curContact ? $curContact->state : '');
            $zip_code = $request->zip_code ?? ($curContact ? $curContact->zip_code : '');
            $birthday = $request->birthday ?? ($curContact ? $curContact->birthday : '');
            $website = $request->website ?? ($curContact ? $curContact->website : '');
            $notes = $request->notes ?? ($curContact ? $curContact->notes : '');
            $is_primary = $request->is_primary ?? ($curContact ? $curContact->is_primary : 0);
            $is_online = $request->is_online ?? ($curContact ? $curContact->is_online : 0);

            $is_primary = (int)$is_primary;

            $contact = $FACTORING_COMPANY_CONTACT->updateOrCreate([
                'id' => $contact_id
            ],
                [
                    'carrier_id' => $carrier_id,
                    'customer_id' => $customer_id,
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
                ]);

            if ($is_primary === 1) {
                $FACTORING_COMPANY->where('id', $factoring_company_id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            }

            $newContact = $FACTORING_COMPANY_CONTACT->where('id', $contact->id)
                ->with('factoring_company')
                ->has('factoring_company')
                ->first();

            $contacts = $FACTORING_COMPANY_CONTACT->where('factoring_company_id', $factoring_company_id)
                ->with('factoring_company')
                ->has('factoring_company')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
        }else{
            return response()->json(['result' => 'NO FACTORING COMPANY']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFactoringCompanyAvatar(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $contact_id = $_POST['contact_id'];
        $factoring_company_id = $request->factoring_company_id ?? 0;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            try {
                unlink(public_path('avatars/' . $cur_avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->first();

        $contacts = $FACTORING_COMPANY_CONTACT->where('factoring_company_id', $factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFactoringCompanyAvatar(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $factoring_company_id = $request->factoring_company_id ?? 0;

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->first();

        try {
            unlink(public_path('avatars/' . $contact->avatar));
        } catch (Throwable | Exception $e) {
        }

        $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->update([
            'avatar' => ''
        ]);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->first();

        $contacts = $FACTORING_COMPANY_CONTACT->where('factoring_company_id', $factoring_company_id)
            ->with('factoring_company')
            ->has('factoring_company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFactoringCompanyContact(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $contact_id = $request->id ?? 0;

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->first();

        $FACTORING_COMPANY_CONTACT->where('id', $contact_id)->delete();

        $contacts = $FACTORING_COMPANY_CONTACT->where('factoring_company_id', $contact->factoring_company_id)
        ->with('factoring_company')
        ->has('factoring_company')
        ->orderBy('first_name')
        ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function factoringCompanyContactsSearch(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $factoring_company_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($factoring_company_id == 0) {
            $contacts = $FACTORING_COMPANY_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('factoring_company')
                ->has('factoring_company')
                ->get();
        } else {
            $contacts = $FACTORING_COMPANY_CONTACT->whereRaw("1 = 1")
                ->whereRaw("factoring_company_id = $factoring_company_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('factoring_company')
                ->has('factoring_company')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }
}
