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
use Illuminate\Support\Facades\DB;

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
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address',
                'term',
                'division',
                'salesman'
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
        $CUSTOMER = Customer::query();

        $code = $request->code ?? '';
        $name = $request->name ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $email = $request->email ?? '';
        $with_relations = $request->with_relations ?? 1;
        $user_code = $request->user_code ?? '';

        $CUSTOMER->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("LOWER(contact_name) like '%$contact_name%'")
            ->whereRaw("contact_phone like '%$contact_phone%'")
            ->whereRaw("LOWER(email) like '%$email%'");

        if ($user_code !== ''){
            $CUSTOMER->where('agent_code', $user_code);
        }

        $CUSTOMER->orderBy('code');
        $CUSTOMER->orderBy('code_number');

        if ($with_relations === 1) {
            $CUSTOMER->with([
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address',
                'term',
                'division',
                'salesman'
            ]);
        } else {
            $CUSTOMER->with([
                'contacts'
            ]);
        }

        $customers = $CUSTOMER->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function customerSearch(Request $request): JsonResponse
    {
        $code = strtolower($request->search[0]['data'] ?? '');
        $name = strtolower($request->search[1]['data'] ?? '');
        $city = strtolower($request->search[2]['data'] ?? '');
        $state = strtolower($request->search[3]['data'] ?? '');
        $zip = strtolower($request->search[4]['data'] ?? '');
        $contact_name = strtolower($request->search[5]['data'] ?? '');
        $contact_phone = strtolower($request->search[6]['data'] ?? '');
        $email = strtolower($request->search[7]['data'] ?? '');
        $user_code = strtolower($request->search[8]['data'] ?? '');

        $customers = DB::table('customers');
        $customers->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_name) like '$contact_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'");

        if ($user_code !== '') {
            $customers->where('agent_code', $user_code);
        }

        $customers->orderBy('code');
        $customers->orderBy('code_number');
        $customers = $customers->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerOrders(Request $request)
    {
        $id = $request->id ?? 0;

        $ORDER = Order::query()->whereRaw('orders.is_imported = 0');

        $ORDER->where(function ($query) use ($id){
            $query->whereHas('bill_to_company', function ($query1) use ($id) {
                $query1->where('id', $id);
            });
            $query->orWhereHas('pickups', function ($query1) use ($id) {
                $query1->whereHas('customer', function ($query2) use ($id) {
                    $query2->where('id', $id);
                });
            });
            $query->orWhereHas('deliveries', function ($query1) use ($id) {
                $query1->whereHas('customer', function ($query2) use ($id) {
                    $query2->where('id', $id);
                });
            });
        });


        $ORDER->select([
            'id',
            'order_number',
            'is_imported'
        ]);

        $ORDER->with([
            'bill_to_company',
            'pickups',
            'deliveries',
            'routing'
        ]);


        $ORDER->orderBy('id', 'desc');

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
        $full_code = $code . $code_number;
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
        $bill_to_code = $request->bill_to_code ?? null;
        $term_id = $request->term_id ?? null;
        $credit_limit_total = $request->credit_limit_total ?? 0.00;
        $division_id = $request->division_id ?? null;
        $salesman_id = $request->salesman_id ?? null;
        $agent_code = strtoupper($request->agent_code ?? '');
        $fid = $request->fid ?? '';
        $user_code = $request->user_code ?? '';
        $codeExist = [];

        // get incoming customer by id, code and code_number
        $curCustomer = $CUSTOMER
            ->where('id',$id)
            ->whereRaw("CONCAT(code,code_number) = '$full_code'")
            ->first();

        if ($curCustomer) { // if customer exists (to update)
            if ($curCustomer->code !== $code) { // if code has been changed
                $codeExist = $CUSTOMER
                    ->where('id', '<>', $id)
                    ->where('code', $code)
                    ->orderBy('code_number', 'desc')
                    ->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[0]->code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else { // if customer doesn't exist (to add new)
            $codeExist = $CUSTOMER
                ->where('code', $code)
                ->orderBy('code_number', 'desc')
                ->get();

            if (count($codeExist) > 0) {
                $code_number = $codeExist[0]->code_number + 1;
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
                'name' => ucwords($name),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => ucwords($contact_name),
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email),
                'bill_to_code' => $bill_to_code,
                'division_id' => $division_id,
                'agent_code' => $agent_code,
                'salesman_id' => $salesman_id,
                'fid' => $fid,
                'term_id' => $term_id,
                'credit_limit_total' => $credit_limit_total
            ]);

        if ($user_code !== ''){
            $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();

            $CUSTOMER_MAILING_ADDRESS->updateOrCreate([
                'customer_id' => $customer->id
            ],[
                'agent_code' => strtoupper($user_code)
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
                $contact->first_name = ucwords(trim($contact_first));
                $contact->last_name = ucwords(trim($contact_last));
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $contact_phone_ext;
                $contact->email_work = $email;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = ucwords($city);
                $contact->state = strtoupper($state);
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

        $CUSTOMER_CONTACT->where('customer_id', $customer->id)->update([
           'address1' => $customer->address1,
           'address2' => $customer->address2,
           'city' => $customer->city,
           'state' => $customer->state,
           'zip_code' => $customer->zip,
        ]);

        $newCustomer = $CUSTOMER->where('id', $customer->id)
            ->with([
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_address',
                'term',
                'division',
                'salesman'
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
        $list = $request->list ?? [];

        if (count($list) > 0) {
            for ($i = 0; $i < count($list); $i++) {
                $item = $list[$i];

                $code = $item['code'] ?? null;
                $code_number = $item['codeNumber'] ?? 0;
                $name = $item['name'] ?? '';
                $address1 = $item['address1'] ?? '';
                $address2 = $item['address2'] ?? '';
                $city = $item['city'] ?? '';
                $state = $item['state'] ?? '';
                $zip = $item['zip'] ?? '';
                $contact_name = $item['contact'] ?? '';
                $contact_first_name = $item['contactFirstName'] ?? '';
                $contact_last_name = $item['contactLastName'] ?? '';
                $contact_phone = $item['phone'] ?? '';
                $ext = $item['ext'] ?? '';
                $email = $item['email'] ?? '';
                $hours_open = $item['hoursOpen'] ?? '';
                $hours_close = $item['hoursClose'] ?? '';
                $bill_to_code = $item['billToCode'] ?? null;
                $bill_to_code_number = $item['billToCodeNumber'] ?? 0;

                $customer_id = 0;

                $zip = str_replace(" ", "", $zip);

                if (preg_match('/[a-z]/i', $zip)){
                    $zip = str_replace("-", "", $zip);
                    $len = strlen($zip);
                    $rem = $len - 6;

                    if ($rem > 0){
                        $zip = substr_replace($zip, "", 0, $rem);
                    }

                    $zip = substr_replace($zip, " ", 3, 0);
                }else if (preg_match('/[0-9]/', $zip)){
                    $zip = explode("-", $zip)[0];

                    $len = strlen($zip);

                    if ($len < 5){
                        $zip = str_pad($zip, 5, "0", STR_PAD_LEFT);
                    }
                }

                try {
                    $saved_customer = Customer::updateOrCreate([
                        'id' => 0
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
                            'ext' => $ext,
                            'email' => strtolower($email)
                        ]);

                    $customer_id = $saved_customer->id;
                } catch (Throwable|Exception $e) {
                    $customer_id = 0;
                }

                if ($customer_id > 0) {
                    try {
                        if (!empty($bill_to_code)) {
                            CustomerMailingAddress::updateOrCreate([
                                'customer_id' => $customer_id
                            ],
                                [
                                    'bill_to_code' => $bill_to_code,
                                    'bill_to_code_number' => $bill_to_code_number
                                ]);
                        }
                    } catch (Throwable|Exception $e) {

                    }

                    try {
                        $saved_contact = Contact::updateOrCreate([
                            'id' => 0
                        ], [
                            'customer_id' => $customer_id,
                            'first_name' => $contact_first_name,
                            'last_name' => $contact_last_name,
                            'phone_work' => $contact_phone,
                            'phone_ext' => $ext,
                            'email_work' => $email,
                            'address1' => $address1,
                            'address2' => $address2,
                            'city' => $city,
                            'state' => $state,
                            'zip_code' => $zip,
                            'is_primary' => 1
                        ]);

                        Customer::where('id', $customer_id)->update([
                            'primary_contact_id' => $saved_contact->id
                        ]);
                    } catch (Throwable|Exception $e) {

                    }

                    try {
                        if ($hours_open !== '' || $hours_close !== '') {
                            CustomerHour::updateorCreate([
                                'customer_id' => $customer_id
                            ],
                                [
                                    'hours_open' => $hours_open,
                                    'hours_close' => $hours_close
                                ]);
                        }
                    } catch (Throwable|Exception $e) {

                    }
                }

            }

            return response()->json(['result' => 'OK']);
        } else {
            return response()->json(['result' => 'NO LIST']);
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
            'documents',
            'directions',
            'hours',
            'automatic_emails',
            'notes',
            'zip_data',
            'mailing_address',
            'term',
            'division',
            'salesman'
        ])->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @throws Exception
     */
    public function customerTest(Request $request): JsonResponse{
        $CUSTOMER = new Customer();

        $customer_id = $request->customer_id ?? 0;

        $customer = $CUSTOMER->where('id', $customer_id)->first();

        return response()->json(['result' => 'OK', 'customers' => $customer->contacts]);
    }
}
