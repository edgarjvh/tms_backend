<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\AgentContact;
use Illuminate\Support\Facades\DB;

class AgentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentById(Request $request): JsonResponse
    {
        $AGENT = Agent::query();
        $id = $request->id ?? 0;

        $agent = $AGENT->where('id', $id)
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'division',
                'drivers'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'agent' => $agent]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgents(Request $request): JsonResponse
    {
        $AGENT = new Agent();
        $code = $request->code ?? '';
        $name = $request->name ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_first_name = $request->contact_first_name ?? '';
        $contact_last_name = $request->contact_last_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $email = $request->email ?? '';
        $with_relations = $request->with_relations ?? 1;

        if ($with_relations === 1) {
            $agents = $AGENT->whereRaw("1 = 1")
                ->whereRaw("UPPER(code) like '$code%'")
                ->whereRaw("LOWER(name) like '$name%'")
                ->whereRaw("LOWER(city) like '$city%'")
                ->whereRaw("LOWER(state) like '$state%'")
                ->whereRaw("zip like '$zip%'")
                ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
                ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
                ->whereRaw("contact_phone like '$contact_phone%'")
                ->whereRaw("LOWER(email) like '$email%'")
                ->orderBy('id')
                ->with([
                    'contacts',
                    'documents',
                    'hours',
                    'notes',
                    'mailing_address',
                    'division',
                    'drivers'
                ])
                ->get();
        } else {
            $agents = $AGENT->whereRaw("1 = 1")
                ->whereRaw("UPPER(code) like '$code%'")
                ->whereRaw("LOWER(name) like '$name%'")
                ->whereRaw("LOWER(city) like '$city%'")
                ->whereRaw("LOWER(state) like '$state%'")
                ->whereRaw("zip like '$zip%'")
                ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
                ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
                ->whereRaw("contact_phone like '$contact_phone%'")
                ->whereRaw("LOWER(email) like '$email%'")
                ->orderBy('id')
                ->with([
                    'contacts',
                    'division'
                ])
                ->get();
        }


        return response()->json(['result' => 'OK', 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentReport(Request $request): JsonResponse
    {
        $agent_id = $request->agent_id ?? 0;

        $params = [];

        $sql =
            /** @lang text */
            "SELECT
            ca.company_id,
            ca.id,
            ca.code,
            ca.name,
            ca.address1,
            ca.address2,
            ca.city,
            ca.state,
            ca.zip,
            TRIM(CONCAT(p1.first_name, ' ', p1.last_name)) AS contact_name,
            (CASE
                WHEN (p1.primary_phone = 'work') THEN p1.phone_work
                WHEN (p1.primary_phone = 'fax') THEN p1.phone_work_fax
                WHEN (p1.primary_phone = 'mobile') THEN p1.phone_mobile
                WHEN (p1.primary_phone = 'direct') THEN p1.phone_direct
                WHEN (p1.primary_phone = 'other') THEN p1.phone_other
            END) AS phone,
            (CASE
                WHEN (p1.primary_email = 'work') THEN p1.email_work
                WHEN (p1.primary_email = 'personal') THEN p1.email_personal
                WHEN (p1.primary_email = 'other') THEN p1.email_other
            END) AS email
        FROM company_agents as ca
        LEFT JOIN contacts AS p1 ON ca.id = p1.agent_id AND p1.is_primary = 1 ";

        if ($agent_id > 0) {
            $sql .=
            /** @lang text */
            "WHERE ca.id = ? ";
            $params = [$agent_id];
        }

        $sql .=
            /** @lang text */
            "ORDER BY ca.name";

        $agents = DB::select($sql);

        return response()->json(['result' => 'OK', 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function agentSearch(Request $request): JsonResponse
    {
        $AGENT = new Agent();

        $code = $request->search[0]['data'] ?? '';
        $name = $request->search[1]['data'] ?? '';
        $city = $request->search[2]['data'] ?? '';
        $state = $request->search[3]['data'] ?? '';
        $zip = $request->search[4]['data'] ?? '';
        $contact_first_name = $request->search[5]['data'] ?? '';
        $contact_last_name = $request->search[6]['data'] ?? '';
        $contact_phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        $agents = $AGENT->whereRaw("1 = 1")
            ->whereRaw("UPPER(code) like '$code%'")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
            ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'")
            ->orderBy('id')
            ->get();

        return response()->json(['result' => 'OK', 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentOrders(Request $request)
    {
        $id = $request->id ?? 0;

        $agent = Agent::find($id);

        $code = $agent->code;

        /**
         * SETTING UP THE THE QUERY STRING
         */
        $sql =
            /** @lang text */
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
				AND o.is_cancelled = 0
                AND (EXISTS (SELECT * FROM user_codes AS uc WHERE o.user_code_id = uc.id AND o.agent_id = ?)
                    OR EXISTS (SELECT * FROM customers AS cu WHERE o.bill_to_customer_id = cu.id AND cu.agent_code = ?))
            ORDER BY o.order_number DESC";

        $params = [$id, $code];

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgent(Request $request): JsonResponse
    {
        $AGENT = new Agent();
        $AGENT_CONTACT = new AgentContact();

        $id = $request->id ?? '';
        $company_id = $request->company_id ?? '';
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_first_name = $request->contact_first_name ?? '';
        $contact_last_name = $request->contact_last_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? ($request->ext ?? '');
        $email = $request->email ?? '';
        $added_date = $request->added_date ?? '';
        $termination_date = $request->termination_date ?? '';
        $regional_manager = $request->regional_manager ?? '';
        $division_id = $request->division_id ?? null;
        $fid = $request->fid ?? '';
        $agent_pay_brokerage = $request->agent_pay_brokerage ?? 0;
        $agent_pay_et3 = $request->agent_pay_et3 ?? 0;
        $agent_pay_outside_broker = $request->agent_pay_outside_broker ?? 0;
        $agent_pay_company_trucks = $request->agent_pay_company_trucks ?? 0;
        $agent_own_units = $request->agent_own_units ?? 0;
        $agent_pay_own_trucks = $request->agent_pay_own_trucks ?? 0;

        $with_contact = true;

        if (trim($contact_first_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $agent = $AGENT->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'company_id' => $company_id,
                'name' => ucwords($name),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_first_name' => ucwords($contact_first_name),
                'contact_last_name' => ucwords($contact_last_name),
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email),
                'added_date' => $added_date,
                'termination_date' => $termination_date,
                'regional_manager' => $regional_manager,
                'division_id' => $division_id,
                'fid' => $fid,
                'agent_pay_brokerage' => $agent_pay_brokerage,
                'agent_pay_et3' => $agent_pay_et3,
                'agent_pay_outside_broker' => $agent_pay_outside_broker,
                'agent_pay_company_trucks' => $agent_pay_company_trucks,
                'agent_own_units' => $agent_own_units,
                'agent_pay_own_trucks' => $agent_pay_own_trucks
            ]
        );

        if ($with_contact) {
            $contacts = $AGENT_CONTACT->where('agent_id', $agent->id)->get();

            if (count($contacts) === 0) {
                $contact = new AgentContact();
                $contact->agent_id = $agent->id;
                $contact->first_name = ucwords(trim($contact_first_name));
                $contact->last_name = ucwords(trim($contact_last_name));
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $contact_phone_ext;
                $contact->email_work = strtolower($email);
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = ucwords($city);
                $contact->state = strtoupper($state);
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->save();

                $AGENT->where('id', $agent->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first_name && $contact->last_name === $contact_last_name) {

                    $AGENT_CONTACT->where('id', $contact->id)->update([
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

        $newAgent = $AGENT->where('id', $agent->id)
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'division',
                'drivers'
            ])->first();

        $agents = $AGENT->where('company_id', $company_id)->with(['contacts'])
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'division',
                'drivers'
            ])->get();

        return response()->json(['result' => 'OK', 'agent' => $newAgent, 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentRevenue(Request $request): JsonResponse
    {
        $agent_code = trim($request->agent_code ?? '');
        $load_type_id = trim($request->load_type_id ?? -1);
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $params = [];

        /**
         * SETTING UP THE THE QUERY STRING
         */
        $sql =
            /** @lang text */
            "SELECT * FROM (
                SELECT
                    o.id,
                    o.order_number,
                    o.bill_to_customer_id,
                    c.code,
                    c.code_number,
                    c.name,
                    c.city,
                    c.state,
                    o.order_date_time,
                    o.customer_check_number,
                    o.agent_date_paid,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating,
                    o.is_cancelled,
                    ca.id AS agent_id,
                    ca.code AS agent_code,
                    ca.name AS agent_name,
                    lt.name AS load_type,
                    ((((SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) - (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id)) * o.agent_commission) / 100) AS agent_commission
                FROM orders AS o
                INNER JOIN company_agents as ca ON o.agent_code = ca.code
                INNER JOIN customers AS c ON o.bill_to_customer_id = c.id
                INNER JOIN load_types AS lt ON o.load_type_id = lt.id
                WHERE o.is_template = 0 ";

        /**
         * CHECKING THE AGENT CODE
         */
        if ($agent_code !== '') {
            $sql .=
                /** @lang text */
                "AND LOWER(o.agent_code) = ? ";

            $params[] = $agent_code;
        }
        // else {
        //     $sql .=
        //             /** @lang text */
        //             "AND o.agent_code <> '' ";
        // }

        /**
         * CHECKING THE LOAD TYPE ID
         */
        if ($load_type_id > -1) {
            $sql .=
                /** @lang text */
                "AND o.load_type_id = ? ";

            $params[] = $load_type_id;
        }

        /**
         * CHECKING THE DATE PARAMETERS
         */
        if ($date_start !== '' && $date_end !== '') {
            $sql .=
                /** @lang text */
                "AND (o.order_date_time BETWEEN STR_TO_DATE(?, '%m/%d/%Y') AND STR_TO_DATE(?, '%m/%d/%Y')) ";

            $params[] = $date_start;
            $params[] = $date_end;
        } else {
            if ($date_start !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.order_date_time >= STR_TO_DATE(?, '%m/%d/%Y')) ";

                $params[] = $date_start;
            } elseif ($date_end !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.order_date_time <= STR_TO_DATE(?, '%m/%d/%Y')) ";

                $params[] = $date_end;
            }
        }

        /**
         * CHECKING THE CITY ORIGIN
         */
        if ($city_origin !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(city) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(city) = ?))) ORDER BY id ASC limit 1)) ";

            $params[] = $city_origin;
            $params[] = $city_origin;
        }

        /**
         * CHECKING THE CITY DESTINATION
         */
        if ($city_destination !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(city) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(city) = ?))) ORDER BY id DESC limit 1)) ";

            $params[] = $city_destination;
            $params[] = $city_destination;
        }

        /**
         * CHECKING THE STATE ORIGIN
         */
        if ($state_origin !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(state) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(state) = ?))) ORDER BY id ASC limit 1)) ";

            $params[] = $state_origin;
            $params[] = $state_origin;
        }

        /**
         * CHECKING THE STATE DESTINATION
         */
        if ($state_destination !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(state) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(state) = ?))) ORDER BY id DESC limit 1)) ";

            $params[] = $state_destination;
            $params[] = $state_destination;
        }

        /**
         * CHECKING THE ZIP ORIGIN
         */
        if ($zip_origin !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(zip) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(zip) = ?))) ORDER BY id ASC limit 1)) ";

            $params[] = $zip_origin;
            $params[] = $zip_origin;
        }

        /**
         * CHECKING THE ZIP DESTINATION
         */
        if ($zip_destination !== '') {
            $sql .=
                /** @lang text */
                "AND (EXISTS (SELECT * FROM order_routing WHERE o.id = order_routing.order_id
                AND (EXISTS (SELECT * FROM order_pickups WHERE order_routing.pickup_id = order_pickups.id
                AND EXISTS (SELECT * FROM customers WHERE order_pickups.customer_id = customers.id
                AND LOWER(zip) = ?))
                OR EXISTS (SELECT * FROM order_deliveries WHERE order_routing.delivery_id = order_deliveries.id
                AND EXISTS (SELECT * FROM customers WHERE order_deliveries.customer_id = customers.id
                AND LOWER(zip) = ?))) ORDER BY id DESC limit 1)) ";

            $params[] = $zip_destination;
            $params[] = $zip_destination;
        }

        /**
         * THE END OF THE QUERY GROUPING BY THE order_number AND THEN ORDERING BY order_date_time DESC
         */
        $sql .=
            /** @lang text */
            ") AS result ORDER BY result.agent_code;";

        $orders = DB::select($sql, $params);
        // $orders = DB::toSql($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentAchWiringInfo(Request $request): JsonResponse
    {
        $agent_id = $request->agent_id ?? 0;
        $ach_banking_info = $request->ach_banking_info ?? '';
        $ach_account_info = $request->ach_account_info ?? '';
        $ach_aba_routing = $request->ach_aba_routing ?? '';
        $ach_remittence_email = $request->ach_remittence_email ?? '';
        $ach_type = $request->ach_type ?? 'checking';
        $wiring_banking_info = $request->wiring_banking_info ?? '';
        $wiring_account_info = $request->wiring_account_info ?? '';
        $wiring_aba_routing = $request->wiring_aba_routing ?? '';
        $wiring_remittence_email = $request->wiring_remittence_email ?? '';
        $wiring_type = $request->wiring_type ?? 'checking';

        $AGENT = new Agent();

        $AGENT->updateOrCreate([
            'id' => $agent_id
        ], [
            'ach_banking_info' => $ach_banking_info,
            'ach_account_info' => $ach_account_info,
            'ach_aba_routing' => $ach_aba_routing,
            'ach_remittence_email' => strtolower($ach_remittence_email),
            'ach_type' => strtolower($ach_type),
            'wiring_banking_info' => $wiring_banking_info,
            'wiring_account_info' => $wiring_account_info,
            'wiring_aba_routing' => $wiring_aba_routing,
            'wiring_remittence_email' => strtolower($wiring_remittence_email),
            'wiring_type' => strtolower($wiring_type),
        ]);

        $agent = $AGENT->where('id', $agent_id)
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'division',
                'drivers'
            ])->first();

        return response()->json(['result' => 'OK', 'agent' => $agent]);
    }
}
