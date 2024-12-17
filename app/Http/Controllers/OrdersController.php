<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Division;
use App\Models\Driver;
use App\Models\InternalNotes;
use App\Models\LoadBoardOrder;
use App\Models\NotesForCarrier;
use App\Models\OrderBillingDocument;
use App\Models\OrderBillingNote;
use App\Models\OrderDocument;
use App\Models\Template;
use App\Models\TemplateDelivery;
use App\Models\Equipment;
use App\Models\EventType;
use App\Models\LoadType;
use App\Models\Order;
use App\Models\OrderCarrierRating;
use App\Models\OrderCustomerRating;
use App\Models\OrderEvent;
use App\Models\OrderLtlUnit;
use App\Models\Pickup;
use App\Models\TemplateInternalNote;
use App\Models\TemplateNoteForCarrier;
use App\Models\TemplatePickup;
use App\Models\RateType;
use App\Models\Route;
use App\Models\TemplateRoute;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;
use Throwable;

class OrdersController extends Controller
{
    public function getOrders2(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $user_code = $request->user_code ?? '';

        $ORDER->where('is_imported', 0);
        $ORDER->where('is_template', 0);
        //        $ORDER->select([
        //            'id',
        //            'order_number',
        //            'total_loaded_events',
        //            'total_delivered_events',
        //            'total_deliveries'
        //        ]);
        // AVAILABLE ===========================
        //        $ORDER->whereDoesntHave('carrier');

        // BOOKED
        //        $ORDER->whereHas('carrier');
        //        $ORDER->whereDoesntHave('events', function ($query1){
        //           return $query1->whereHas('event_type', function($query2){
        //              return $query2->where('name', 'loaded');
        //           });
        //        });

        // IN TRANSIT
        $ORDER->whereHas('carrier');
        $ORDER->totalDeliveries();
        //        $ORDER->whereColumn('total_delivered_events', '<', 'total_deliveries');

        if ($user_code !== '') {
            $ORDER->whereHas('user_code', function ($query1) use ($user_code) {
                return $query1->where('code', $user_code);
            });
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'routing',
            'events',
            'user_code'
        ]);

        $ORDER->orderBy('order_number');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    public function getOrders(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $user_code = $request->user_code ?? '';

        $ORDER->where('is_imported', 0);
        $ORDER->where('is_template', 0);

        if ($user_code !== '') {
            $ORDER->whereHas('user_code', function ($query1) use ($user_code) {
                return $query1->where('code', $user_code);
            });
        }

        $ORDER->with([
            //            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'routing',
            //            'events',
            //            'user_code'
        ]);

        $ORDER->orderBy('order_number');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoadBoardOrderById(Request $request): JsonResponse
    {
        $id = $request->id;

        $order = Order::query()
            ->select([
                'id',
                'order_number',
                'bill_to_customer_id',
                'bill_to_contact_id',
                'bill_to_contact_primary_phone',
                'carrier_id',
                'carrier_contact_id',
                'carrier_contact_primary_phone',
                'equipment_id',
                'carrier_driver_id'
            ])
            ->where('id', $id)
            ->with([
                'bill_to_company',
                'carrier',
                'pickups',
                'deliveries',
                'routing',
                'equipment',
                'driver',
                'accessorials'
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoadBoardOrders(Request $request): JsonResponse
    {
        $user_code = $request->user_code ?? '';
        $order_id = $request->order_id;

        $sql_available =
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
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state,
                (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
            FROM orders AS o
            WHERE o.is_imported = 0 AND o.is_template = 0 AND o.is_cancelled = 0 AND order_invoiced = 0 AND o.carrier_id IS NULL
            ORDER BY o.order_number ASC;";

        $sql_booked =
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
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state,
                c.code,
                c.code_number,
                (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
            FROM orders AS o
            INNER JOIN carriers AS c ON o.carrier_id = c.id
            WHERE o.is_imported = 0 AND o.is_template = 0 AND o.is_cancelled = 0 AND order_invoiced = 0 AND o.carrier_id IS NOT NULL
            AND NOT EXISTS (SELECT * FROM order_events AS e WHERE e.order_id = o.id AND e.event_type_id = 9)
            ORDER BY o.order_number ASC;";

        $sql_in_transit =
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
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state,
                c.code,
                c.code_number,
                (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
            FROM orders AS o
            INNER JOIN carriers AS c ON o.carrier_id = c.id
            WHERE o.is_imported = 0 AND o.is_template = 0 AND o.is_cancelled = 0 AND order_invoiced = 0 AND o.carrier_id IS NOT NULL
            AND EXISTS (SELECT * FROM order_events AS e WHERE e.order_id = o.id AND e.event_type_id = 9)
            AND ((SELECT COUNT(*) FROM order_routing AS r WHERE r.order_id = o.id AND r.delivery_id IS NOT NULL) > (SELECT COUNT(*) FROM order_events AS oe WHERE oe.order_id = o.id AND oe.event_type_id = 6))
            ORDER BY o.order_number ASC;";

        $sql_not_invoiced =
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
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state,
                c.code,
                c.code_number,
                (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
            FROM orders AS o
            INNER JOIN carriers AS c ON o.carrier_id = c.id
            WHERE o.is_imported = 0 AND o.is_template = 0 AND o.is_cancelled = 0 AND order_invoiced = 0 AND o.carrier_id IS NOT NULL
            AND EXISTS (SELECT * FROM order_events AS e WHERE e.order_id = o.id AND e.event_type_id = 9)
            AND ((SELECT COUNT(*) FROM order_routing AS r WHERE r.order_id = o.id AND r.delivery_id IS NOT NULL) = (SELECT COUNT(*) FROM order_events AS oe WHERE oe.order_id = o.id AND oe.event_type_id = 6))
            ORDER BY o.order_number ASC;";

        $available_orders = DB::select($sql_available);
        $booked_orders = DB::select($sql_booked);
        $in_transit_orders = DB::select($sql_in_transit);
        $not_invoiced_orders = DB::select($sql_not_invoiced);

        $selected_order = null;

        if (($order_id ?? 0) > 0) {
            $selected_order = Order::query()
                ->select([
                    'id',
                    'order_number',
                    'bill_to_customer_id',
                    'bill_to_contact_id',
                    'bill_to_contact_primary_phone',
                    'carrier_id',
                    'carrier_contact_id',
                    'carrier_contact_primary_phone',
                    'equipment_id',
                    'carrier_driver_id'
                ])
                ->where('id', $order_id)
                ->with([
                    'bill_to_company',
                    'carrier',
                    'pickups',
                    'deliveries',
                    'routing',
                    'equipment',
                    'driver'
                ])->first();
        }

        return response()->json([
            'result' => 'OK',
            'available_orders' => $available_orders,
            'booked_orders' => $booked_orders,
            'in_transit_orders' => $in_transit_orders,
            'not_invoiced_orders' => $not_invoiced_orders,
            'selected_order' => $selected_order
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCustomer(Request $request): JsonResponse
    {
        $bill_to_code = trim($request->bill_to_code ?? '');
        $customer_id = trim($request->customer_id ?? 0);
        $customer_code = trim($request->customer_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $customer_code = strlen($customer_code) === 7 ? $customer_code . '0' : $customer_code;

        if ($customer_id === 0 && $customer_code !== '') {
            $customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$customer_code'")->first();
            $customer_id = $customer->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    CASE op.customer_id WHEN ? THEN op.customer_id ELSE NULL END AS shipper_customer_id,
                    CASE od.customer_id WHEN ? THEN od.customer_id ELSE NULL END AS consignee_customer_id,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                INNER JOIN customers AS c ON o.bill_to_customer_id = c.id
                INNER JOIN order_pickups AS op ON o.id = op.order_id
                INNER JOIN order_deliveries AS od ON o.id = od.order_id
                WHERE is_template = 0 ";

        $params[] = $customer_id;
        $params[] = $customer_id;

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($customer_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN (?))
                         AND (op.customer_id IN (?) OR od.customer_id in (?)) ";

                $params[] = $bill_to_customer_id;
                $params[] = $customer_id;
                $params[] = $customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN (?) OR op.customer_id IN (?) OR od.customer_id in (?)) ";

                $params[] = $customer_id;
                $params[] = $customer_id;
                $params[] = $customer_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCarrier(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $carrier_id = trim($request->carrier_id ?? 0);
        $carrier_code = trim($request->carrier_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $carrier_code = strlen($carrier_code) === 7 ? $carrier_code . '0' : $carrier_code;

        if ($carrier_id === 0 && $carrier_code !== '') {
            $carrier = Carrier::query()->whereRaw("CONCAT(`code`, `code_number`) = '$carrier_code'")->first();
            $carrier_id = $carrier->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                INNER JOIN customers AS c ON o.bill_to_customer_id = c.id
                WHERE is_template = 0 ";

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($carrier_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.carrier_id IN (?)) AND (o.bill_to_customer_id in (?)) ";

                $params[] = $carrier_id;
                $params[] = $bill_to_customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.carrier_id IN (?)) ";

                $params[] = $carrier_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueDivision(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $division_id = trim($request->division_id ?? 0);
        $division_code = trim($request->division_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $division_code = strlen($division_code) === 7 ? $division_code . '0' : $division_code;

        if ($division_id === 0 && $division_code !== '') {
            $division = Division::query()->whereRaw("CONCAT(`code`, `code_number`) = '$division_code'")->first();
            $division_id = $division->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                INNER JOIN customers AS c ON o.bill_to_customer_id = c.id
                WHERE is_template = 0 ";

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($division_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.division_id IN (?)) AND (o.bill_to_customer_id in (?)) ";

                $params[] = $division_id;
                $params[] = $bill_to_customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.division_id IN (?)) ";

                $params[] = $division_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCustomer(Request $request): JsonResponse
    {
        $bill_to_code = trim($request->bill_to_code ?? '');
        $customer_id = trim($request->customer_id ?? 0);
        $customer_code = trim($request->customer_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $customer_code = strlen($customer_code) === 7 ? $customer_code . '0' : $customer_code;

        if ($customer_id === 0 && $customer_code !== '') {
            $customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$customer_code'")->first();
            $customer_id = $customer->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    o.order_date_time,
                    o.customer_check_number,
                    CASE op.customer_id WHEN ? THEN op.customer_id ELSE NULL END AS shipper_customer_id,
                    CASE od.customer_id WHEN ? THEN od.customer_id ELSE NULL END AS consignee_customer_id,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                INNER JOIN order_pickups AS op ON o.id = op.order_id
                INNER JOIN order_deliveries AS od ON o.id = od.order_id
                WHERE is_template = 0 ";

        $params[] = $customer_id;
        $params[] = $customer_id;

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($customer_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN (?))
                         AND (op.customer_id IN (?) OR od.customer_id in (?)) ";

                $params[] = $bill_to_customer_id;
                $params[] = $customer_id;
                $params[] = $customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN (?) OR op.customer_id IN (?) OR od.customer_id in (?)) ";

                $params[] = $customer_id;
                $params[] = $customer_id;
                $params[] = $customer_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCarrier(Request $request): JsonResponse
    {
        $bill_to_code = trim($request->bill_to_code ?? '');
        $carrier_id = trim($request->carrier_id ?? 0);
        $carrier_code = trim($request->carrier_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $carrier_code = strlen($carrier_code) === 7 ? $carrier_code . '0' : $carrier_code;

        if ($carrier_id === 0 && $carrier_code !== '') {
            $carrier = Carrier::query()->whereRaw("CONCAT(`code`, `code_number`) = '$carrier_code'")->first();
            $carrier_id = $carrier->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    o.order_date_time,
                    o.customer_check_number,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                WHERE is_template = 0 ";

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($carrier_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.carrier_id IN (?)) AND (o.bill_to_customer_id in (?)) ";

                $params[] = $carrier_id;
                $params[] = $bill_to_customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.carrier_id IN (?)) ";

                $params[] = $carrier_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryDivision(Request $request): JsonResponse
    {
        $bill_to_code = trim($request->bill_to_code ?? '');
        $division_id = trim($request->division_id ?? 0);
        $division_code = trim($request->division_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $division_code = strlen($division_code) === 7 ? $division_code . '0' : $division_code;

        if ($division_id === 0 && $division_code !== '') {
            $division = Division::query()->whereRaw("CONCAT(`code`, `code_number`) = '$division_code'")->first();
            $division_id = $division->id ?? 0;
        }

        $bill_to_customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_customer_id = $bill_to_customer->id ?? 0;

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
                    o.order_date_time,
                    o.customer_check_number,
                    (SELECT sum(cur.total_charges) FROM order_customer_ratings AS cur WHERE cur.order_id = o.id) AS total_customer_rating,
                    (SELECT sum(car.total_charges) FROM order_carrier_ratings AS car WHERE car.order_id = o.id) AS total_carrier_rating
                FROM orders AS o
                WHERE is_template = 0 ";

        /**
         * CHECKING THE BILL-TO/CUSTOMER IDs
         */
        if ($division_id > 0) {
            if ($bill_to_code !== '') {
                $sql .=
                    /** @lang text */
                    "AND (o.division_id IN (?)) AND (o.bill_to_customer_id in (?)) ";

                $params[] = $division_id;
                $params[] = $bill_to_customer_id;
            } else {
                $sql .=
                    /** @lang text */
                    "AND (o.division_id IN (?)) ";

                $params[] = $division_id;
            }
        } else {
            if ($bill_to_customer_id > 0) {
                $sql .=
                    /** @lang text */
                    "AND (o.bill_to_customer_id IN ($bill_to_customer_id)) ";

                $params[] = $bill_to_customer_id;
            }
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
            "GROUP BY o.order_number) AS result ORDER BY result.order_date_time DESC;";

        $orders = DB::select($sql, $params);

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderById(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $id = $request->id ?? 0;
        $is_template = $request->is_template ?? 0;

        $ORDER->where('id', $id);

        if ($is_template > 0) {
            $ORDER->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'division',
                'load_type',
                'order_customer_ratings',
                'order_carrier_ratings',
                'user_code'
            ]);
        } else {
            $ORDER->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
                'billing_documents',
                'billing_notes',
                'term',
                'order_ltl_units',
                'user_code',
                'accessorials'
            ]);
        }


        $order = $ORDER->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByOrderNumber(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $order_number = $request->order_number ?? 0;
        $user_code = $request->user_code ?? '';
        $action = $request->action ?? null;

        if ($action) {
            if ($action === 'next') {
                $_order = DB::select("select order_number from orders where order_number = (select min(order_number) from orders where order_number > ?)", [$order_number]);
                if (count($_order) > 0) {
                    $order_number = $_order[0]->order_number;
                }
            }

            if ($action === 'previous') {
                $_order = DB::select("select order_number from orders where order_number = (select max(order_number) from orders where order_number < ?)", [$order_number]);
                if (count($_order) > 0) {
                    $order_number = $_order[0]->order_number;
                }
            }
        }

        if ($user_code !== '') {
            $ORDER->whereHas('bill_to_company', function ($query) use ($user_code) {
                return $query->where('agent_code', $user_code);
            });
        }

        $ORDER->where('order_number', $order_number)
            ->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
                'billing_documents',
                'billing_notes',
                'term',
                'order_ltl_units',
                'user_code',
                'accessorials'
            ]);

        $order = $ORDER->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByTripNumber(Request $request): JsonResponse
    {
        $ORDER = new Order();

        $trip_number = $request->trip_number ?? 0;
        $user_code = $request->user_code ?? '';

        if ($user_code !== '') {
            $ORDER->whereHas('bill_to_company', function ($query) use ($user_code) {
                return $query->where('agent_code', $user_code);
            });
        }

        $ORDER->where('trip_number', $trip_number)
            ->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
                'billing_documents',
                'billing_notes',
                'term',
                'order_ltl_units',
                'user_code',
                'accessorials'
            ]);

        $order = $ORDER->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @throws Exception
     */
    public function getLastOrderNumber(): JsonResponse
    {
        $ORDER = new Order();

        $last_order_number = $ORDER->max('order_number');

        if (!$last_order_number) {
            $last_order_number = 0;
        }

        return response()->json(['result' => 'OK', 'last_order_number' => $last_order_number]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrder(Request $request): JsonResponse
    {
        $ORDER = new Order();

        $id = $request->id ?? null;
        $is_template = (int)($request->is_template ?? 0);
        $is_new_template = (int)($request->is_new_template ?? 0);
        $keep_order = (int)($request->keep_order ?? 0);
        $name = $request->name ?? '';
        $order_number = (int)($request->order_number ?? 0);
        $user_code_id = $request->user_code_id ?? null;
        $trip_number = (int)($request->trip_number ?? 0);
        $division_id = $request->division_id ?? null;
        $load_type_id = $request->load_type_id ?? null;
        $bill_to_customer_id = $request->bill_to_customer_id ?? null;
        $bill_to_contact_id = $request->bill_to_contact_id ?? null;
        $bill_to_contact_name = $request->bill_to_contact_name ?? '';
        $bill_to_contact_primary_phone = $request->bill_to_contact_primary_phone ?? 'work';
        $carrier_id = $request->carrier_id ?? null;

        $carrier_contact_id = $request->carrier_contact_id ?? null;
        $carrier_contact_primary_phone = $request->carrier_contact_primary_phone ?? 'work';

        $equipment_id = $request->equipment_id ?? null;
        $carrier_driver_id = isset($request->carrier_driver_id) ? $request->carrier_driver_id > 0 ? $request->carrier_driver_id : null : null;
        $agent_code = $request->agent_code ?? '';
        $agent_commission = $request->agent_commission ?? '';
        $salesman_code = $request->salesman_code ?? '';
        $salesman_commission = $request->salesman_commission ?? '';
        $miles = $request->miles ?? 0;
        $charges = $request->charges ?? '';
        $order_cost = $request->order_cost ?? '';
        $profit = $request->profit ?? '';
        $percentage = $request->percentage ?? '';
        $haz_mat = $request->haz_mat ?? 0;
        $expedited = $request->expedited ?? 0;
        $is_cancelled = $request->is_cancelled ?? 0;

        $customer_check_number = $request->customer_check_number ?? null;
        $customer_date_received = $request->customer_date_received ?? null;
        $invoice_received_date = $request->invoice_received_date ?? null;
        $invoice_number = $request->invoice_number ?? null;
        $term_id = $request->term_id ?? null;
        $invoice_date_paid = $request->invoice_date_paid ?? null;
        $carrier_check_number = $request->carrier_check_number ?? null;
        $invoice_customer_reviewed = $request->invoice_customer_reviewed ?? 0;
        $order_invoiced = $request->order_invoiced ?? 0;
        $invoice_carrier_previewed = $request->invoice_carrier_previewed ?? 0;
        $invoice_carrier_received = $request->invoice_carrier_received ?? 0;
        $invoice_bol_received = $request->invoice_bol_received ?? 0;
        $invoice_rate_conf_received = $request->invoice_rate_conf_received ?? 0;
        $invoice_carrier_approved = $request->invoice_carrier_approved ?? 0;

        $pickups = $request->pickups ?? [];
        $deliveries = $request->deliveries ?? [];
        $routing = $request->routing ?? [];
        $order_customer_ratings = $request->order_customer_ratings ?? [];
        $order_carrier_ratings = $request->order_carrier_ratings ?? [];
        $order_internal_notes = $request->order_internal_notes ?? [];
        $order_notes_for_carrier = $request->order_notes_for_carrier ?? [];

        $last_order_number = $ORDER->max('order_number');
        $last_trip_number = $ORDER->max('trip_number');

        if ($is_template === 0) {
            if ($order_number === 0) {
                if ($last_order_number && $last_order_number >= 32000) {
                    $order_number = $last_order_number + 1;
                } else {
                    $order_number = 32000;
                }

                if ($carrier_id > 0) {
                    if ($last_trip_number) {
                        $trip_number = $last_trip_number + 1;
                    } else {
                        $trip_number = 1;
                    }
                }
            } else {
                if ($carrier_id > 0 && $trip_number === 0) {
                    if ($last_trip_number) {
                        $trip_number = $last_trip_number + 1;
                    } else {
                        $trip_number = 1;
                    }
                }
            }

            $order = $ORDER->updateOrCreate(
                [
                    'order_number' => $order_number
                ],
                [
                    'user_code_id' => $user_code_id,
                    'trip_number' => $trip_number,
                    'division_id' => $division_id,
                    'load_type_id' => $load_type_id,
                    'bill_to_customer_id' => $bill_to_customer_id,
                    'carrier_id' => $carrier_id,
                    'carrier_contact_id' => $carrier_contact_id,
                    'carrier_contact_primary_phone' => $carrier_contact_primary_phone,
                    'equipment_id' => $equipment_id,
                    'carrier_driver_id' => $carrier_driver_id,
                    'agent_code' => $agent_code,
                    'agent_commission' => $agent_commission,
                    'salesman_code' => $salesman_code,
                    'salesman_commission' => $salesman_commission,
                    'miles' => $miles,
                    'charges' => $charges,
                    'order_cost' => $order_cost,
                    'profit' => $profit,
                    'percentage' => $percentage,
                    'haz_mat' => $haz_mat,
                    'expedited' => $expedited,
                    'customer_check_number' => trim($customer_check_number) === '' ? null : $customer_check_number,
                    'customer_date_received' => $customer_date_received,
                    'invoice_received_date' => $invoice_received_date,
                    'invoice_number' => $invoice_number,
                    'term_id' => $term_id,
                    'invoice_date_paid' => $invoice_date_paid,
                    'carrier_check_number' => $carrier_check_number,
                    'invoice_customer_reviewed' => $invoice_customer_reviewed,
                    'order_invoiced' => $order_invoiced,
                    'invoice_carrier_previewed' => $invoice_carrier_previewed,
                    'invoice_carrier_received' => $invoice_carrier_received,
                    'invoice_bol_received' => $invoice_bol_received,
                    'invoice_rate_conf_received' => $invoice_rate_conf_received,
                    'invoice_carrier_approved' => $invoice_carrier_approved,
                    'is_cancelled' => $is_cancelled
                ]
            );

            if (count($pickups) > 0) {
                $PICKUP = new Pickup();

                for ($i = 0; $i < count($pickups); $i++) {
                    $pickup = $pickups[$i];

                    if (($pickup['toSave'] ?? false)) {
                        if (($pickup['customer_id'] ?? 0) > 0) {
                            $PICKUP->updateOrCreate([
                                'id' => $pickup['id'] ?? 0
                            ], [
                                'order_id' => $order->id,
                                'customer_id' => $pickup['customer_id'],
                                'type' => 'pickup',
                                'pu_date1' => $pickup['pu_date1'] ?? '',
                                'pu_date2' => $pickup['pu_date2'] ?? '',
                                'pu_time1' => $pickup['pu_time1'] ?? '',
                                'pu_time2' => $pickup['pu_time2'] ?? '',
                                'bol_numbers' => $pickup['bol_numbers'] ?? '',
                                'po_numbers' => $pickup['po_numbers'] ?? '',
                                'ref_numbers' => $pickup['ref_numbers'] ?? '',
                                'seal_number' => $pickup['seal_number'] ?? '',
                                'special_instructions' => $pickup['special_instructions'] ?? ''
                            ]);
                        }
                    }
                }
            }

            $newOrder = $ORDER->where('order_number', $order->order_number ?? 0)
                ->with([
                    'bill_to_company',
                    'carrier',
                    'equipment',
                    'driver',
                    'notes_for_driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'documents',
                    'events',
                    'division',
                    'load_type',
                    'template',
                    'order_customer_ratings',
                    'order_carrier_ratings',
                    'billing_documents',
                    'billing_notes',
                    'term',
                    'order_ltl_units',
                    'user_code'
                ])->first();

            return response()->json(['result' => 'OK', 'order' => $newOrder, 'order_number' => $order_number]);
        } else {
            if ($is_new_template === 1) {
                if ($keep_order === 1) {
                    $new_template = Order::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_number' => 0,
                        'trip_number' => 0,
                        'division_id' => $division_id,
                        'load_type_id' => $load_type_id,
                        'is_template' => 1,
                        'name' => $name,
                        'bill_to_customer_id' => $bill_to_customer_id,
                        'bill_to_contact_id' => $bill_to_contact_id,
                        'bill_to_contact_name' => $bill_to_contact_name,
                        'bill_to_contact_primary_phone' => $bill_to_contact_primary_phone,
                        'carrier_id' => $carrier_id,
                        'carrier_contact_id' => $carrier_contact_id,
                        'carrier_contact_primary_phone' => $carrier_contact_primary_phone,
                        'equipment_id' => $equipment_id,
                        'carrier_driver_id' => $carrier_driver_id,
                        'agent_code' => $agent_code,
                        'miles' => $miles,
                        'haz_mat' => $haz_mat,
                        'expedited' => $expedited
                    ]);

                    if (count($pickups) > 0) {
                        for ($p = 0; $p < count($pickups); $p++) {
                            $pickup = $pickups[$p];

                            Pickup::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'customer_id' => $pickup['customer_id'],
                                'type' => 'pickup',
                                'pu_date1' => $pickup['pu_date1'] ?? '',
                                'pu_date2' => $pickup['pu_date2'] ?? '',
                                'pu_time1' => $pickup['pu_time1'] ?? '',
                                'pu_time2' => $pickup['pu_time2'] ?? '',
                                'bol_numbers' => $pickup['bol_numbers'] ?? '',
                                'po_numbers' => $pickup['po_numbers'] ?? '',
                                'ref_numbers' => $pickup['ref_numbers'] ?? '',
                                'seal_number' => $pickup['seal_number'] ?? '',
                                'special_instructions' => $pickup['special_instructions'] ?? ''
                            ]);
                        }
                    }

                    if (count($deliveries) > 0) {
                        for ($d = 0; $d < count($deliveries); $d++) {
                            $delivery = $deliveries[$d];

                            Delivery::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'customer_id' => $delivery['customer_id'],
                                'type' => 'delivery',
                                'delivery_date1' => $delivery['delivery_date1'] ?? '',
                                'delivery_date2' => $delivery['delivery_date2'] ?? '',
                                'delivery_time1' => $delivery['delivery_time1'] ?? '',
                                'delivery_time2' => $delivery['delivery_time2'] ?? '',
                                'bol_numbers' => $delivery['bol_numbers'] ?? '',
                                'po_numbers' => $delivery['po_numbers'] ?? '',
                                'ref_numbers' => $delivery['ref_numbers'] ?? '',
                                'seal_number' => $delivery['seal_number'] ?? '',
                                'special_instructions' => $delivery['special_instructions'] ?? ''
                            ]);
                        }
                    }

                    if (count($routing) > 0) {
                        for ($r = 0; $r < count($routing); $r++) {
                            $route = $routing[$r];

                            Route::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'pickup_id' => $route['pickup_id'] ?? null,
                                'delivery_id' => $route['delivery_id'] ?? null,
                                'type' => $route['type'] ?? 'pickup'
                            ]);
                        }
                    }

                    if (count($order_customer_ratings) > 0) {
                        for ($x = 0; $x < count($order_customer_ratings); $x++) {
                            $customer_rating = $order_customer_ratings[$x];

                            OrderCustomerRating::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'rate_type_id' => $customer_rating['rate_type_id'] ?? null,
                                'description' => $customer_rating['description'] ?? '',
                                'rate_subtype_id' => $customer_rating['rate_subtype_id'] ?? null,
                                'pieces' => $customer_rating['pieces'] ?? 0.00,
                                'pieces_unit' => $customer_rating['pieces_unit'] ?? '',
                                'weight' => $customer_rating['weight'] ?? 0.00,
                                'weight_unit' => $customer_rating['weight_unit'] ?? '',
                                'feet_required' => $customer_rating['feet_required'] ?? 0.00,
                                'feet_required_unit' => $customer_rating['feet_required_unit'] ?? '',
                                'rate' => $customer_rating['rate'] ?? 0.00,
                                'percentage' => $customer_rating['percentage'] ?? 0.00,
                                'days' => $customer_rating['days'] ?? 0.00,
                                'hours' => $customer_rating['hours'] ?? 0.00,
                                'total_charges' => $customer_rating['total_charges'] ?? 0.00
                            ]);
                        }
                    }

                    if (count($order_carrier_ratings) > 0) {
                        for ($y = 0; $y < count($order_carrier_ratings); $y++) {
                            $carrier_rating = $order_carrier_ratings[$y];

                            OrderCustomerRating::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'rate_type_id' => $carrier_rating['rate_type_id'] ?? null,
                                'description' => $carrier_rating['description'] ?? '',
                                'rate_subtype_id' => $carrier_rating['rate_subtype_id'] ?? null,
                                'pieces' => $carrier_rating['pieces'] ?? 0.00,
                                'pieces_unit' => $carrier_rating['pieces_unit'] ?? '',
                                'weight' => $carrier_rating['weight'] ?? 0.00,
                                'weight_unit' => $carrier_rating['weight_unit'] ?? '',
                                'feet_required' => $carrier_rating['feet_required'] ?? 0.00,
                                'feet_required_unit' => $carrier_rating['feet_required_unit'] ?? '',
                                'rate' => $carrier_rating['rate'] ?? 0.00,
                                'percentage' => $carrier_rating['percentage'] ?? 0.00,
                                'days' => $carrier_rating['days'] ?? 0.00,
                                'hours' => $carrier_rating['hours'] ?? 0.00,
                                'total_charges' => $carrier_rating['total_charges'] ?? 0.00
                            ]);
                        }
                    }

                    if (count($order_internal_notes) > 0) {
                        for ($w = 0; $w < count($order_internal_notes); $w++) {
                            $internal_note = $order_internal_notes[$w];

                            InternalNotes::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'text' => $internal_note['text'] ?? ''
                            ]);
                        }
                    }

                    if (count($order_notes_for_carrier) > 0) {
                        for ($z = 0; $z < count($order_notes_for_carrier); $z++) {
                            $note_for_carrier = $order_notes_for_carrier[$z];

                            NotesForCarrier::query()->updateOrCreate([
                                'id' => null
                            ], [
                                'order_id' => $new_template->id,
                                'text' => $note_for_carrier['text'] ?? ''
                            ]);
                        }
                    }
                } else {
                    OrderDocument::query()->where('order_id', $id)->delete();
                    OrderEvent::query()->where('order_id', $id)->delete();
                    OrderBillingNote::query()->where('order_id', $id)->delete();
                    OrderBillingDocument::query()->where('order_id', $id)->delete();

                    Order::query()->updateOrCreate([
                        'id' => $id
                    ], [
                        'order_number' => 0,
                        'trip_number' => 0,
                        'is_template' => 1,
                        'name' => $name
                    ]);
                }
            } else {
                Pickup::query()->where('order_id', $id)->delete();
                Delivery::query()->where('order_id', $id)->delete();
                Route::query()->where('order_id', $id)->delete();
                OrderCustomerRating::query()->where('order_id', $id)->delete();
                OrderCarrierRating::query()->where('order_id', $id)->delete();
                InternalNotes::query()->where('order_id', $id)->delete();
                NotesForCarrier::query()->where('order_id', $id)->delete();

                Order::query()->updateOrCreate(
                    [
                        'id' => $id
                    ],
                    [
                        'order_number' => 0,
                        'trip_number' => 0,
                        'division_id' => $division_id,
                        'load_type_id' => $load_type_id,
                        'is_template' => 1,
                        'name' => $name,
                        'bill_to_customer_id' => $bill_to_customer_id,
                        'carrier_id' => $carrier_id,
                        'carrier_contact_id' => $carrier_contact_id,
                        'carrier_contact_primary_phone' => $carrier_contact_primary_phone,
                        'equipment_id' => $equipment_id,
                        'carrier_driver_id' => $carrier_driver_id,
                        'agent_code' => $agent_code,
                        'miles' => $miles,
                        'haz_mat' => $haz_mat,
                        'expedited' => $expedited
                    ]
                );

                if (count($pickups) > 0) {
                    for ($p = 0; $p < count($pickups); $p++) {
                        $pickup = $pickups[$p];

                        Pickup::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'customer_id' => $pickup['customer_id'],
                            'type' => 'pickup',
                            'pu_date1' => $pickup['pu_date1'] ?? '',
                            'pu_date2' => $pickup['pu_date2'] ?? '',
                            'pu_time1' => $pickup['pu_time1'] ?? '',
                            'pu_time2' => $pickup['pu_time2'] ?? '',
                            'bol_numbers' => $pickup['bol_numbers'] ?? '',
                            'po_numbers' => $pickup['po_numbers'] ?? '',
                            'ref_numbers' => $pickup['ref_numbers'] ?? '',
                            'seal_number' => $pickup['seal_number'] ?? '',
                            'special_instructions' => $pickup['special_instructions'] ?? ''
                        ]);
                    }
                }

                if (count($deliveries) > 0) {
                    for ($d = 0; $d < count($deliveries); $d++) {
                        $delivery = $deliveries[$d];

                        Delivery::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'customer_id' => $delivery['customer_id'],
                            'type' => 'delivery',
                            'delivery_date1' => $delivery['delivery_date1'] ?? '',
                            'delivery_date2' => $delivery['delivery_date2'] ?? '',
                            'delivery_time1' => $delivery['delivery_time1'] ?? '',
                            'delivery_time2' => $delivery['delivery_time2'] ?? '',
                            'bol_numbers' => $delivery['bol_numbers'] ?? '',
                            'po_numbers' => $delivery['po_numbers'] ?? '',
                            'ref_numbers' => $delivery['ref_numbers'] ?? '',
                            'seal_number' => $delivery['seal_number'] ?? '',
                            'special_instructions' => $delivery['special_instructions'] ?? ''
                        ]);
                    }
                }

                if (count($routing) > 0) {
                    for ($r = 0; $r < count($routing); $r++) {
                        $route = $routing[$r];

                        Route::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'pickup_id' => $route['pickup_id'] ?? null,
                            'delivery_id' => $route['delivery_id'] ?? null,
                            'type' => $route['type'] ?? 'pickup'
                        ]);
                    }
                }

                if (count($order_customer_ratings) > 0) {
                    for ($x = 0; $x < count($order_customer_ratings); $x++) {
                        $customer_rating = $order_customer_ratings[$x];

                        OrderCustomerRating::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'rate_type_id' => $customer_rating['rate_type_id'] ?? null,
                            'description' => $customer_rating['description'] ?? '',
                            'rate_subtype_id' => $customer_rating['rate_subtype_id'] ?? null,
                            'pieces' => $customer_rating['pieces'] ?? 0.00,
                            'pieces_unit' => $customer_rating['pieces_unit'] ?? '',
                            'weight' => $customer_rating['weight'] ?? 0.00,
                            'weight_unit' => $customer_rating['weight_unit'] ?? '',
                            'feet_required' => $customer_rating['feet_required'] ?? 0.00,
                            'feet_required_unit' => $customer_rating['feet_required_unit'] ?? '',
                            'rate' => $customer_rating['rate'] ?? 0.00,
                            'percentage' => $customer_rating['percentage'] ?? 0.00,
                            'days' => $customer_rating['days'] ?? 0.00,
                            'hours' => $customer_rating['hours'] ?? 0.00,
                            'total_charges' => $customer_rating['total_charges'] ?? 0.00
                        ]);
                    }
                }

                if (count($order_carrier_ratings) > 0) {
                    for ($y = 0; $y < count($order_carrier_ratings); $y++) {
                        $carrier_rating = $order_carrier_ratings[$y];

                        OrderCustomerRating::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'rate_type_id' => $carrier_rating['rate_type_id'] ?? null,
                            'description' => $carrier_rating['description'] ?? '',
                            'rate_subtype_id' => $carrier_rating['rate_subtype_id'] ?? null,
                            'pieces' => $carrier_rating['pieces'] ?? 0.00,
                            'pieces_unit' => $carrier_rating['pieces_unit'] ?? '',
                            'weight' => $carrier_rating['weight'] ?? 0.00,
                            'weight_unit' => $carrier_rating['weight_unit'] ?? '',
                            'feet_required' => $carrier_rating['feet_required'] ?? 0.00,
                            'feet_required_unit' => $carrier_rating['feet_required_unit'] ?? '',
                            'rate' => $carrier_rating['rate'] ?? 0.00,
                            'percentage' => $carrier_rating['percentage'] ?? 0.00,
                            'days' => $carrier_rating['days'] ?? 0.00,
                            'hours' => $carrier_rating['hours'] ?? 0.00,
                            'total_charges' => $carrier_rating['total_charges'] ?? 0.00
                        ]);
                    }
                }

                if (count($order_internal_notes) > 0) {
                    for ($w = 0; $w < count($order_internal_notes); $w++) {
                        $internal_note = $order_internal_notes[$w];

                        InternalNotes::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'text' => $internal_note['text'] ?? ''
                        ]);
                    }
                }

                if (count($order_notes_for_carrier) > 0) {
                    for ($z = 0; $z < count($order_notes_for_carrier); $z++) {
                        $note_for_carrier = $order_notes_for_carrier[$z];

                        NotesForCarrier::query()->updateOrCreate([
                            'id' => null
                        ], [
                            'order_id' => $id,
                            'text' => $note_for_carrier['text'] ?? ''
                        ]);
                    }
                }
            }

            return response()->json(['result' => 'OK']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function useTemplate(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $user_code_id = $request->user_code_id ?? null;

        if ($id) {
            $template = Template::query()->where('id', $id)->with([
                'pickups',
                'deliveries',
                'routing',
                'notes_for_carrier',
                'internal_notes',
                'order_customer_ratings',
                'order_carrier_ratings'
            ])->first();

            $order_number = (Order::query()->max('order_number') + 1);
            $trip_number = ($template->carrier_id ?? 0) > 0 ? (Order::query()->max('trip_number') + 1) : 0;

            $order = Order::query()->updateOrCreate([
                'order_number' => $order_number
            ], [
                'user_code_id' => $user_code_id,
                'trip_number' => $trip_number,
                'division_id' => $template->division_id,
                'load_type_id' => $template->load_type_id,
                'bill_to_customer_id' => $template->bill_to_customer_id,
                'bill_to_contact_id' => $template->bill_to_contact_id,
                'bill_to_contact_name' => $template->bill_to_contact_name,
                'bill_to_contact_primary_phone' => $template->bill_to_contact_primary_phone,
                'carrier_id' => $template->carrier_id,
                'carrier_contact_id' => $template->carrier_contact_id,
                'carrier_contact_primary_phone' => $template->carrier_contact_primary_phone,
                'equipment_id' => $template->equipment_id,
                'carrier_driver_id' => $template->carrier_driver_id,
                'agent_code' => $template->agent_code,
                'miles' => $template->miles,
                'haz_mat' => $template->haz_mat,
                'expedited' => $template->expedited,
                'waypoints' => $template->waypoints
            ]);

            if (count($template->notes_for_carrier) > 0) {
                foreach ($template->notes_for_carrier as $note) {
                    NotesForCarrier::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'user_code_id' => $user_code_id,
                        'order_id' => $order->id,
                        'text' => $note['text']
                    ]);
                }
            }

            if (count($template->internal_notes) > 0) {
                foreach ($template->internal_notes as $note) {
                    InternalNotes::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'user_code_id' => $user_code_id,
                        'order_id' => $order->id,
                        'text' => $note['text']
                    ]);
                }
            }

            if (count($template->order_customer_ratings) > 0) {
                foreach ($template->order_customer_ratings as $rating) {
                    OrderCustomerRating::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'rate_type_id' => $rating->rate_type_id,
                        'description' => $rating->description,
                        'pieces' => $rating->pieces,
                        'pieces_unit' => $rating->pieces_unit,
                        'weight' => $rating->weight,
                        'weight_unit' => $rating->weight_unit,
                        'feet_required' => $rating->feet_required,
                        'feet_required_unit' => $rating->feet_required_unit,
                        'rate_subtype_id' => $rating->rate_subtype_id,
                        'percentage' => $rating->percentage,
                        'rate' => $rating->rate,
                        'linehaul' => $rating->linehaul,
                        'days' => $rating->days,
                        'hours' => $rating->hours,
                        'total_charges' => $rating->total_charges
                    ]);
                }
            }

            if (count($template->order_carrier_ratings) > 0) {
                foreach ($template->order_carrier_ratings as $rating) {
                    OrderCarrierRating::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'rate_type_id' => $rating->rate_type_id,
                        'description' => $rating->description,
                        'pieces' => $rating->pieces,
                        'pieces_unit' => $rating->pieces_unit,
                        'weight' => $rating->weight,
                        'weight_unit' => $rating->weight_unit,
                        'feet_required' => $rating->feet_required,
                        'feet_required_unit' => $rating->feet_required_unit,
                        'rate_subtype_id' => $rating->rate_subtype_id,
                        'percentage' => $rating->percentage,
                        'rate' => $rating->rate,
                        'linehaul' => $rating->linehaul,
                        'days' => $rating->days,
                        'hours' => $rating->hours,
                        'total_charges' => $rating->total_charges
                    ]);
                }
            }

            $routing = array();

            foreach ($template->routing as $route) {
                $routing[] = 0;
            }

            if (count($template->pickups) > 0) {
                foreach ($template->pickups as $item) {
                    $new_pickup = Pickup::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'customer_id' => $item->customer_id,
                        'contact_id' => $item->contact_id,
                        'contact_name' => $item->contact_name,
                        'contact_primary_phone' => $item->contact_primary_phone,
                        'pu_date1' => $item->pu_date1,
                        'pu_date2' => $item->pu_date2,
                        'pu_time1' => $item->pu_time1,
                        'pu_time2' => $item->pu_time2,
                        'bol_numbers' => $item->bol_numbers,
                        'po_numbers' => $item->po_numbers,
                        'ref_numbers' => $item->ref_numbers,
                        'seal_number' => $item->seal_number,
                        'special_instructions' => $item->special_instructions,
                        'type' => $item->type
                    ]);

                    $index = 0;
                    foreach ($template->routing as $route) {

                        if (($route->pickup_id ?? 0) > 0) {
                            if ($route->pickup_id === $item->id) {
                                $order_routing = new stdClass();
                                $order_routing->order_id = $order->id;
                                $order_routing->pickup_id = $new_pickup->id;
                                $order_routing->delivery_id = null;
                                $order_routing->type = 'pickup';

                                $routing[$index] = $order_routing;
                            }
                        }

                        $index++;
                    }
                }
            }

            if (count($template->deliveries) > 0) {
                foreach ($template->deliveries as $item) {
                    $new_delivery = Delivery::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'customer_id' => $item->customer_id,
                        'contact_id' => $item->contact_id,
                        'contact_name' => $item->contact_name,
                        'contact_primary_phone' => $item->contact_primary_phone,
                        'delivery_date1' => $item->delivery_date1,
                        'delivery_date2' => $item->delivery_date2,
                        'delivery_time1' => $item->delivery_time1,
                        'delivery_time2' => $item->delivery_time2,
                        'bol_numbers' => $item->bol_numbers,
                        'po_numbers' => $item->po_numbers,
                        'ref_numbers' => $item->ref_numbers,
                        'seal_number' => $item->seal_number,
                        'special_instructions' => $item->special_instructions,
                        'type' => $item->type
                    ]);

                    $index = 0;
                    foreach ($template->routing as $route) {
                        if (($route->delivery_id ?? 0) > 0) {
                            if ($route->delivery_id === $item->id) {
                                $order_routing = new stdClass();
                                $order_routing->order_id = $order->id;
                                $order_routing->pickup_id = null;
                                $order_routing->delivery_id = $new_delivery->id;
                                $order_routing->type = 'delivery';

                                $routing[$index] = $order_routing;
                            }
                        }

                        $index++;
                    }
                }
            }

            if (count($routing) > 0) {
                foreach ($routing as $route) {
                    Route::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'pickup_id' => $route->pickup_id,
                        'delivery_id' => $route->delivery_id,
                        'type' => $route->type
                    ]);
                }
            }

            if (($template->carrier_id ?? 0) > 0) {
                $carrier = Carrier::query()->where('id', $template->carrier_id)->first();

                if ($carrier) {
                    $carrier_code = strtoupper(($carrier->code_number ?? 0) === 0
                        ? ($carrier->code ?? '')
                        : ($carrier->code || '') . $carrier->code_number);
                    $carrier_name = ucwords($carrier->name);

                    date_default_timezone_set('America/Chicago');

                    $event_date = date('m/d/Y', time());
                    $event_time = date('Hi', time());
                    $event_notes = 'Assigned Carrier ' . $carrier_code . ' - ' . $carrier_name;

                    OrderEvent::query()->updateOrCreate([
                        'id' => null
                    ], [
                        'order_id' => $order->id,
                        'user_code_id' => $user_code_id,
                        'event_type_id' => 2,
                        'new_carrier_id' => $template->carrier_id,
                        'time' => $event_time,
                        'event_time' => $event_time,
                        'date' => $event_date,
                        'event_date' => $event_date,
                        'event_notes' => $event_notes
                    ]);
                }
            }

            $new_order = Order::query()->where('id', $order->id)->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
                'billing_documents',
                'billing_notes',
                'term',
                'order_ltl_units',
                'user_code'
            ])->first();

            return response()->json(['result' => 'OK', 'order' => $new_order]);
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderEvent(Request $request): JsonResponse
    {
        $ORDER_EVENT = new OrderEvent();
        $order_id = $request->order_id ?? 0;
        $event_type_id = $request->event_type_id ?? null;
        $shipper_id = isset($request->shipper_id) ? $request->shipper_id > 0 ? $request->shipper_id : null : null;
        $consignee_id = isset($request->consignee_id) ? $request->consignee_id > 0 ? $request->consignee_id : null : null;
        $arrived_customer_id = isset($request->arrived_customer_id) ? $request->arrived_customer_id > 0 ? $request->arrived_customer_id : null : null;
        $departed_customer_id = isset($request->departed_customer_id) ? $request->departed_customer_id > 0 ? $request->departed_customer_id : null : null;
        $old_carrier_id = isset($request->old_carrier_id) ? $request->old_carrier_id > 0 ? $request->old_carrier_id : null : null;
        $new_carrier_id = isset($request->new_carrier_id) ? $request->new_carrier_id > 0 ? $request->new_carrier_id : null : null;
        $time = $request->time ?? '';
        $event_time = $request->event_time ?? '';
        $date = $request->date ?? '';
        $event_date = $request->event_date ?? '';
        $user_code_id = $request->user_code_id ?? null;
        $event_location = $request->event_location ?? '';
        $event_notes = $request->event_notes ?? '';

        if ($order_id === 0) {
            return response()->json(['result' => 'ORDER ID NOT VALID', 'order_id' => $order_id]);
        }

        $order_event = $ORDER_EVENT->updateOrCreate([
            'order_id' => $order_id,
            'event_type_id' => $event_type_id,
            'shipper_id' => $shipper_id,
            'consignee_id' => $consignee_id,
            'arrived_customer_id' => $arrived_customer_id,
            'departed_customer_id' => $departed_customer_id,
            'old_carrier_id' => $old_carrier_id,
            'new_carrier_id' => $new_carrier_id,
        ], [
            'order_id' => $order_id,
            'event_type_id' => $event_type_id,
            'shipper_id' => $shipper_id,
            'consignee_id' => $consignee_id,
            'arrived_customer_id' => $arrived_customer_id,
            'departed_customer_id' => $departed_customer_id,
            'old_carrier_id' => $old_carrier_id,
            'new_carrier_id' => $new_carrier_id,
            'time' => $time,
            'event_time' => $event_time,
            'date' => $date,
            'event_date' => $event_date,
            'user_code_id' => $user_code_id,
            'event_location' => $event_location,
            'event_notes' => $event_notes
        ]);

        $order_events = $ORDER_EVENT->where('order_id', $order_id)
            ->with(['shipper', 'consignee', 'arrived_customer', 'departed_customer', 'old_carrier', 'new_carrier', 'event_type', 'user_code'])
            ->orderBy('updated_at', 'desc')->get();

        return response()->json(['result' => 'OK', 'order_event' => $order_event, 'order_events' => $order_events]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderPickup(Request $request): JsonResponse
    {
        $PICKUP = new Pickup();
        $ORDER = new Order();

        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'pickup';

        if ($order_id > 0) {
            if ($customer_id > 0) {
                $pickup = $PICKUP->updateOrCreate([
                    'id' => $id
                ], [
                    'order_id' => $order_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'contact_id' => $contact_id === '' ? null : $contact_id,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'contact_phone_ext' => $contact_phone_ext,
                    'contact_primary_phone' => $contact_primary_phone,
                    'pu_date1' => $pu_date1,
                    'pu_date2' => $pu_date2,
                    'pu_time1' => $pu_time1,
                    'pu_time2' => $pu_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $pickup = $PICKUP->where('id', $pickup->id ?? 0)->with(['customer'])->first();

                $order = $ORDER->where('id', $order_id)->with([
                    'bill_to_company',
                    'carrier',
                    'equipment',
                    'driver',
                    'notes_for_driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'documents',
                    'events',
                    'division',
                    'load_type',
                    'template',
                    'order_customer_ratings',
                    'order_carrier_ratings',
                    'billing_documents',
                    'billing_notes',
                    'term',
                    'order_ltl_units',
                    'user_code'
                ])->first();

                return response()->json(['result' => 'OK', 'pickup' => $pickup, 'order' => $order]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTemplate(Request $request): JsonResponse
    {
        $id = $request->id ?? null;

        Template::query()->where('id', $id)->delete();

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTemplateById(Request $request): JsonResponse
    {
        $id = $request->id ?? null;

        $template = Template::query()->where('id', $id)->with([
            'division',
            'load_type',
            'bill_to_company',
            'carrier',
            'equipment',
            'pickups',
            'deliveries',
            'routing',
            'notes_for_carrier',
            'internal_notes',
            'order_customer_ratings',
            'order_carrier_ratings'
        ])->first();

        return response()->json(['result' => 'OK', 'template' => $template]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $name = $request->name ?? '';
        $division_id = $request->division_id ?? null;
        $load_type_id = $request->load_type_id ?? null;
        $bill_to_customer_id = $request->bill_to_customer_id ?? null;
        $bill_to_contact_id = $request->bill_to_contact_id ?? null;
        $bill_to_contact_name = $request->bill_to_contact_name ?? '';
        $bill_to_contact_primary_phone = $request->bill_to_contact_primary_phone ?? 'work';
        $carrier_id = $request->carrier_id ?? null;
        $carrier_contact_id = $request->carrier_contact_id ?? null;
        $carrier_contact_name = $request->carrier_contact_name ?? '';
        $carrier_contact_primary_phone = $request->carrier_contact_primary_phone ?? 'work';
        $carrier_driver_id = $request->carrier_driver_id ?? null;
        $equipment_id = $request->equipment_id ?? null;
        $miles = $request->miles ?? 0.0;
        $haz_mat = $request->haz_mat ?? 0;
        $expedited = $request->expedited ?? 0;

        $TEMPLATE = Template::query();

        $template = $TEMPLATE->updateOrCreate([
            'id' => $id
        ], [
            'name' => $name,
            'division_id' => $division_id,
            'load_type_id' => $load_type_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'bill_to_contact_id' => $bill_to_contact_id,
            'bill_to_contact_name' => $bill_to_contact_name,
            'bill_to_contact_primary_phone' => $bill_to_contact_primary_phone === '' ? 'work' : $bill_to_contact_primary_phone,
            'carrier_id' => $carrier_id,
            'carrier_contact_id' => $carrier_contact_id,
            'carrier_contact_name' => $carrier_contact_name,
            'carrier_contact_primary_phone' => $carrier_contact_primary_phone === '' ? 'work' : $carrier_contact_primary_phone,
            'carrier_driver_id' => $carrier_driver_id,
            'equipment_id' => $equipment_id,
            'miles' => $miles,
            'haz_mat' => $haz_mat,
            'expedited' => $expedited
        ]);

        return response()->json(['result' => 'OK', 'template' => $template]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplatePickup(Request $request): JsonResponse
    {
        $TEMPLATE_PICKUP = TemplatePickup::query();

        $template_id = $request->template_id ?? 0;
        $id = $request->pickup_id ?? null;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'pickup';

        if ($template_id > 0) {
            if ($customer_id > 0) {
                $pickup = $TEMPLATE_PICKUP->updateOrCreate([
                    'id' => $id
                ], [
                    'template_id' => $template_id,
                    'customer_id' => $customer_id,
                    'contact_id' => $contact_id,
                    'contact_name' => $contact_name,
                    'contact_primary_phone' => $contact_primary_phone,
                    'type' => $type,
                    'pu_date1' => $pu_date1,
                    'pu_date2' => $pu_date2,
                    'pu_time1' => $pu_time1,
                    'pu_time2' => $pu_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $new_pickup = TemplatePickup::where('id', $pickup->id ?? 0)->with(['customer'])->first();

                return response()->json(['result' => 'OK', 'pickup' => $new_pickup]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeTemplatePickup(Request $request): JsonResponse
    {
        $id = $request->id ?? null;

        if ($id) {
            TemplatePickup::query()->where('id', $id)->delete();
            return response()->json(['result' => 'OK']);
        } else {
            return response()->json(['result' => 'NO PICKUP']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateDelivery(Request $request): JsonResponse
    {
        $TEMPLATE_DELIVERY = TemplateDelivery::query();

        $template_id = $request->template_id ?? 0;
        $id = $request->delivery_id ?? null;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'delivery';

        if ($template_id > 0) {
            if ($customer_id > 0) {
                $delivery = $TEMPLATE_DELIVERY->updateOrCreate([
                    'id' => $id
                ], [
                    'template_id' => $template_id,
                    'customer_id' => $customer_id,
                    'contact_id' => $contact_id,
                    'contact_name' => $contact_name,
                    'contact_primary_phone' => $contact_primary_phone,
                    'type' => $type,
                    'delivery_date1' => $delivery_date1,
                    'delivery_date2' => $delivery_date2,
                    'delivery_time1' => $delivery_time1,
                    'delivery_time2' => $delivery_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $new_delivery = TemplateDelivery::where('id', $delivery->id ?? 0)->with(['customer'])->first();

                return response()->json(['result' => 'OK', 'delivery' => $new_delivery]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeTemplateDelivery(Request $request): JsonResponse
    {
        $id = $request->id ?? null;

        if ($id) {
            TemplateDelivery::query()->where('id', $id)->delete();
            return response()->json(['result' => 'OK']);
        } else {
            return response()->json(['result' => 'NO DELIVERY']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateRouting(Request $request): JsonResponse
    {
        $TEMPLATE_ROUTE = TemplateRoute::query();

        $template_id = $request->template_id ?? 0;
        $routing = $request->routing ?? [];

        if ($template_id > 0) {
            $TEMPLATE_ROUTE->where('template_id', $template_id)->delete();

            if (count($routing) > 0) {
                for ($i = 0; $i < count($routing); $i++) {
                    $route = $routing[$i];

                    $TEMPLATE_ROUTE->updateOrCreate([
                        'id' => 0
                    ], [
                        'template_id' => $template_id,
                        'pickup_id' => $route['pickup_id'] ?? null,
                        'delivery_id' => $route['delivery_id'] ?? null,
                        'type' => $route['type'] ?? 'pickup'
                    ]);
                }
            }

            $new_routing = TemplateRoute::where('template_id', $template_id)->get();

            return response()->json(['result' => 'OK', 'routing' => $new_routing]);
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateNotesForCarrier(Request $request): JsonResponse
    {
        $NOTES_FOR_CARRIER = TemplateNoteForCarrier::query();

        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        if ($template_id > 0) {
            $note_for_carrier = $NOTES_FOR_CARRIER->updateOrCreate([
                'id' => $id
            ], [
                'template_id' => $template_id,
                'user_code_id' => $user_code_id,
                'text' => $text
            ]);

            $notes_for_carrier = TemplateNoteForCarrier::where('template_id', $template_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note_for_carrier, 'notes' => $notes_for_carrier]);
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTemplateNotesForCarrier(Request $request): JsonResponse
    {
        $NOTES_FOR_CARRIER = TemplateNoteForCarrier::query();

        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;

        $note_for_carrier = $NOTES_FOR_CARRIER->where('id', $id)->delete();
        $notes_for_carrier = TemplateNoteForCarrier::where('template_id', $template_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $note_for_carrier, 'notes' => $notes_for_carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateInternalNotes(Request $request): JsonResponse
    {
        $INTERNAL_NOTE = TemplateInternalNote::query();

        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        if ($template_id > 0) {
            $internal_note = $INTERNAL_NOTE->updateOrCreate([
                'id' => $id
            ], [
                'template_id' => $template_id,
                'user_code_id' => $user_code_id,
                'text' => $text,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $internal_notes = TemplateInternalNote::where('template_id', $template_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $internal_note, 'notes' => $internal_notes]);
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTemplateInternalNotes(Request $request): JsonResponse
    {
        $INTERNAL_NOTE = TemplateInternalNote::query();

        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;

        $internal_note = $INTERNAL_NOTE->where('id', $id)->delete();
        $internal_notes = TemplateInternalNote::where('template_id', $template_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $internal_note, 'notes' => $internal_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateMilesWaypoints(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $miles = $request->miles ?? 0;
        $waypoints = $request->waypoints ?? '';

        Template::query()->updateOrCreate([
            'id' => $id
        ], [
            'miles' => $miles,
            'waypoints' => $waypoints
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderMilesWaypoints(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $miles = $request->miles ?? 0;
        $waypoints = $request->waypoints ?? '';

        Order::query()->updateOrCreate([
            'id' => $id
        ], [
            'miles' => $miles,
            'waypoints' => $waypoints
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderDelivery(Request $request): JsonResponse
    {
        $DELIVERY = new Delivery();
        $ORDER = new Order();

        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'delivery';

        if ($order_id > 0) {
            if ($customer_id > 0) {
                $delivery = $DELIVERY->updateOrCreate([
                    'id' => $id
                ], [
                    'order_id' => $order_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'contact_id' => $contact_id === '' ? null : $contact_id,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'contact_phone_ext' => $contact_phone_ext,
                    'contact_primary_phone' => $contact_primary_phone,
                    'delivery_date1' => $delivery_date1,
                    'delivery_date2' => $delivery_date2,
                    'delivery_time1' => $delivery_time1,
                    'delivery_time2' => $delivery_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $delivery = $DELIVERY->where('id', $delivery->id ?? 0)->with(['customer'])->first();

                $order = $ORDER->where('id', $order_id)->with([
                    'bill_to_company',
                    'carrier',
                    'equipment',
                    'driver',
                    'notes_for_driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'documents',
                    'events',
                    'division',
                    'load_type',
                    'template',
                    'order_customer_ratings',
                    'order_carrier_ratings',
                    'billing_documents',
                    'billing_notes',
                    'term',
                    'order_ltl_units',
                    'user_code'
                ])->first();

                return response()->json(['result' => 'OK', 'delivery' => $delivery, 'order' => $order]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOrderPickup(Request $request): JsonResponse
    {
        $PICKUP = new Pickup();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;

        $pickup = $PICKUP->where('id', $id)->delete();
        $order = $ORDER->where('id', $order_id)->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'order_ltl_units',
            'user_code'
        ])->first();

        return response()->json(['result' => 'OK', 'pickup' => $pickup, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOrderDelivery(Request $request): JsonResponse
    {
        $DELIVERY = new Delivery();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;

        $delivery = $DELIVERY->where('id', $id)->delete();
        $order = $ORDER->where('id', $order_id)->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'order_ltl_units',
            'user_code'
        ])->first();

        return response()->json(['result' => 'OK', 'delivery' => $delivery, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderRouting(Request $request): JsonResponse
    {
        $ROUTE = new Route();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $routing = $request->routing ?? [];

        if ($order_id > 0) {
            $ROUTE->where('order_id', $order_id)->delete();

            if (count($routing) > 0) {
                for ($i = 0; $i < count($routing); $i++) {
                    $route = $routing[$i];

                    $ROUTE->updateOrCreate([
                        'id' => 0
                    ], [
                        'order_id' => $order_id,
                        'pickup_id' => $route['pickup_id'] ?? null,
                        'delivery_id' => $route['delivery_id'] ?? null,
                        'type' => $route['type'] ?? 'pickup'
                    ]);
                }
            }
            $order = $ORDER->where('id', $order_id)->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
                'billing_documents',
                'billing_notes',
                'term',
                'order_ltl_units',
                'user_code'
            ])->first();

            return response()->json(['result' => 'OK', 'order' => $order]);
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrdersRelatedData()
    {
        $CUSTOMER = new Customer();
        $CARRIER = new Carrier();
        $LOAD_TYPE = new LoadType();
        $EQUIPMENT = new Equipment();
        $RATE_TYPE = new RateType();
        $EVENT_TYPE = new EventType();

        $customers = $CUSTOMER->get();
        $carriers = $CARRIER->get();
        $load_types = $LOAD_TYPE->get();
        $equipments = $EQUIPMENT->get();
        $rate_types = $RATE_TYPE->get();
        $event_types = $EVENT_TYPE->get();

        return response()->json([
            'result' => 'OK',
            'customers' => $customers,
            'carriers' => $carriers,
            'load_types' => $load_types,
            'equipments' => $equipments,
            'rate_types' => $rate_types,
            'event_types' => $event_types
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitOrderImport(Request $request)
    {
        $order_number = $request->order ?? '';
        $trip_number = $request->trip ?? '';
        $load_type_id = $request->loadTypeId ?? 0;
        $haz_mat = $request->hazMat ?? 0;
        $expedited = $request->expedited ?? 0;
        $miles = $request->miles ?? 0;
        $order_date_time = $request->orderDateTime ?? '';
        $bill_to_customer_id = $request->billToCustomerId ?? 0;
        $carrier_id = $request->carrierId ?? 0;
        $equipment_type_id = $request->equipmentTypeId ?? 0;
        $shipper_customer_id = $request->shipperCustomerId ?? 0;
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $consignee_customer_id = $request->consigneeCustomerId ?? 0;
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $customer_rating = $request->customerRating ?? null;
        $carrier_rating = $request->carrierRating ?? null;
        $loaded_event = $request->loadedEvent ?? null;
        $delivered_event = $request->deliveredEvent ?? null;
        $user_code_id = $request->user_code_id ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => 0
        ], [
            'order_number' => $order_number,
            'trip_number' => $trip_number,
            'user_code_id' => $user_code_id,
            'order_date_time' => $order_date_time,
            'load_type_id' => $load_type_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'carrier_id' => $carrier_id,
            'equipment_id' => $equipment_type_id,
            'miles' => $miles,
            'haz_mat' => $haz_mat,
            'expedited' => $expedited
        ]);

        $order_id = $order->id;

        if ($shipper_customer_id > 0) {
            $PICKUP = new Pickup();

            $pickup = $PICKUP->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'customer_id' => $shipper_customer_id,
                'pu_date1' => $pu_date1,
                'pu_time1' => $pu_time1,
                'pu_date2' => $pu_date2,
                'pu_time2' => $pu_time2,
                'ref_numbers' => $ref_numbers
            ]);
        }

        if ($consignee_customer_id > 0) {
            $DELIVERY = new Delivery();

            $delivery = $DELIVERY->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'customer_id' => $consignee_customer_id,
                'delivery_date1' => $delivery_date1,
                'delivery_time1' => $delivery_time1,
                'delivery_date2' => $delivery_date2,
                'delivery_time2' => $delivery_time2
            ]);
        }

        if ($shipper_customer_id > 0 && $consignee_customer_id > 0) {
            $ROUTE = new Route();

            $route = $ROUTE->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'pickup_id' => $pickup->id,
                'type' => 'pickup'
            ]);

            $route = $ROUTE->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'delivery_id' => $delivery->id,
                'type' => 'delivery'
            ]);
        }

        if ($customer_rating['total_charges'] > 0) {
            $ORDER_CUSTOMER_RATING = new OrderCustomerRating();

            $ORDER_CUSTOMER_RATING->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'rate_type_id' => $customer_rating['rateTypeId'],
                'description' => $customer_rating['description'],
                'pieces' => $customer_rating['pieces'],
                'pieces_unit' => 'sk',
                'weight' => $customer_rating['weight'],
                'total_charges' => $customer_rating['total_charges']
            ]);
        }

        if ($carrier_rating['total_charges'] > 0) {
            $ORDER_CARRIER_RATING = new OrderCarrierRating();

            $ORDER_CARRIER_RATING->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'rate_type_id' => $carrier_rating['rateTypeId'],
                'description' => $carrier_rating['description'],
                'pieces' => $carrier_rating['pieces'],
                'pieces_unit' => 'sk',
                'weight' => $carrier_rating['weight'],
                'total_charges' => $carrier_rating['total_charges']
            ]);
        }

        $ORDER_EVENT = new OrderEvent();

        $ORDER_EVENT->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'event_type_id' => $loaded_event['eventTypeId'],
            'time' => $loaded_event['time'],
            'event_time' => $loaded_event['eventTime'],
            'date' => $loaded_event['date'],
            'event_date' => $loaded_event['eventDate'],
            'event_location' => $loaded_event['eventLocation'],
            'event_notes' => $loaded_event['eventNotes'],
            'user_code_id' => $user_code_id
        ]);

        $ORDER_EVENT->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'event_type_id' => $delivered_event['eventTypeId'],
            'time' => $delivered_event['time'],
            'event_time' => $delivered_event['eventTime'],
            'date' => $delivered_event['date'],
            'event_date' => $delivered_event['eventDate'],
            'event_location' => $delivered_event['eventLocation'],
            'event_notes' => $delivered_event['eventNotes'],
            'user_code_id' => $user_code_id
        ]);

        $newOrder = $ORDER->where('id', $order_id)->with([
            'pickups',
            'deliveries',
            'events',
            'order_customer_ratings',
            'order_carrier_ratings',
            'routing',
            'user_code'
        ]);

        return response()->json([
            'result' => 'OK',
            'order' => $newOrder
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitOrderImport2(Request $request): JsonResponse
    {
        $list = $request->list ?? [];

        if (count($list) > 0) {
            for ($i = 0; $i < count($list); $i++) {
                $item = $list[$i];

                $user_code_id = $item['user_code_id'] ?? null;
                $order_number = $item['order_number'];
                $trip_number = $item['trip_number'];
                $load_type_id = ($item['load_type_id'] ?? 0) === 0 ? null : $item['load_type_id'];
                $haz_mat = $item['haz_mat'];
                $expedited = $item['expedited'];
                $miles = ($item['miles'] ?? 0) * 1609.34;
                $order_date_time = $item['order_date_time'];
                $bill_to_customer_id = ($item['bill_to_customer_id'] ?? 0) === 0 ? null : $item['bill_to_customer_id'];
                $carrier_id = ($item['carrier_id'] ?? 0) === 0 ? null : $item['carrier_id'];
                $equipment_id = ($item['equipment_id'] ?? 0) === 0 ? null : $item['equipment_id'];
                //                $order_pickups = $item['pickups'] ?? [];
                //                $order_deliveries = $item['deliveries'] ?? [];
                $order_routing = $item['routing'] ?? [];
                $order_customer_rating = $item['customer_rating'] ?? [];
                $order_carrier_rating = $item['carrier_rating'] ?? [];
                $order_events = $item['events'] ?? [];
                $order_internal_notes = $item['internal_notes'] ?? [];
                $order_notes_for_carrier = $item['notes_for_carrier'] ?? [];

                $order = Order::updateOrCreate([
                    'id' => 0
                ], [
                    'order_number' => $order_number,
                    'trip_number' => $trip_number,
                    'load_type_id' => $load_type_id,
                    'haz_mat' => $haz_mat,
                    'expedited' => $expedited,
                    'miles' => $miles,
                    'order_date_time' => $order_date_time,
                    'bill_to_customer_id' => $bill_to_customer_id,
                    'carrier_id' => $carrier_id,
                    'equipment_id' => $equipment_id,
                    'is_imported' => 1,
                    'user_code_id' => $user_code_id
                ]);

                $order_id = $order->id;

                if ($order_id > 0) {
                    for ($p = 0; $p < count($order_routing); $p++) {
                        $route_item = $order_routing[$p];
                        $route_type = $route_item['type'];

                        if ($route_type === 'pickup') {
                            $pickup = Pickup::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'customer_id' => $route_item['customer_id'],
                                'pu_date1' => $route_item['pu_date1'] ?? '',
                                'pu_time1' => $route_item['pu_time1'] ?? '',
                                'pu_date2' => $route_item['pu_date2'] ?? '',
                                'pu_time2' => $route_item['pu_time2'] ?? '',
                                'po_numbers' => $route_item['po_numbers'] ?? '',
                                'bol_numbers' => $route_item['bol_numbers'] ?? '',
                                'ref_numbers' => $route_item['ref_numbers'] ?? '',
                                'seal_number' => $route_item['seal_number'] ?? ''
                            ]);

                            Route::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'pickup_id' => $pickup->id,
                                'delivery_id' => null,
                                'type' => $route_type
                            ]);
                        } else {
                            $delivery = Delivery::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'customer_id' => $route_item['customer_id'],
                                'delivery_date1' => $route_item['delivery_date1'] ?? '',
                                'delivery_time1' => $route_item['delivery_time1'] ?? '',
                                'delivery_date2' => $route_item['delivery_date2'] ?? '',
                                'delivery_time2' => $route_item['delivery_time2'] ?? '',
                                'po_numbers' => $route_item['po_numbers'] ?? '',
                                'bol_numbers' => $route_item['bol_numbers'] ?? '',
                                'ref_numbers' => $route_item['ref_numbers'] ?? '',
                                'seal_number' => $route_item['seal_number'] ?? ''
                            ]);

                            Route::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'pickup_id' => null,
                                'delivery_id' => $delivery->id,
                                'type' => $route_type
                            ]);
                        }
                    }

                    for ($cus = 0; $cus < count($order_customer_rating); $cus++) {
                        $customer_rating_item = $order_customer_rating[$cus];

                        OrderCustomerRating::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'rate_type_id' => $customer_rating_item['rate_type_id'],
                            'description' => $customer_rating_item['description'],
                            'pieces' => $customer_rating_item['pieces'] ?? 0.00,
                            'pieces_unit' => $item['pieces_unit'] ?? 'sk',
                            'weight' => $customer_rating_item['weight'] ?? 0.00,
                            'total_charges' => $customer_rating_item['total_charges'] ?? 0.00
                        ]);
                    }

                    for ($car = 0; $car < count($order_carrier_rating); $car++) {
                        $carrier_rating_item = $order_carrier_rating[$car];

                        OrderCarrierRating::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'rate_type_id' => $carrier_rating_item['rate_type_id'],
                            'description' => $carrier_rating_item['description'],
                            'pieces' => $carrier_rating_item['pieces'] ?? 0.00,
                            'pieces_unit' => $carrier_rating_item['pieces_unit'] ?? 'sk',
                            'weight' => $carrier_rating_item['weight'] ?? 0.00,
                            'total_charges' => $carrier_rating_item['total_charges'] ?? 0.00
                        ]);
                    }

                    for ($e = 0; $e < count($order_events); $e++) {
                        $event_item = $order_events[$e];

                        OrderEvent::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $event_item['user_code_id'] ?? null,
                            'event_type_id' => $event_item['event_type_id'],
                            'shipper_id' => $event_item['shipper_id'] ?? null,
                            'consignee_id' => $event_item['consignee_id'] ?? null,
                            'old_carrier_id' => $event_item['old_carrier_id'] ?? null,
                            'new_carrier_id' => $event_item['new_carrier_id'] ?? null,
                            'time' => $event_item['time'] ?? '',
                            'date' => $event_item['date'] ?? '',
                            'event_time' => $event_item['event_time'] ?? '',
                            'event_date' => $event_item['event_date'] ?? '',
                            'event_location' => $event_item['event_location'] ?? '',
                            'event_notes' => $event_item['event_notes'] ?? ''
                        ]);
                    }

                    for ($n = 0; $n < count($order_internal_notes); $n++) {
                        $internal_note = $order_internal_notes[$n];

                        InternalNotes::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $internal_note['user_code_id'] ?? null,
                            'date_time' => $internal_note['date_time'] ?? null,
                            'text' => $internal_note['text'] ?? '',
                        ]);
                    }

                    for ($n = 0; $n < count($order_notes_for_carrier); $n++) {
                        $note_for_carrier = $order_notes_for_carrier[$n];

                        NotesForCarrier::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $note_for_carrier['user_code_id'] ?? null,
                            'date_time' => $note_for_carrier['date_time'] ?? null,
                            'text' => $note_for_carrier['text'] ?? '',
                        ]);
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

    public function getOrderCarrierByCode(Request $request): JsonResponse
    {
        $code = $request->code ?? '';
        $division_type = strtolower($request->division_type ?? '');
        $carrier = null;

        if ($division_type !== '') {
            if ($division_type === 'brokerage') {
                $CARRIER = Carrier::query();

                $carrier = Carrier::whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
                    ->with([
                        'contacts',
                        'drivers',
                        'insurances'
                    ])->first();

                if ($carrier) {
                    return response()->json(['result' => 'OK', 'carrier' => $carrier, 'owner_type' => 'carrier']);
                } else {
                    $carrier = Carrier::whereHas('drivers', function ($query) use ($code) {
                        return $query->whereRaw("code like '$code%'");
                    })
                        ->with([
                            'contacts',
                            'drivers',
                            'insurances'
                        ])->first();

                    if ($carrier) {
                        return response()->json(['result' => 'OK', 'carrier' => $carrier, 'owner_type' => 'carrier', 'driver_code' => strtoupper($code)]);
                    } else {
                        $AGENT = Agent::query();

                        $carrier = Carrier::whereRaw("code like '$code%'")
                            ->with([
                                'contacts',
                                'drivers',
                                'insurances'
                            ])->first();

                        if (!$carrier) {
                            $carrier = Carrier::whereHas('drivers', function ($query) use ($code) {
                                return $query->whereRaw("code like '$code%'");
                            })->with([
                                'contacts',
                                'drivers',
                                'insurances'
                            ])->first();
                        } else {
                            $code = '';
                        }

                        return response()->json(['result' => 'OK', 'carrier' => $carrier, 'owner_type' => 'agent', 'driver_code' => strtoupper($code)]);
                    }
                }
            } elseif ($division_type === 'company') {
                $DRIVER = Driver::query();

                $carrier = $DRIVER->whereRaw("code like '$code%'")
                    ->whereNull(['carrier_id', 'agent_id'])
                    ->with(['contacts', 'tractor', 'trailer'])->first();

                return response()->json(['result' => 'OK', 'carrier' => $carrier, 'owner_type' => $carrier?->owner_type]);
            } else {
                return response()->json(['result' => 'NO DIVISION']);
            }
        } else {
            return response()->json(['result' => 'NO DIVISION']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoutingBol(Request $request): JsonResponse
    {
        $order_id = $request->order_id ?? null;
        $name = $request->name ?? '';

        $ROUTE = Route::query();

        $ROUTE->where('order_id', $order_id);
        $ROUTE->where(function ($query) use ($name) {
            $query->whereHas('pickup', function ($query1) use ($name) {
                $query1->whereHas('customer', function ($query2) use ($name) {
                    $query2->whereRaw("1 = 1")
                        ->whereRaw("CONCAT(`code`,`code_number`) like '%$name%'")
                        ->orWhereRaw("LOWER(name) like '%$name%'");
                });
            })
                ->orWhereHas('delivery', function ($query1) use ($name) {
                    $query1->whereHas('customer', function ($query2) use ($name) {
                        $query2->whereRaw("1 = 1")
                            ->whereRaw("CONCAT(`code`,`code_number`) like '%$name%'")
                            ->orWhereRaw("LOWER(name) like '%$name%'");
                    });
                });
        });


        $routing = $ROUTE->with(['pickup', 'delivery'])->get();

        return response()->json(['result' => 'OK', 'routing' => $routing]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function arrayTest(): JsonResponse
    {
        $date_time1 = '08/20/2015 09:58:11';
        $date_time2 = '08/20/2015 09:58:11';
        $date_time3 = '11/06/2021 07:45:00';
        $date_time4 = '11/06/2021 07:45:00';

        $date1 = DateTime::createFromFormat('m/d/Y H:i:s', $date_time1)->getTimestamp();
        $date2 = strtotime($date_time2);
        $date3 = DateTime::createFromFormat('m/d/Y H:i:s', $date_time3)->getTimestamp();
        $date4 = strtotime($date_time4);

        return response()->json([
            'date1' => $date1,
            'date2' => $date2,
            'date3' => $date3,
            'date4' => $date4
        ]);
    }

    public function saveInvoiceCustomerCheckNumber(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $customer_check_number = $request->customer_check_number ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'customer_check_number' => $customer_check_number
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceCustomerDateReceived(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $customer_date_received = $request->customer_date_received ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'customer_date_received' => $customer_date_received
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceCarrierReceivedDate(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $invoice_received_date = $request->invoice_received_date ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'invoice_received_date' => $invoice_received_date
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceNumber(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $invoice_number = $request->invoice_number ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'invoice_number' => $invoice_number
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceTerm(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $term_id = $request->term_id ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'term_id' => $term_id
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceDatePaid(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $invoice_date_paid = $request->invoice_date_paid ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'invoice_date_paid' => $invoice_date_paid
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function saveInvoiceCarrierCheckNumber(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $carrier_check_number = $request->carrier_check_number ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => $id
        ], [
            'carrier_check_number' => $carrier_check_number
        ]);

        return response()->json(['result' => 'OK']);
    }    
}


function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}
