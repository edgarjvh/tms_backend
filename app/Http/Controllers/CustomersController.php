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
use App\Models\PlainCustomer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\TestFixture\func;

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
                'mailing_same',
                'mailing_address',
                'mailing_customer',
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
    public function getCustomerByCode(Request $request): JsonResponse
    {
        $code = $request->code ?? '';
        $user_code = $request->user_code ?? '';

        $CUSTOMER = Customer::query();

        $CUSTOMER->whereRaw("1 = 1");
        $CUSTOMER->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'");

        if ($user_code !== '') {
            $CUSTOMER->where('agent_code', $user_code);
        }

        $customer = $CUSTOMER->with(['zip_data'])->first();

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

        if ($user_code !== '') {
            $CUSTOMER->where('agent_code', $user_code);
        }

        $CUSTOMER->orderBy('code');
        $CUSTOMER->orderBy('code_number');

        $customers = [];

        if ($with_relations === 1) {
            $CUSTOMER->with([
                'documents',
                'directions',
                'hours',
                'automatic_emails',
                'notes',
                'zip_data',
                'mailing_same',
                'mailing_address',
                'mailing_customer',
                'term',
                'division',
                'salesman'
            ]);

            $customers = $CUSTOMER->get();
        } else {
            $customers = DB::table('customers')->select()->get();
        }

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerReport(): JsonResponse
    {
        $sql = /** @lang text */
        "SELECT
            cu.id,
            CONCAT(cu.code, CASE WHEN cu.code_number = 0 THEN '' ELSE cu.code_number END) AS code,
            cu.name,
            cu.address1,
            cu.address2,
            cu.city,
            cu.state,
            cu.zip,
            TRIM(CONCAT(p1.first_name, ' ', p1.last_name)) AS contact_name1,
            (CASE
                WHEN (p1.primary_phone = 'work') THEN p1.phone_work
                WHEN (p1.primary_phone = 'fax') THEN p1.phone_work_fax
                WHEN (p1.primary_phone = 'mobile') THEN p1.phone_mobile
                WHEN (p1.primary_phone = 'direct') THEN p1.phone_direct
                WHEN (p1.primary_phone = 'other') THEN p1.phone_other
            END) AS phone1,
            (CASE
                WHEN (p1.primary_email = 'work') THEN p1.email_work
                WHEN (p1.primary_email = 'personal') THEN p1.email_personal
                WHEN (p1.primary_email = 'other') THEN p1.email_other
            END) AS email1,
            TRIM(CONCAT(p2.first_name, ' ', p2.last_name)) AS contact_name2,
            (CASE
                WHEN (p2.primary_phone = 'work') THEN p2.phone_work
                WHEN (p2.primary_phone = 'fax') THEN p2.phone_work_fax
                WHEN (p2.primary_phone = 'mobile') THEN p2.phone_mobile
                WHEN (p2.primary_phone = 'direct') THEN p2.phone_direct
                WHEN (p2.primary_phone = 'other') THEN p2.phone_other
            END) AS phone2,
            (CASE
                WHEN (p2.primary_email = 'work') THEN p2.email_work
                WHEN (p2.primary_email = 'personal') THEN p2.email_personal
                WHEN (p2.primary_email = 'other') THEN p2.email_other
            END) AS email2
        FROM customers as cu
        LEFT JOIN contacts AS p1 ON cu.id = p1.customer_id AND p1.is_primary = 1
        LEFT JOIN contact_customer AS cc ON cu.id = cc.customer_id AND cc.is_primary = 1
        LEFT JOIN contacts AS p2 ON cc.contact_id = p2.id
        ORDER BY cu.name";

        $customers = DB::select($sql);

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
        $origin = strtolower($request->search[9]['data'] ?? '');

        $customers = Customer::query();

        if (trim($code) !== '') {
            $customers->whereRaw("LOWER(CONCAT(`code`,`code_number`)) like '$code%'");
        }

        if (trim($name) !== '') {
            $customers->whereRaw("LOWER(name) like '$name%'");
        }

        if (trim($city) !== '') {
            $customers->whereRaw("LOWER(city) like '$city%'");
        }

        if (trim($state) !== '') {
            $customers->whereRaw("LOWER(state) like '$state%'");
        }

        if (trim($zip) !== '') {
            $customers->whereRaw("zip like '$zip%'");
        }

        if (trim($contact_name) !== '') {
            $customers->whereRaw("LOWER(contact_name) like '$contact_name%'");
        }

        if (trim($contact_phone) !== '') {
            $customers->whereRaw("contact_phone like '$contact_phone%'");
        }

        if (trim($email) !== '') {
            $customers->whereRaw("LOWER(email) like '$email%'");
        }

        if ($user_code !== '') {
            $customers->where('agent_code', $user_code);
        }

        if ($origin === 'agent') {
            $customers->whereRaw('agent_code <> ""');
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

        /**
         * SETTING UP THE THE QUERY STRING
         */
        $sql = /** @lang text */
            "SELECT 
                o.id,
                o.order_number,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_pickup_city,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_delivery_city,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_pickup_state,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_delivery_state,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_pickup_city,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_city,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_pickup_state,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state
            FROM orders AS o
            WHERE o.is_imported = 0
                AND o.bill_to_customer_id = ?
                OR (EXISTS (SELECT * FROM order_routing AS r WHERE o.id = r.order_id AND (EXISTS (SELECT * FROM order_pickups AS p WHERE r.pickup_id = p.id AND p.customer_id = ?) OR EXISTS (SELECT * FROM order_deliveries AS d WHERE r.delivery_id = d.id AND d.customer_id = ?)) ORDER BY r.id ASC limit 1))
                OR (EXISTS (SELECT * FROM order_routing AS r WHERE o.id = r.order_id AND (EXISTS (SELECT * FROM order_pickups AS p WHERE r.pickup_id = p.id AND p.customer_id = ?) OR EXISTS (SELECT * FROM order_deliveries AS d WHERE r.delivery_id = d.id AND d.customer_id = ?)) ORDER BY r.id DESC limit 1))
            ORDER BY o.order_number DESC";

        $params = [$id, $id, $id, $id, $id];

        $orders = DB::select($sql, $params);

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

        $mailing_address_id = $request->mailing_address_id ?? null;
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;
        $mailing_customer_id = $request->mailing_customer_id ?? null;
        $mailing_customer_contact_id = $request->mailing_customer_contact_id ?? null;
        $mailing_customer_contact_primary_phone = $request->mailing_customer_contact_primary_phone ?? 'work';
        $mailing_customer_contact_primary_email = $request->mailing_customer_contact_primary_email ?? 'work';

        $codeExist = [];

        // get incoming customer by id, code and code_number
        $curCustomer = $CUSTOMER
            ->where('id', $id)
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
                'credit_limit_total' => $credit_limit_total,
                'mailing_address_id' => $mailing_address_id,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
                'mailing_customer_id' => $mailing_customer_id,
                'mailing_customer_contact_id' => $mailing_customer_contact_id,
                'mailing_customer_contact_primary_phone' => $mailing_customer_contact_primary_phone,
                'mailing_customer_contact_primary_email' => $mailing_customer_contact_primary_email
            ]);

        if ($user_code !== '') {
            $CUSTOMER_MAILING_ADDRESS = new CustomerMailingAddress();

            $CUSTOMER_MAILING_ADDRESS->updateOrCreate([
                'customer_id' => $customer->id
            ], [
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
                'mailing_same',
                'mailing_address',
                'mailing_customer',
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

                if (preg_match('/[a-z]/i', $zip)) {
                    $zip = str_replace("-", "", $zip);
                    $len = strlen($zip);
                    $rem = $len - 6;

                    if ($rem > 0) {
                        $zip = substr_replace($zip, "", 0, $rem);
                    }

                    $zip = substr_replace($zip, " ", 3, 0);
                } else if (preg_match('/[0-9]/', $zip)) {
                    $zip = explode("-", $zip)[0];

                    $len = strlen($zip);

                    if ($len < 5) {
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
            'mailing_same',
            'mailing_address',
            'mailing_customer',
            'term',
            'division',
            'salesman'
        ])->get();

        return response()->json(['result' => 'OK', 'customers' => $customers]);
    }

    /**
     * @throws Exception
     */
    public function customerTest(Request $request): JsonResponse
    {
        $CUSTOMER = new PlainCustomer();
        $MAILING_ADDRESS = new CustomerMailingAddress();

        $collection = collect();

        $customers = $CUSTOMER->selectRaw('id, code, code_number, name, address1, address2, city, state, zip, concat("customer") as type')->get();
        $addresses = $MAILING_ADDRESS->where('code', '<>', '')->selectRaw('id, code, code_number, name, address1, address2, city, state, zip, concat("mailing") as type')->get();

        foreach ($customers as $customer)
            $collection->push($customer);

        foreach ($addresses as $address)
            $collection->push($address);

        return response()->json(['result' => 'OK', 'customers' => $collection]);
    }
}
