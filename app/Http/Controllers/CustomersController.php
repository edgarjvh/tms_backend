<?php

namespace App\Http\Controllers;

use App\Models\AutomaticEmail;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomerHour;
use App\Models\CustomerMailingAddress;
use App\Models\Direction;
use App\Models\Note;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerById(Request $request): JsonResponse
    {
        $CUSTOMER = Customer::query();
        $id = $request->id ?? 0;

        $customer = $CUSTOMER->where('id', $id)
            ->with([
                'contacts',
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address',
                'orders'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'customer' => $customer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customers(Request $request): JsonResponse
    {
        $CUSTOMER = new Customer();

        $code = $request->code ?? '';
        $name = $request->name ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $email = $request->email ?? '';
        $with_relations = $request->with_relations ?? 1;

        if ($with_relations === 1) {
            $customers = $CUSTOMER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("LOWER(name) like '%$name%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("LOWER(contact_name) like '%$contact_name%'")
                ->whereRaw("contact_phone like '%$contact_phone%'")
                ->whereRaw("LOWER(email) like '%$email%'")
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts',
                    'documents',
                    'directions',
                    'hours',
                    'automatic_emails',
                    'notes',
                    'zip_data',
                    'mailing_address'
                ])
                ->get();
        } else {
            $customers = $CUSTOMER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("LOWER(name) like '%$name%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("LOWER(contact_name) like '%$contact_name%'")
                ->whereRaw("contact_phone like '%$contact_phone%'")
                ->whereRaw("LOWER(email) like '%$email%'")
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts'
                ])
                ->get();
        }


        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerSearch(Request $request): JsonResponse
    {
        $CUSTOMER = new Customer();

        $name = $request->search[0]['data'] ?? '';
        $city = $request->search[1]['data'] ?? '';
        $state = $request->search[2]['data'] ?? '';
        $zip = $request->search[3]['data'] ?? '';
        $contact_name = $request->search[4]['data'] ?? '';
        $contact_phone = $request->search[5]['data'] ?? '';
        $email = $request->search[6]['data'] ?? '';

        $customers = $CUSTOMER->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("LOWER(contact_name) like '%$contact_name%'")
            ->whereRaw("contact_phone like '%$contact_phone%'")
            ->whereRaw("LOWER(email) like '%$email%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->with([
                'contacts',
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address'
            ])
            ->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerOrders(Request $request)
    {
        $id = $request->id ?? 0;

        $ORDER = Order::query();

        $ORDER->whereRaw("1 = 1");
        $ORDER->whereHas('bill_to_company', function ($query1) use ($id) {
            $query1->where('id', $id);
        });
        $ORDER->orWhereHas('pickups', function ($query1) use ($id) {
            $query1->whereHas('customer', function ($query2) use ($id) {
                $query2->where('id', $id);
            });
        });
        $ORDER->orWhereHas('deliveries', function ($query1) use ($id) {
            $query1->whereHas('customer', function ($query2) use ($id) {
                $query2->where('id', $id);
            });
        });

        $ORDER->with([
            'bill_to_company',
            'pickups',
            'deliveries',
            'routing'
        ]);

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomer(Request $request): JsonResponse
    {
        $CUSTOMER = new Customer();
        $CUSTOMER_CONTACT = new Contact();

        $id = $request->id ?? '';
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
        $contact_phone_ext = $request->contact_phone_ext ?? ($request->ext ?? '');
        $email = $request->email ?? '';

        $curCustomer = $CUSTOMER->where('id', $id)->first();

        if ($curCustomer) {
            if ($curCustomer->code !== $code) {
                $codeExist = $CUSTOMER->where('id', '<>', $id)
                    ->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $CUSTOMER->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else {
            $codeExist = $CUSTOMER->where('code', $code)->get();

            if (count($codeExist) > 0) {
                $max_code_number = $CUSTOMER->where('code', $code)->max('code_number');
                $code_number = $max_code_number + 1;
            } else {
                $code_number = 0;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $customer = $CUSTOMER->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => strtoupper($code),
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email)
            ]);

        if ($with_contact) {
            $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer->id)->get();

            $contact_name_splitted = explode(" ", $contact_name);
            $contact_first = $contact_name_splitted[0];
            $contact_last = '';

            if (count($contact_name_splitted) > 0) {
                for ($i = 1; $i < count($contact_name_splitted); $i++) {
                    $contact_last .= $contact_name_splitted[$i] . " ";
                }
            }

            $contact_last = trim($contact_last);

            if (count($contacts) === 0) {
                $contact = new Contact();
                $contact->customer_id = $customer->id;
                $contact->first_name = trim($contact_first);
                $contact->last_name = trim($contact_last);
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

                $CUSTOMER->where('id', $customer->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CUSTOMER_CONTACT->where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $contact_phone_ext,
                        'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        $newCustomer = $CUSTOMER->where('id', $customer->id)
            ->with([
                'contacts',
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address'
            ])->first();

        return response()->json(['result' => 'OK', 'customer' => $newCustomer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCustomerImport(Request $request)
    {
        $CUSTOMER = new Customer();
        $CUSTOMER_CONTACT = new Contact();

        $id = 0;
        $code = $request->code ?? '';
        $code_number = $request->codeNumber ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact ?? '';
        $contact_phone = $request->phone ?? '';
        $contact_phone_ext = $request->ext ?? '';
        $email = $request->email ?? '';
        $hours_open = $request->hoursOpen ?? '';
        $hours_close = $request->hoursClose ?? '';
        $bill_to_code = $request->billToCode ?? '';
        $bill_to_code_number = $request->billToCodeNumber ?? 0;

//        $curCustomer = $CUSTOMER->where('id', $id)->first();
//
//        if ($curCustomer) {
//            if ($curCustomer->code !== $code) {
//                $codeExist = $CUSTOMER->where('id', '<>', $id)
//                    ->where('code', $code)->get();
//
//                if (count($codeExist) > 0) {
//                    $max_code_number = $CUSTOMER->where('code', $code)->max('code_number');
//                    $code_number = $max_code_number + 1;
//                } else {
//                    $code_number = 0;
//                }
//            }
//        } else {
//            $codeExist = $CUSTOMER->where('code', $code)->get();
//
//            if (count($codeExist) > 0) {
//                $max_code_number = $CUSTOMER->where('code', $code)->max('code_number');
//                $code_number = $max_code_number + 1;
//            } else {
//                $code_number = 0;
//            }
//        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $customer = $CUSTOMER->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => strtoupper($code),
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email)
            ]);

        if (!empty($bill_to_code)) {
            CustomerMailingAddress::updateOrCreate([
                'customer_id' => $customer->id
            ],
                [
                    'bill_to_code' => $bill_to_code,
                    'bill_to_code_number' => $bill_to_code_number
                ]);
        }

        if ($with_contact) {
            $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer->id)->get();

            $contact_name_splitted = explode(" ", $contact_name);
            $contact_first = $contact_name_splitted[0];
            $contact_last = '';

            if (count($contact_name_splitted) > 0) {
                for ($i = 1; $i < count($contact_name_splitted); $i++) {
                    $contact_last .= $contact_name_splitted[$i] . " ";
                }
            }

            $contact_last = trim($contact_last);

            if (count($contacts) === 0) {
                $contact = new Contact();
                $contact->customer_id = $customer->id;
                $contact->first_name = trim($contact_first);
                $contact->last_name = trim($contact_last);
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

                $CUSTOMER->where('id', $customer->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CUSTOMER_CONTACT->where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $contact_phone_ext,
                        'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        $CUSTOMER_HOUR = new CustomerHour();

        if ($hours_open !== '' || $hours_close !== '') {
            $customer_hours = $CUSTOMER_HOUR->updateorCreate([
                'customer_id' => $customer->id
            ],
                [
                    'hours_open' => $hours_open,
                    'hours_close' => $hours_close
                ]);
        }

        $newCustomer = $CUSTOMER->where('id', $customer->id)->first();

        return response()->json(['result' => 'OK', 'customer' => $newCustomer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCustomerImport2(Request $request)
    {
        $customerList = $request->list ?? [];
        $savedCustomers = [];

        if (count($customerList) > 0) {
            for ($i = 0; $i < count($customerList); $i++) {
                $currentCustomer = $customerList[$i];

//                $CUSTOMER = new Customer();
//                $CUSTOMER_CONTACT = new Contact();

                $id = 0;
                $code = $currentCustomer['code'] ?? '';
                $code_number = 0;
                $name = $currentCustomer['name'] ?? '';
                $address1 = $currentCustomer['address1'] ?? '';
                $address2 = $currentCustomer['address2'] ?? '';
                $city = $currentCustomer['city'] ?? '';
                $state = $currentCustomer['state'] ?? '';
                $zip = $currentCustomer['zip'] ?? '';
                $contact_name = $currentCustomer['contact'] ?? '';
                $contact_phone = $currentCustomer['phone'] ?? '';
                $contact_phone_ext = $currentCustomer['ext'] ?? '';
                $email = $currentCustomer['email'] ?? '';

//                $curCustomer = Customer::where('id', $id)->first();

                $codeExist = Customer::where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = Customer::where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                } else {
                    $code_number = 0;
                }

                $with_contact = true;

                if (trim($contact_name) === '' || trim($contact_phone) === '') {
                    $with_contact = false;
                }

                $customer = Customer::updateOrCreate([
                    'id' => $id
                ],
                    [
                        'code' => strtoupper($code),
                        'code_number' => $code_number,
                        'name' => $name,
                        'address1' => $address1,
                        'address2' => $address2,
                        'city' => $city,
                        'state' => strtoupper($state),
                        'zip' => $zip,
                        'contact_name' => $contact_name,
                        'contact_phone' => $contact_phone,
                        'ext' => $contact_phone_ext,
                        'email' => strtolower($email)
                    ]);

                if ($with_contact) {
                    $contacts = Contact::where('customer_id', $customer->id)->get();

                    $contact_name_splitted = explode(" ", $contact_name);
                    $contact_first = $contact_name_splitted[0];
                    $contact_last = '';

                    if (count($contact_name_splitted) > 0) {
                        for ($i = 1; $i < count($contact_name_splitted); $i++) {
                            $contact_last .= $contact_name_splitted[$i] . " ";
                        }
                    }

                    $contact_last = trim($contact_last);

                    if (count($contacts) === 0) {
                        $contact = new Contact();
                        $contact->customer_id = $customer->id;
                        $contact->first_name = trim($contact_first);
                        $contact->last_name = trim($contact_last);
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
                    } elseif (count($contacts) === 1) {

                        $contact = $contacts[0];
                        if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                            Contact::where('id', $contact->id)->update([
                                'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                                'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                                'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                                'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                                'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                                'phone_ext' => $contact_phone_ext,
                                'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                                'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                                'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                            ]);
                        }
                    }
                }

                $savedCustomers[] = $customer;
            }

            return response()->json(['result' => 'OK', 'customers' => $savedCustomers]);
        } else {
            return response()->json(['result' => 'NO CUSTOMERS']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerPayload(Request $request): JsonResponse
    {
        $CUSTOMER_CONTACT = new Contact();
        $CUSTOMER_NOTE = new Note();
        $CUSTOMER_DIRECTION = new Direction();
        $CUSTOMER_HOUR = new CustomerHour();
        $AUTOMATIC_EMAIL = new AutomaticEmail();

        $customer_id = $request->customer_id ?? 0;

        $contacts = $CUSTOMER_CONTACT->where('customer_id', $customer_id)->orderBy('last_name')->get();
        $notes = $CUSTOMER_NOTE->where('customer_id', $customer_id)->get();
        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();
        $customer_hours = $CUSTOMER_HOUR->where('customer_id', $customer_id)->first();
        $automatic_emails = $AUTOMATIC_EMAIL->where('customer_id', $customer_id)->first();

        return response()->json([
            'result' => 'OK',
            'contacts' => $contacts,
            'notes' => $notes,
            'directions' => $directions,
            'customer_hours' => $customer_hours,
            'automatic_emails' => $automatic_emails
        ]);
    }

    /**
     * @throws Exception
     */
    public function getFullCustomers(): JsonResponse
    {
        $CUSTOMER = new Customer();

        $customers = $CUSTOMER->with([
            'contacts',
            'documents',
            'directions',
            'hours',
            'automatic_emails',
            'notes',
            'zip_data',
            'mailing_address'
        ])->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }
}
