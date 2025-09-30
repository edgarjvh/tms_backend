<?php


namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierContact;
use App\Models\Contact;
use App\Models\CarrierDriver;
use App\Models\CarrierNote;
use App\Models\Equipment;
use App\Models\Insurance;
use App\Models\InsuranceType;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CarriersController extends Controller
{
    /**
     * @param Request $json
     * @return JsonResponse
     */
    public function getCarrierById(Request $request)
    {
        $CARRIER = Carrier::query();
        $id = $request->id ?? 0;

        $carrier = $CARRIER->where('id', $id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])->first();

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    public function getCarrierByCode(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();

        $code = $request->code ?? '';

        $carrier = $CARRIER->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $json
     * @return JsonResponse
     */
    public function carriers(Request $json): JsonResponse
    {
        $CARRIER = new Carrier();

        $code = $json->code ?? '';
        $name = $json->name ?? '';
        $address1 = $json->address1 ?? '';
        $address2 = $json->address2 ?? '';
        $city = $json->city ?? '';
        $state = $json->state ?? '';
        $zip = $json->zip ?? '';
        $email = $json->email ?? '';
        $with_relations = $request->with_relations ?? 1;

        if ($with_relations === 1) {
            $carriers = $CARRIER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("name like '%$name%'")
                ->whereRaw("address1 like '%$address1%'")
                ->whereRaw("address2 like '%$address2%'")
                ->whereRaw("city like '%$city%'")
                ->whereRaw("state like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("email like '%$email%'")
                ->with('factoring_company')
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts',
                    'drivers',
                    'notes',
                    'insurances',
                    'factoring_company',
                    'mailing_same',
                    'mailing_address',
                    'mailing_carrier',
                    'documents',
                    'equipments_information'
                ])
                ->get();
        } else {
            $carriers = $CARRIER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("name like '%$name%'")
                ->whereRaw("address1 like '%$address1%'")
                ->whereRaw("address2 like '%$address2%'")
                ->whereRaw("city like '%$city%'")
                ->whereRaw("state like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("email like '%$email%'")
                ->with('factoring_company')
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts'
                ])
                ->get();
        }

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCarrierTtStatus(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();

        $id = $request->id ?? null;
        $is_in_tt = $request->is_in_tt ?? 0;

        if ($id > 0) {
            $CARRIER->where('id', $id)->update([
                'is_in_tt' => $is_in_tt
            ]);
        } else {
            return response()->json(['result' => 'ERROR', 'message' => 'Carrier ID is required']);
        }

        // EJEMPLO DE CÓDIGO PARA DESCARGAR UNA IMAGEN
        // $url = "https://assets.truckertools.com/images/tt-scan-docs/scanImageuploads/abc.jpg";
        // $filename = basename($url); // abc.jpg
        // $extension = pathinfo($filename, PATHINFO_EXTENSION); // jpg

        // // Ejemplo de resultado:
        // // $filename = "abc.jpg"
        // // $extension = "jpg"

        // $publicPath = public_path('uploads');
        // if (!file_exists($publicPath)) {
        //     mkdir($publicPath, 0777, true);
        // }
        // $destination = $publicPath . DIRECTORY_SEPARATOR . $filename;
        // copy($url, $destination);


        // SERVER TIME ZONE
        // $dateString = $request->date_string ?? null;
        // $estDateString = null;

        // if ($dateString) {
        //     try {
        //         $dt = new \DateTime($dateString, new \DateTimeZone('UTC'));
        //         $serverTz = new \DateTimeZone(date_default_timezone_get());
        //         $dt->setTimezone($serverTz);
        //         $estDateString = $dt->format('m/d/Y H:i:s T');
        //     } catch (\Exception $e) {
        //         $estDateString = null;
        //     }
        // }


        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierReport(): JsonResponse
    {
        $sql =
    "SELECT
        ca.id,
        CONCAT(ca.code, CASE WHEN ca.code_number = 0 THEN '' ELSE ca.code_number END) AS code,
        ca.name,
        GROUP_CONCAT(DISTINCT e.name ORDER BY e.name SEPARATOR ' | ') AS equipment_name,
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
        END) AS email,
        GROUP_CONCAT(DISTINCT e.id ORDER BY e.name SEPARATOR ',') as equipment_id
    FROM carriers as ca
    LEFT JOIN contacts AS p1 ON ca.id = p1.carrier_id AND p1.is_primary = 1
    LEFT JOIN carrier_equipments as ce ON ca.id = ce.carrier_id
    LEFT JOIN equipments as e ON ce.equipment_id = e.id
    GROUP BY ca.id
    ORDER BY ca.name";

        $carriers = DB::select($sql);

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierOpenInvoicesReport(Request $request): JsonResponse
    {
        $carrier_code = trim($request->carrier_code ?? '');
        $equipment_id = $request->equipment_id ?? -1;
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
                    o.carrier_id,
                    c.code,
                    c.code_number,
                    c.name,
                    o.order_date_time,
                    o.equipment_id,
                    e.name AS equipment_name,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating,
                    o.is_cancelled,
                    o.invoice_received_date
                FROM orders AS o
                INNER JOIN carriers AS c ON o.carrier_id = c.id
                INNER JOIN equipments AS e ON o.equipment_id = e.id
                WHERE o.is_template = 0
                    AND o.is_imported = 0
                    AND o.carrier_id IS NOT NULL
                    AND (o.invoice_date_paid IS NULL OR TRIM(o.invoice_date_paid) = '')
                    AND (o.invoice_received_date IS NOT NULL OR TRIM(o.invoice_received_date <> ''))
                    AND o.order_invoiced = 1 ";

        /**
         * CHECKING THE CARRIER CODE
         */
        if ($carrier_code !== '') {

            $sql .=
                /** @lang text */
                "AND LOWER(CONCAT(c.code, c.code_number)) LIKE '" . strtolower($carrier_code) . "%' ";
        }

        /**
         * CHECKING THE EQUIPMENT ID
         */
        if ($equipment_id > -1) {
            $sql .=
                /** @lang text */
                "AND o.equipment_id = ? ";

            $params[] = $equipment_id;
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
            ") AS result ORDER BY result.code ASC, result.code_number ASC, result.order_number ASC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function carrierSearch(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();

        $code = $request->search[0]['data'] ?? '';
        $name = $request->search[1]['data'] ?? '';
        $city = $request->search[2]['data'] ?? '';
        $state = $request->search[3]['data'] ?? '';
        $zip = $request->search[4]['data'] ?? '';
        $contact_name = $request->search[5]['data'] ?? '';
        $contact_phone = $request->search[6]['data'] ?? '';
        $email = $request->search[7]['data'] ?? '';
        $mc_number = $request->search[8]['data'] ?? '';
        $dot_number = $request->search[9]['data'] ?? '';
        $scac = $request->search[10]['data'] ?? '';
        $fid = $request->search[11]['data'] ?? '';

        $carriers = $CARRIER->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_name) like '$contact_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'")
            ->whereRaw("LOWER(mc_number) like '$mc_number%'")
            ->whereRaw("LOWER(dot_number) like '$dot_number%'")
            ->whereRaw("LOWER(scac) like '$scac%'")
            ->whereRaw("LOWER(fid) like '$fid%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->without([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_address',
                'documents',
                'equipments_information'
            ])
            ->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierOrders(Request $request)
    {
        $id = $request->id ?? 0;

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
                AND o.carrier_id = ?
            ORDER BY o.order_number DESC";

        $params = [$id];

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrier(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new Contact();

        $id = $request->id ?? '';
        $factoring_company_id = $request->factoring_company_id ?? null;
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
        $ext = $request->ext ?? '';
        $email = $request->email ?? '';
        $mc_number = $request->mc_number ?? '';
        $dot_number = $request->dot_number ?? '';
        $scac = $request->scac ?? '';
        $fid = $request->fid ?? '';
        $do_not_use = $request->do_not_use ?? 0;
        $rating = $request->rating ?? 0;
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

        $mailing_address_id = $request->mailing_address_id ?? null;
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;
        $mailing_carrier_id = $request->mailing_carrier_id ?? null;
        $mailing_carrier_contact_id = $request->mailing_carrier_contact_id ?? null;
        $mailing_carrier_contact_primary_phone = $request->mailing_carrier_contact_primary_phone ?? 'work';
        $mailing_carrier_contact_primary_email = $request->mailing_carrier_contact_primary_email ?? 'work';

        $codeExist = [];

        $curCarrier = $CARRIER
            ->where('id', $id)
            ->whereRaw("CONCAT(code,code_number) = '$full_code'")
            ->first();

        if ($curCarrier) {
            if ($curCarrier->code !== $code) {
                $codeExist = $CARRIER
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
        } else {
            $codeExist = $CARRIER
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

        $carrier = $CARRIER->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'factoring_company_id' => empty($factoring_company_id) ? null : $factoring_company_id,
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
                'ext' => $ext,
                'email' => strtolower($email),
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => strtoupper($scac),
                'fid' => $fid,
                'do_not_use' => $do_not_use,
                'rating' => $rating,
                'mailing_address_id' => $mailing_address_id,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
                'mailing_carrier_id' => $mailing_carrier_id,
                'mailing_carrier_contact_id' => $mailing_carrier_contact_id,
                'mailing_carrier_contact_primary_phone' => $mailing_carrier_contact_primary_phone,
                'mailing_carrier_contact_primary_email' => $mailing_carrier_contact_primary_email,
                'ach_banking_info' => $ach_banking_info,
                'ach_account_info' => $ach_account_info,
                'ach_aba_routing' => $ach_aba_routing,
                'ach_remittence_email' => $ach_remittence_email,
                'ach_type' => $ach_type,
                'wiring_banking_info' => $wiring_banking_info,
                'wiring_account_info' => $wiring_account_info,
                'wiring_aba_routing' => $wiring_aba_routing,
                'wiring_remittence_email' => $wiring_remittence_email,
                'wiring_type' => $wiring_type
            ]
        );

        if ($with_contact) {
            $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier->id)->get();

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
                $contact->carrier_id = $carrier->id;
                $contact->first_name = $contact_first;
                $contact->last_name = $contact_last;
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $ext;
                $contact->email_work = $email;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = $city;
                $contact->state = $state;
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->save();

                $CARRIER->where('id', $carrier->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CARRIER_CONTACT->where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $ext,
                        'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        $CARRIER_CONTACT->where('carrier_id', $carrier->id)->update([
            'address1' => $carrier->address1,
            'address2' => $carrier->address2,
            'city' => $carrier->city,
            'state' => $carrier->state,
            'zip_code' => $carrier->zip,
        ]);

        $carrier = $CARRIER->where('id', $carrier->id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])->first();


        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierAchWiringInfo(Request $request): JsonResponse
    {
        $carrier_id = $request->carrier_id ?? 0;
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

        $CARRIER = new Carrier();

        $CARRIER->updateOrCreate([
            'id' => $carrier_id
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

        $carrier = $CARRIER->where('id', $carrier_id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])->first();

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCarrierImport(Request $request)
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new Contact();

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
        $mc_number = $request->mcNumber ?? '';
        $dot_number = $request->dotNumber ?? '';
        $scac = $request->scac ?? '';
        $fid = $request->fid ?? '';
        $do_not_use = $request->doNotUse ?? 'N';

        //        $curCarrier = $CARRIER->where('id', $id)->first();
        //
        //        if ($curCarrier) {
        //            if ($curCarrier->code !== $code) {
        //                $codeExist = $CARRIER->where('id', '<>', $id)
        //                    ->where('code', $code)->get();
        //
        //                if (count($codeExist) > 0) {
        //                    $max_code_number = $CARRIER->where('code', $code)->max('code_number');
        //                    $code_number = $max_code_number + 1;
        //                } else {
        //                    $code_number = 0;
        //                }
        //            }
        //        } else {
        //            $codeExist = $CARRIER->where('code', $code)->get();
        //
        //            if (count($codeExist) > 0) {
        //                $max_code_number = $CARRIER->where('code', $code)->max('code_number');
        //                $code_number = $max_code_number + 1;
        //            } else {
        //                $code_number = 0;
        //            }
        //        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $carrier = $CARRIER->updateOrCreate(
            [
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
                'email' => strtolower($email),
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => strtoupper($scac),
                'fid' => $fid,
                'do_not_use' => strtoupper($do_not_use) === 'Y' ? 1 : 0,
            ]
        );

        if ($with_contact) {
            $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier->id)->get();

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
                $contact->carrier_id = $carrier->id;
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

                $CARRIER->where('id', $carrier->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CARRIER_CONTACT->where('id', $contact->id)->update([
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

        $newCarrier = $CARRIER->where('id', $carrier->id)->first();

        return response()->json(['result' => 'OK', 'carrier' => $newCarrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCarrierImport2(Request $request)
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
                $mc_number = $item['mcNumber'] ?? '';
                $dot_number = $item['dotNumber'] ?? '';
                $scac = $item['scac'] ?? '';
                $fid = $item['fid'] ?? '';
                $do_not_use = $item['doNotUse'] ?? 0;

                $carrier_id = 0;

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
                    $saved_carrier = Carrier::updateOrCreate(
                        [
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
                            'email' => strtolower($email),
                            'mc_number' => $mc_number,
                            'dot_number' => $dot_number,
                            'scac' => strtoupper($scac),
                            'fid' => $fid,
                            'do_not_use' => $do_not_use
                        ]
                    );

                    $carrier_id = $saved_carrier->id;
                } catch (Throwable | Exception $e) {
                    $carrier_id = 0;
                }

                if ($carrier_id > 0) {
                    try {
                        $saved_contact = CarrierContact::updateOrCreate([
                            'id' => 0
                        ], [
                            'carrier_id' => $carrier_id,
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

                        Carrier::where('id', $carrier_id)->update([
                            'primary_contact_id' => $saved_contact->id
                        ]);
                    } catch (Throwable | Exception $e) {
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
    public function getCarrierPayload(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new Contact();
        $CARRIER_NOTE = new CarrierNote();
        $CARRIER_DRIVER = new CarrierDriver();
        $INSURANCE = new Insurance();
        $carrier_id = $request->carrier_id;

        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)->orderBy('last_name')->get();
        $notes = $CARRIER_NOTE->where('carrier_id', $carrier_id)->get();
        $drivers = $CARRIER_DRIVER->where('carrier_id', $carrier_id)->orderBy('first_name')->get();
        $insurances = $INSURANCE->where('carrier_id', $carrier_id)->with('insurance_type')->has('insurance_type')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'notes' => $notes, 'drivers' => $drivers, 'insurances' => $insurances]);
    }

    /**
     * @throws Exception
     */
    public function getFullCarriers(): JsonResponse
    {
        $CARRIER = new Carrier();

        $carriers = $CARRIER->with([
            'contacts',
            'drivers',
            'notes',
            'insurances',
            'factoring_company',
            'mailing_same',
            'mailing_address',
            'mailing_carrier',
            'documents',
            'equipments_information'
        ])->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @throws Exception
     */
    public function getCarrierPopupItems(): JsonResponse
    {
        $EQUIPMENT = new Equipment();
        $INSURANCE_TYPE = new InsuranceType();

        $equipments = $EQUIPMENT->orderBy('name')->get();
        $insurance_types = $INSURANCE_TYPE->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments, 'insurance_types' => $insurance_types]);
    }

    /**
     * @throws Exception
     */
    public function getMcNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('mc_number');
        $CARRIER->where('mc_number', '<>', '');
        $CARRIER->orderBy('mc_number');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'mc_number']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getDotNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('dot_number');
        $CARRIER->where('dot_number', '<>', '');
        $CARRIER->orderBy('dot_number');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'dot_number']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getScacNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('scac');
        $CARRIER->where('scac', '<>', '');
        $CARRIER->orderBy('scac');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'scac']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getFidNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('fid');
        $CARRIER->where('fid', '<>', '');
        $CARRIER->orderBy('fid');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'fid']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }
}
