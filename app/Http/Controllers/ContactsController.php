<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Contact;
use App\Models\CarrierContact;
use App\Models\ContactCustomer;
use App\Models\Customer;
use App\Models\FactoringCompany;
use App\Models\FactoringCompanyContact;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    // CUSTOMER CONTACTS ===================================================
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByCustomerId(Request $request): JsonResponse
    {
        $customer_id = $request->owner_id;

        $contacts = $this->getCustomerContacts($customer_id);

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param $customer_id
     * @return array
     */
    public function getCustomerContacts($customer_id): array
    {
        $sql1 =
            /** @lang text */
            "SELECT
                c.*,
                CONCAT(0) AS is_pivot,
                CONCAT(0) AS pivot_is_primary,
                cu.name AS owner_name,
                CONCAT(null) AS pivot
            FROM contacts AS c
            LEFT JOIN customers AS cu ON c.customer_id = cu.id
            WHERE customer_id = ?
            ORDER BY first_name";

        $params1 = [$customer_id];

        $contacts1 = DB::select($sql1, $params1);

        $sql2 =
            /** @lang text */
            "SELECT
                c.*,
                CONCAT(1) AS is_pivot,
                cc.is_primary AS pivot_is_primary,
                cu.name AS owner_name
            FROM contacts AS c
            LEFT JOIN contact_customer AS cc ON c.id = cc.contact_id
            LEFT JOIN customers AS cu ON c.customer_id = cu.id
            WHERE cc.customer_id = ?
            ORDER BY c.first_name";

        $params2 = [$customer_id];

        $contacts2 = DB::select($sql2, $params2);

        if (!empty($contacts2)) {
            foreach ($contacts2 as $contact) {
                $pivot = ContactCustomer::where('contact_id', $contact->id)
                    ->where('customer_id', $customer_id)
                    ->first();

                if ($pivot) {
                    $contact->pivot = [
                        'id' => $pivot->id,
                        'is_primary' => $pivot->is_primary,
                        'customer_id' => $pivot->customer_id,
                        'contact_id' => $pivot->contact_id
                    ];
                } else {
                    $contact->pivot = null;
                }
            }
        }
        $contacts = array_merge($contacts1, $contacts2);

        return $contacts;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveContact(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $customer_id = $request->customer_id ?? null;
        $owner_id = $request->owner_id ?? null;
        $prefix = $request->prefix ?? '';
        $first_name = ucwords($request->first_name ?? '');
        $middle_name = ucwords($request->middle_name ?? '');
        $last_name = ucwords($request->last_name ?? '');
        $suffix = $request->suffix ?? '';
        $title = ucwords($request->title ?? '');
        $company = ucwords($request->company ?? '');
        $department = ucwords($request->department ?? '');
        $email_work = strtolower($request->email_work ?? '');
        $email_personal = strtolower($request->email_personal ?? '');
        $email_other = strtolower($request->email_other ?? '');
        $primary_email = $request->primary_email ?? 'work';
        $phone_work = $request->phone_work ?? '';
        $phone_work_fax = $request->phone_work_fax ?? '';
        $phone_mobile = $request->phone_mobile ?? '';
        $phone_direct = $request->phone_direct ?? '';
        $phone_other = $request->phone_other ?? '';
        $primary_phone = $request->primary_phone ?? 'work';
        $phone_ext = $request->phone_ext ?? '';
        $country = ucwords($request->country ?? '');
        $address1 = ucwords($request->address1 ?? '');
        $address2 = ucwords($request->address2 ?? '');
        $city = ucwords($request->city ?? '');
        $state = strtoupper($request->state ?? '');
        $zip_code = $request->zip_code ?? '';
        $birthday = $request->birthday ?? '';
        $website = strtolower($request->website ?? '');
        $notes = $request->notes ?? '';
        $is_primary = $request->is_primary ?? 0;
        $is_online = $request->is_online ?? 0;
        $type = $request->type ?? 'internal';
        $pivot = $request->pivot ?? null;
        $is_primary = (int)$is_primary;

        $CONTACT = new Contact();
        $CUSTOMER = new Customer();

        $contact = $CONTACT->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'customer_id' => $owner_id,
                'prefix' => $prefix,
                'first_name' => ucwords(trim($first_name)),
                'middle_name' => ucwords(trim($middle_name)),
                'last_name' => ucwords(trim($last_name)),
                'suffix' => $suffix,
                'title' => $title,
                'company' => $company,
                'department' => $department,
                'email_work' => strtolower($email_work),
                'email_personal' => strtolower($email_personal),
                'email_other' => strtolower($email_other),
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => ucwords($country),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => strtolower($website),
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
                'type' => $type
            ]
        );

        $CONTACT_CUSTOMER = new ContactCustomer();

        if ($pivot) {
            if (($pivot['is_primary'] ?? 0) === 1) {
                $CUSTOMER->where('id', $customer_id)->update([
                    'primary_contact_id' => $contact->id
                ]);

                $CONTACT_CUSTOMER->where('id', $pivot['id'])->update([
                    'is_primary' => 1
                ]);
            } else {
                $CUSTOMER->where(['id' => $customer_id, 'primary_contact_id' => $contact->id])->update([
                    'primary_contact_id' => null
                ]);

                $CONTACT_CUSTOMER->where('id', $pivot['id'])->update([
                    'is_primary' => 0
                ]);
            }
        } else {
            if ($is_primary === 1) {
                $CUSTOMER->where('id', $customer_id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } else {
                $CUSTOMER->where(['id' => $customer_id, 'primary_contact_id' => $contact->id])->update([
                    'primary_contact_id' => null
                ]);
            }
        }

        $newContact = $CONTACT->where('id', $contact->id)->first();

        $contacts = $this->getCustomerContacts($customer_id);

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $CONTACT = new Contact();

        $id = $_POST['id'];
        $customer_id = $_POST['owner_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) { // delete old avatar
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $CONTACT->where('id', $id)->update([ // update avatar
            'avatar' => $new_avatar
        ]);

        $contact = $CONTACT->where('id', $id)->first();

        $contacts = $this->getCustomerContacts($customer_id); // get all contacts for the customer

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar)); // move new avatar to public folder

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $CONTACT = new Contact();

        $id = $request->id ?? null;
        $customer_id = $request->owner_id ?? null;

        $contact = $CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) { // delete old avatar
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $CONTACT->where('id', $id)->update([ // update avatar
            'avatar' => null
        ]);

        $contact = $CONTACT->where('id', $id)->first();

        $contacts = $this->getCustomerContacts($customer_id); // get all contacts for the customer

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteContact(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $customer_id = $request->owner_id ?? null;
        $is_pivot = $request->is_pivot ?? 0;

        $CONTACT = new Contact();
        $CONTACT_CUSTOMER = new ContactCustomer();


        if ($is_pivot > 0) { // delete contact from pivot table
            $CONTACT_CUSTOMER->where([
                'contact_id' => $id,
                'customer_id' => $customer_id
            ])->delete();
        } else {
            $isUserContact = Contact::where('id', $id)->whereNotNull('user_code_id')->first(); // check if contact is a user contact

            if ($isUserContact) {
                $CONTACT->where('id', $id)->update([
                    'customer_id' => null // set customer_id to null
                ]);
            } else {
                $CONTACT->where('id', $id)->delete(); // delete contact
            }
        }

        $contacts = $this->getCustomerContacts($customer_id); // get all contacts for the customer

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    // CARRIER CONTACTS ===================================================
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
        $carrier_id = $request->owner_id;

        $contacts = $this->getCarrierContacts1($carrier_id);

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param $carrier_id
     * @return array
     */
    public function getCarrierContacts1($carrier_id): array
    {
        $sql =
            /** @lang text */
            "SELECT
                c.*,
                ca.name AS owner_name
            FROM contacts AS c
            LEFT JOIN carriers AS ca ON c.carrier_id = ca.id
            WHERE carrier_id = ?
            ORDER BY first_name";

        $params = [$carrier_id];

        $contacts = DB::select($sql, $params);

        return $contacts;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierContact(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $carrier_id = $request->owner_id ?? null;
        $prefix = $request->prefix ?? '';
        $first_name = ucwords($request->first_name ?? '');
        $middle_name = ucwords($request->middle_name ?? '');
        $last_name = ucwords($request->last_name ?? '');
        $suffix = $request->suffix ?? '';
        $title = ucwords($request->title ?? '');
        $company = ucwords($request->company ?? '');
        $department = ucwords($request->department ?? '');
        $email_work = strtolower($request->email_work ?? '');
        $email_personal = strtolower($request->email_personal ?? '');
        $email_other = strtolower($request->email_other ?? '');
        $primary_email = $request->primary_email ?? 'work';
        $phone_work = $request->phone_work ?? '';
        $phone_work_fax = $request->phone_work_fax ?? '';
        $phone_mobile = $request->phone_mobile ?? '';
        $phone_direct = $request->phone_direct ?? '';
        $phone_other = $request->phone_other ?? '';
        $primary_phone = $request->primary_phone ?? 'work';
        $phone_ext = $request->phone_ext ?? '';
        $country = ucwords($request->country ?? '');
        $address1 = ucwords($request->address1 ?? '');
        $address2 = ucwords($request->address2 ?? '');
        $city = ucwords($request->city ?? '');
        $state = strtoupper($request->state ?? '');
        $zip_code = $request->zip_code ?? '';
        $birthday = $request->birthday ?? '';
        $website = strtolower($request->website ?? '');
        $notes = $request->notes ?? '';
        $is_primary = $request->is_primary ?? 0;
        $is_online = $request->is_online ?? 0;
        $type = $request->type ?? 'internal';
        $is_primary = (int)$is_primary;

        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new CarrierContact();

        $contact = $CARRIER_CONTACT->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'carrier_id' => $carrier_id,
                'prefix' => $prefix,
                'first_name' => ucwords(trim($first_name)),
                'middle_name' => ucwords(trim($middle_name)),
                'last_name' => ucwords(trim($last_name)),
                'suffix' => $suffix,
                'title' => $title,
                'company' => $company,
                'department' => $department,
                'email_work' => strtolower($email_work),
                'email_personal' => strtolower($email_personal),
                'email_other' => strtolower($email_other),
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => ucwords($country),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => strtolower($website),
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
                'type' => $type
            ]
        );

        if ($is_primary === 1) {
            $CARRIER->where('id', $carrier_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        }else{
            $CARRIER->where(['id' => $carrier_id, 'primary_contact_id' => $contact->id])->update([
                'primary_contact_id' => null
            ]);
        }

        $newContact = $CARRIER_CONTACT->where('id', $contact->id)->first();

        $contacts = $this->getCarrierContacts1($carrier_id); // get all contacts for the carrier

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadCarrierAvatar(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $id = $_POST['id'];
        $carrier_id = $_POST['owner_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $CARRIER_CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) { // delete old avatar
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $CARRIER_CONTACT->where('id', $id)->update([ // update avatar
            'avatar' => $new_avatar
        ]);

        $contact = $CARRIER_CONTACT->where('id', $id)->first(); // get updated contact

        $contacts = $this->getCarrierContacts1($carrier_id); // get all contacts for the carrier

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar)); // move new avatar to public folder

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCarrierAvatar(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $id = $request->id ?? null;
        $carrier_id = $request->owner_id ?? null;

        $contact = $CARRIER_CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) { // delete old avatar
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $CARRIER_CONTACT->where('id', $id)->update([
            'avatar' => null
        ]);

        $contact = $CARRIER_CONTACT->where('id', $id)->first(); // get updated contact

        $contacts = $this->getCarrierContacts1($carrier_id); // get all contacts for the carrier

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierContact(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();

        $id = $request->id;
        $carrier_id = $request->owner_id;

        $isUserContact = CarrierContact::where('id', $id)->whereNotNull('user_code_id')->first(); // check if contact is a user contact
        if ($isUserContact) {
            $CARRIER_CONTACT->where('id', $id)->update([
                'carrier_id' => null // set carrier_id to null
            ]);
        } else {
            $CARRIER_CONTACT->where('id', $id)->delete(); // delete contact
        }

        $contacts = $this->getCarrierContacts1($carrier_id); // get all contacts for the carrier

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    // FACTORING COMPANY CONTACTS ===================================================

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByFactoringCompanyId(Request $request): JsonResponse
    {
        $factoring_company_id = $request->owner_id;

        $contacts = $this->getFactoringCompanyContacts($factoring_company_id);
        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    public function getFactoringCompanyContacts($factoring_company_id): array // get all contacts for the factoring company
    {
        $sql =
            /** @lang text */
            "SELECT
                c.*,
                fc.name AS owner_name
            FROM contacts AS c
            LEFT JOIN factoring_companies AS fc ON c.factoring_company_id = fc.id
            WHERE factoring_company_id = ?
            ORDER BY first_name";

        $params = [$factoring_company_id];

        $contacts = DB::select($sql, $params);

        return $contacts;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFactoringCompanyContact(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $factoring_company_id = $request->owner_id ?? null;
        $prefix = $request->prefix ?? '';
        $first_name = ucwords($request->first_name ?? '');
        $middle_name = ucwords($request->middle_name ?? '');
        $last_name = ucwords($request->last_name ?? '');
        $suffix = $request->suffix ?? '';
        $title = ucwords($request->title ?? '');
        $company = ucwords($request->company ?? '');
        $department = ucwords($request->department ?? '');
        $email_work = strtolower($request->email_work ?? '');
        $email_personal = strtolower($request->email_personal ?? '');
        $email_other = strtolower($request->email_other ?? '');
        $primary_email = $request->primary_email ?? 'work';
        $phone_work = $request->phone_work ?? '';
        $phone_work_fax = $request->phone_work_fax ?? '';
        $phone_mobile = $request->phone_mobile ?? '';
        $phone_direct = $request->phone_direct ?? '';
        $phone_other = $request->phone_other ?? '';
        $primary_phone = $request->primary_phone ?? 'work';
        $phone_ext = $request->phone_ext ?? '';
        $country = ucwords($request->country ?? '');
        $address1 = ucwords($request->address1 ?? '');
        $address2 = ucwords($request->address2 ?? '');
        $city = ucwords($request->city ?? '');
        $state = strtoupper($request->state ?? '');
        $zip_code = $request->zip_code ?? '';
        $birthday = $request->birthday ?? '';
        $website = strtolower($request->website ?? '');
        $notes = $request->notes ?? '';
        $is_primary = $request->is_primary ?? 0;
        $is_online = $request->is_online ?? 0;
        $is_primary = (int)$is_primary;

        $FACTORING_COMPANY = new FactoringCompany();
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $contact = $FACTORING_COMPANY_CONTACT->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'factoring_company_id' => $factoring_company_id,
                'prefix' => $prefix,
                'first_name' => ucwords(trim($first_name)),
                'middle_name' => ucwords(trim($middle_name)),
                'last_name' => ucwords(trim($last_name)),
                'suffix' => $suffix,
                'title' => $title,
                'company' => $company,
                'department' => $department,
                'email_work' => strtolower($email_work),
                'email_personal' => strtolower($email_personal),
                'email_other' => strtolower($email_other),
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => ucwords($country),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => strtolower($website),
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online,
            ]
        );

        if ($is_primary === 1) {
            $FACTORING_COMPANY->where('id', $factoring_company_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        }else{
            $FACTORING_COMPANY->where(['id' => $factoring_company_id, 'primary_contact_id' => $contact->id])->update([
                'primary_contact_id' => null
            ]);
        }

        $newContact = $FACTORING_COMPANY_CONTACT->where('id', $contact->id)->first();

        $contacts = $this->getFactoringCompanyContacts($factoring_company_id); // get all contacts for the factoring company

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadFactoringCompanyAvatar(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $id = $_POST['id'];
        $factoring_company_id = $_POST['owner_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) { // delete old avatar
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $FACTORING_COMPANY_CONTACT->where('id', $id)->update([ // update avatar
            'avatar' => $new_avatar
        ]);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $id)->first();

        $contacts = $this->getFactoringCompanyContacts($factoring_company_id); // get all contacts for the factoring company

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar)); // move new avatar to public folder

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFactoringCompanyAvatar(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $id = $request->id ?? null;
        $factoring_company_id = $request->owner_id ?? null;

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) { // delete old avatar
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $FACTORING_COMPANY_CONTACT->where('id', $id)->update([ // update avatar
            'avatar' => null
        ]);

        $contact = $FACTORING_COMPANY_CONTACT->where('id', $id)->first();

        $contacts = $this->getFactoringCompanyContacts($factoring_company_id); // get all contacts for the factoring company

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFactoringCompanyContact(Request $request): JsonResponse
    {
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();

        $id = $request->id ?? 0;
        $factoring_company_id = $request->owner_id ?? null;

        $isUserContact = FactoringCompanyContact::where('id', $id)->whereNotNull('user_code_id')->first(); // check if contact is a user contact

        if ($isUserContact) {
            $FACTORING_COMPANY_CONTACT->where('id', $id)->update([
                'factoring_company_id' => null // set factoring_company_id to null
            ]);
        } else {
            $FACTORING_COMPANY_CONTACT->where('id', $id)->delete(); // delete contact
        }

        $contacts = $this->getFactoringCompanyContacts($factoring_company_id); // get all contacts for the factoring company

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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactList(Request $request): JsonResponse
    {
        $customer_id = $request->customer_id ?? 0;
        $user_code = $request->user_code ?? '';
        $user_type = $request->user_type ?? 'employee';

        if ($user_type === 'agent' && $user_code === '') {
            return response()->json(['result' => 'NO USER', 'count' => 0, 'contacts' => []]);
        }

        if ($customer_id === 0) {
            return response()->json(['result' => 'NO CUSTOMER', 'count' => 0, 'contacts' => []]);
        }

        $CONTACT = Contact::query();

        $CONTACT->whereRaw("first_name <> '' AND last_name <> ''");

        $CONTACT->where(function ($query) use ($user_type, $user_code, $customer_id) {
            $query->where(function ($query1) use ($user_type, $user_code) {
                if ($user_type === 'employee') {
                    $query1->where('employee_code', $user_code);
                } elseif ($user_type === 'agent') {
                    $query1->orWhere('agent_code', $user_code);
                }
            });

            $query->orWhere(function ($query1) use ($user_type, $user_code, $customer_id) {
                $query1->whereRaw("customer_id IS NOT NULL");
                $query1->where('customer_id', '<>', $customer_id);

                $query1->whereDoesntHave('ext_customers', function ($query2) use ($customer_id) {
                    $query2->where('customer_id', $customer_id);
                });

                if ($user_type === 'agent' && $user_code !== '') {
                    $query1->whereHas('customer', function ($query2) use ($user_code) {
                        $query2->where('agent_code', $user_code);
                    });
                }
            });
        });



        $CONTACT->select([
            'contacts.id',
            'contacts.customer_id',
            'contacts.first_name',
            'contacts.last_name',
            'contacts.phone_work',
            'contacts.email_work',
            'customers.code as code',
            'customers.code_number as code_number',
            'customers.name as name'
        ])
            ->join('customers', 'contacts.customer_id', '=', 'customers.id');

        //        $CONTACT->with(['customer' => function ($query) {
        //            $query->select('id', 'code', 'code_number', 'name')->without(['documents', 'directions', 'hours', 'automatic_emails', 'notes']);
        //        }]);

        $CONTACT->orderBy('first_name');
        $CONTACT->orderBy('last_name');

        $contacts = $CONTACT->get();

        return response()->json(['result' => 'OK', 'count' => count($contacts), 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveExtCustomerContact(Request $request): JsonResponse
    {
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? 0;

        $CUSTOMER_CONTACT = new ContactCustomer();
        $CUSTOMER = new Customer();

        $CUSTOMER_CONTACT->updateOrCreate([
            'id' => 0
        ], [
            'customer_id' => $customer_id,
            'contact_id' => $contact_id
        ]);

        $customer = $CUSTOMER->where('id', $customer_id)
            ->select('id', 'code', 'code_number', 'name')
            ->without(['documents', 'directions', 'hours', 'automatic_emails', 'notes'])
            ->first();

        return response()->json(['result' => 'OK', 'customer' => $customer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmailContacts(): JsonResponse
    {
        $CONTACT = Contact::query();

        $CONTACT->selectRaw("id, email_work, email_personal, email_other, TRIM(CONCAT(first_name, ' ', last_name)) as name");
        $CONTACT->whereRaw("TRIM(email_work) <> ''");
        $CONTACT->orWhereRaw("TRIM(email_personal) <> ''");
        $CONTACT->orWhereRaw("TRIM(email_other) <> ''");
        $contacts = $CONTACT->orderBy('first_name')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function getUserContacts(Request $request): JsonResponse
    {
        $user_code_id = $request->user_code_id ?? null;

        $CONTACT = Contact::query();

        $contacts = $CONTACT->where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function saveUserContact(Request $request): JsonResponse
    {
        $user_code_id = $request->user_code_id ?? null;
        $id = $request->id ?? null;
        $prefix = $request->prefix ?? '';
        $first_name = $request->first_name ?? '';
        $middle_name = $request->middle_name ?? '';
        $last_name = $request->last_name ?? '';
        $suffix = $request->suffix ?? '';
        $title = $request->title ?? '';
        $company = $request->company ?? '';
        $department = $request->department ?? '';
        $email_work = $request->email_work ?? '';
        $email_personal = $request->email_personal ?? '';
        $email_other = $request->email_other ?? '';
        $primary_email = $request->primary_email ?? 'work';
        $phone_work = $request->phone_work ?? '';
        $phone_work_fax = $request->phone_work_fax ?? '';
        $phone_mobile = $request->phone_mobile ?? '';
        $phone_direct = $request->phone_direct ?? '';
        $phone_other = $request->phone_other ?? '';
        $primary_phone = $request->primary_phone ?? 'work';
        $phone_ext = $request->phone_ext ?? '';
        $country = $request->country ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip_code = $request->zip_code ?? '';
        $birthday = $request->birthday ?? '';
        $website = $request->website ?? '';
        $notes = $request->notes ?? '';

        $CONTACT = Contact::query();

        $contact = $CONTACT->updateOrCreate([
            'id' => $id
        ], [
            'user_code_id' => $user_code_id,
            'prefix' => $prefix,
            'first_name' => ucwords(trim($first_name)),
            'middle_name' => ucwords(trim($middle_name)),
            'last_name' => ucwords(trim($last_name)),
            'suffix' => $suffix,
            'title' => $title,
            'company' => $company,
            'department' => $department,
            'email_work' => strtolower($email_work),
            'email_personal' => strtolower($email_personal),
            'email_other' => strtolower($email_other),
            'primary_email' => $primary_email,
            'phone_work' => $phone_work,
            'phone_work_fax' => $phone_work_fax,
            'phone_mobile' => $phone_mobile,
            'phone_direct' => $phone_direct,
            'phone_other' => $phone_other,
            'primary_phone' => $primary_phone,
            'phone_ext' => $phone_ext,
            'country' => ucwords($country),
            'address1' => $address1,
            'address2' => $address2,
            'city' => ucwords($city),
            'state' => strtoupper($state),
            'zip_code' => $zip_code,
            'birthday' => $birthday,
            'website' => strtolower($website),
            'notes' => $notes
        ]);

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();
        $contact = Contact::where('id', $contact->id)->first();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserContact(Request $request): JsonResponse
    {
        $user_code_id = $request->user_code_id ?? null;
        $id = $request->id ?? null;

        $CONTACT = Contact::query();

        $contact = $CONTACT->where('id', $id)->first();

        if (
            $contact->customer_id || // check if contact is associated with a customer
            $contact->carrier_id || // check if contact is associated with a carrier
            $contact->factoring_company_id || // check if contact is associated with a factoring company
            $contact->division_id || // check if contact is associated with a division
            $contact->employee_id || // check if contact is associated with an employee
            $contact->agent_id || // check if contact is associated with an agent
            $contact->operator_id || // check if contact is associated with an operator
            $contact->driver_id // check if contact is associated with a driver
        ) {
            $CONTACT->where('id', $id)->update([
                'user_code_id' => null // set user_code_id to null
            ]);
        } else {
            $CONTACT->where('id', $id)->delete(); // delete contact
        }

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadUserContactAvatar(Request $request): JsonResponse
    {
        $CONTACT = Contact::query();

        $id = $_POST['id'];
        $user_code_id = $request->user_code_id ?? null;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $CONTACT->where('id', $id)->update([
            'avatar' => $new_avatar
        ]);

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();
        $contact = Contact::where('id', $id)->first();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeUserContactAvatar(Request $request): JsonResponse
    {
        $CONTACT = Contact::query();

        $id = $request->id ?? null;
        $user_code_id = $request->user_code_id ?? null;

        $contact = $CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) {
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $CONTACT->where('id', $id)->update([
            'avatar' => null
        ]);

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();
        $contact = Contact::where('id', $id)->first();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToUserContact(Request $request): JsonResponse
    {
        $user_code_id = $request->user_code_id ?? null;
        $id = $request->id ?? null;

        $CONTACT = Contact::query();

        $CONTACT->where('id', $id)->update([
            'user_code_id' => $user_code_id
        ]);

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromUserContact(Request $request): JsonResponse
    {
        $user_code_id = $request->user_code_id ?? null;
        $id = $request->id ?? null;

        $CONTACT = Contact::query();

        $CONTACT->where('id', $id)->update([
            'user_code_id' => null
        ]);

        $contacts = Contact::where('user_code_id', $user_code_id)->orderBy('first_name')->orderBy('last_name')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }
}
