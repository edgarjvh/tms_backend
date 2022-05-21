<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Equipment;
use App\Models\EventType;
use App\Models\LoadType;
use App\Models\Order;
use App\Models\OrderCarrierRating;
use App\Models\OrderCustomerRating;
use App\Models\OrderEvent;
use App\Models\Pickup;
use App\Models\RateType;
use App\Models\Route;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OrdersController extends Controller
{
    public function getOrders(): JsonResponse
    {
        $ORDER = new Order();

        $orders = $ORDER->where('is_imported', 0)->with([
            'bill_to_company',
            'carrier',
//            'equipment',
//            'driver',
//            'notes_for_driver',
//            'notes_for_carrier',
//            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
//            'documents',
            'events',
//            'division',
//            'load_type',
//            'template',
//            'order_customer_ratings',
//            'order_carrier_ratings',
//            'billing_documents',
//            'billing_notes',
//            'term'
        ])

            ->orderBy('order_number')
            ->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCustomer(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $customer_id = $request->customer_id ?? 0;
        $date_start = $request->date_start ?? '';
        $date_end = $request->date_end ?? '';
        $bill_to_code = $request->bill_to_code ?? '';
        $customer_code = $request->customer_code ?? '';

        $ORDER->whereRaw("1 = 1")->select(['id', 'order_number', 'bill_to_customer_id', 'carrier_id', 'order_date_time']);
        $ORDER->whereHas('bill_to_company');
        if ($customer_id > 0) {
            $ORDER->where('bill_to_customer_id', $customer_id);
        } else {
            if (trim($bill_to_code) !== '') {
                $ORDER->whereHas('bill_to_company', function ($query1) use ($bill_to_code) {
                    $query1->whereRaw("CONCAT(`code`, `code_number`) LIKE '$bill_to_code%'");
                });
            }
        }

        if (trim($customer_code) !== '') {
            $ORDER->whereHas('pickups', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });

            $ORDER->whereHas('deliveries', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
        } else {
            if ($date_start !== '') {
                $ORDER->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
            }

            if ($date_end !== '') {
                $ORDER->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
            }
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'order_customer_ratings',
            'order_carrier_ratings',
        ]);

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCarrier(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $customer_id = $request->customer_id ?? 0;
        $date_start = $request->date_start ?? '';
        $date_end = $request->date_end ?? '';
        $bill_to_code = $request->bill_to_code ?? '';
        $customer_code = $request->customer_code ?? '';

        $ORDER->whereRaw("1 = 1")->select(['id', 'order_number', 'bill_to_customer_id', 'carrier_id', 'order_date_time']);

        if ($customer_id > 0) {
            $ORDER->where('carrier_id', $customer_id);
        }

        if (trim($bill_to_code) !== '') {
            $ORDER->whereHas('bill_to_company', function ($query1) use ($bill_to_code) {
                $query1->whereRaw("CONCAT(`code`, `code_number`) LIKE '$bill_to_code%'");
            });
        }

        if (trim($customer_code) !== '') {
            $ORDER->whereHas('pickups', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });

            $ORDER->whereHas('deliveries', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
        } else {
            if ($date_start !== '') {
                $ORDER->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
            }

            if ($date_end !== '') {
                $ORDER->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
            }
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'order_customer_ratings',
            'order_carrier_ratings',
        ]);

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCustomer(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $customer_id = $request->customer_id ?? 0;
        $date_start = $request->date_start ?? '';
        $date_end = $request->date_end ?? '';
        $bill_to_code = $request->bill_to_code ?? '';
        $customer_code = $request->customer_code ?? '';

        $ORDER->whereRaw("1 = 1")->select(['id', 'order_number', 'bill_to_customer_id', 'carrier_id', 'order_date_time']);

        if ($customer_id > 0) {
            $ORDER->whereHas('bill_to_company', function ($query1) use ($customer_id) {
                $query1->where('id', $customer_id);
            });

            $ORDER->orWhereHas('pickups', function ($query1) use ($customer_id) {
                $query1->whereHas('customer', function ($query2) use ($customer_id) {
                    $query2->where('id', $customer_id);
                });
            });

            $ORDER->orWhereHas('deliveries', function ($query1) use ($customer_id) {
                $query1->whereHas('customer', function ($query2) use ($customer_id) {
                    $query2->where('id', $customer_id);
                });
            });
        }

        if (trim($bill_to_code) !== '') {
            $ORDER->whereHas('bill_to_company', function ($query1) use ($bill_to_code) {
                $query1->whereRaw("CONCAT(`code`, `code_number`) LIKE '$bill_to_code%'");
            });
        }

        if (trim($customer_code) !== '') {
            $ORDER->whereHas('pickups', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });

            $ORDER->whereHas('deliveries', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
        } else {
            if ($date_start !== '') {
                $ORDER->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
            }

            if ($date_end !== '') {
                $ORDER->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
            }
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'order_customer_ratings',
            'order_carrier_ratings',
        ]);

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCarrier(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $customer_id = $request->customer_id ?? 0;
        $date_start = $request->date_start ?? '';
        $date_end = $request->date_end ?? '';
        $bill_to_code = $request->bill_to_code ?? '';
        $customer_code = $request->customer_code ?? '';

        $ORDER->whereRaw("1 = 1")->select(['id', 'order_number', 'bill_to_customer_id', 'carrier_id', 'order_date_time']);

        if ($customer_id > 0) {
            $ORDER->where('carrier_id', $customer_id);
        }

        if (trim($bill_to_code) !== '') {
            $ORDER->whereHas('bill_to_company', function ($query1) use ($bill_to_code) {
                $query1->whereRaw("CONCAT(`code`, `code_number`) LIKE '$bill_to_code%'");
            });
        }

        if (trim($customer_code) !== '') {
            $ORDER->whereHas('pickups', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });

            $ORDER->whereHas('deliveries', function ($query1) use ($customer_code) {
                $query1->whereHas('customer', function ($query2) use ($customer_code) {
                    $query2->whereRaw("CONCAT(`code`, `code_number`) LIKE '$customer_code%'");
                });
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
        } else {
            if ($date_start !== '') {
                $ORDER->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
            }

            if ($date_end !== '') {
                $ORDER->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
            }
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'order_customer_ratings',
            'order_carrier_ratings',
        ]);

        $orders = $ORDER->get();

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

        $order = $ORDER->where('id', $id)
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
                'term'
            ])
            ->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByOrderNumber(Request $request): JsonResponse
    {
        $ORDER = new Order();

        $order_number = $request->order_number ?? 0;

        $order = $ORDER->where('order_number', $order_number)
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
                'term'
            ])
            ->first();

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

        $order = $ORDER->where('trip_number', $trip_number)
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
                'term'
            ])
            ->first();

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

        $order_number = (int)($request->order_number ?? 0);
        $ae_number = $request->ae_number ?? 0;
        $trip_number = (int)($request->trip_number ?? 0);
        $division_id = isset($request->division_id) ? $request->division_id > 0 ? $request->division_id : null : null;
        $load_type_id = isset($request->load_type_id) ? $request->load_type_id > 0 ? $request->load_type_id : null : null;
        $template_id = isset($request->template_id) ? $request->template_id > 0 ? $request->template_id : null : null;
        $bill_to_customer_id = isset($request->bill_to_customer_id) ? $request->bill_to_customer_id > 0 ? $request->bill_to_customer_id : null : null;
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id > 0 ? $request->carrier_id : null : null;
        $carrier_load = $request->carrier_load ?? '';
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

        $last_order_number = $ORDER->max('order_number');
        $last_trip_number = $ORDER->max('trip_number');

        if ($order_number === 0) {
            if ($last_order_number) {
                $order_number = $last_order_number + 1;
            } else {
                $order_number = 1;
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

        $order = $ORDER->updateOrCreate([
            'order_number' => $order_number
        ], [
            'ae_number' => $ae_number,
            'trip_number' => $trip_number,
            'division_id' => $division_id,
            'load_type_id' => $load_type_id,
            'template_id' => $template_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'carrier_id' => $carrier_id,
            'carrier_load' => $carrier_load,
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
            'customer_check_number' => $customer_check_number,
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
            'invoice_carrier_approved' => $invoice_carrier_approved
        ]);

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
                'term'
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $newOrder, 'order_number' => $order_number]);
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
        $user_id = $request->user_id ?? 0;
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
            'user_id' => $user_id,
            'event_location' => $event_location,
            'event_notes' => $event_notes
        ]);

        $order_events = $ORDER_EVENT->where('order_id', $order_id)
            ->with(['shipper', 'consignee', 'arrived_customer', 'departed_customer', 'old_carrier', 'new_carrier', 'event_type'])
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
                    'term'
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
    public function saveOrderDelivery(Request $request): JsonResponse
    {
        $DELIVERY = new Delivery();
        $ORDER = new Order();

        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
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
                    'delivery_date1' => $delivery_date1,
                    'delivery_date2' => $delivery_date2,
                    'delivery_time1' => $delivery_time1,
                    'delivery_time2' => $delivery_time2,
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
                    'term'
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
            'term'
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
            'term'
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
                'term'
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

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => 0
        ], [
            'order_number' => $order_number,
            'trip_number' => $trip_number,
            'ae_number' => rand(1, 100),
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
            'event_notes' => $loaded_event['eventNotes']
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
            'event_notes' => $delivered_event['eventNotes']
        ]);

        $newOrder = $ORDER->where('id', $order_id)->with([
            'pickups',
            'deliveries',
            'events',
            'order_customer_ratings',
            'order_carrier_ratings',
            'routing'
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
    public function submitOrderImport2(Request $request): JsonResponse{
        $list = $request->list ?? [];

        if (count($list) > 0){
            for ($i = 0; $i < count($list); $i++){
                $item = $list[$i];

                $order = $item['order'];

                $trip = $item['trip'];
                $load_type_id = ($item['loadTypeId'] ?? 0) === 0 ? null : $item['loadTypeId'];
                $haz_mat = $item['hazMat'];
                $expedited = $item['expedited'];
                $miles = ($item['miles'] ?? 0) * 1609.34;
                $order_date_time = $item['orderDateTime'];
                $bill_to_customer_id = ($item['billToCustomerId'] ?? 0) === 0 ? null : $item['billToCustomerId'];
                $carrier_id = ($item['carrierId'] ?? 0) === 0 ? null : $item['carrierId'];
                $equipment_id = ($item['equipmentTypeId'] ?? 0) === 0 ? null : $item['equipmentTypeId'];
                $shipper_customer_id = $item['shipperCustomerId'] ?? 0;
                $pu_date1 = $item['pu_date1'] ?? '';
                $pu_date2 = $item['pu_date2'] ?? '';
                $pu_time1 = $item['pu_time1'] ?? '';
                $pu_time2 = $item['pu_time2'] ?? '';
                $ref_numbers = $item['ref_numbers'] ?? '';
                $consignee_customer_id = $item['consigneeCustomerId'] ?? 0;
                $delivery_date1 = $item['delivery_date1'] ?? '';
                $delivery_date2 = $item['delivery_date2'] ?? '';
                $delivery_time1 = $item['delivery_time1'] ?? '';
                $delivery_time2 = $item['delivery_time2'] ?? '';
                $order_customer_rating = $item['customerRating'] ?? null;
                $order_carrier_rating = $item['carrierRating'] ?? null;
                $loaded_event = $item['loadedEvent'] ?? null;
                $delivered_event = $item['deliveredEvent'] ?? null;

                $order_id = 0;

                try {
                    $saved_order = Order::updateOrCreate([
                        'id' => 0
                    ],[
                        'order_number' => $order,
                        'trip_number' => $trip,
                        'load_type_id' => $load_type_id,
                        'haz_mat' => $haz_mat,
                        'expedited' => $expedited,
                        'miles' => $miles,
                        'order_date_time' => $order_date_time,
                        'bill_to_customer_id' => $bill_to_customer_id,
                        'carrier_id' => $carrier_id,
                        'equipment_id' => $equipment_id,
                        'is_imported' => 1
                    ]);

                    $order_id = $saved_order->id;
                } catch (Throwable | Exception $e) {

                }

                if ($order_id > 0){
                    $pickup = null;
                    $delivery = null;

                    if ($shipper_customer_id > 0){
                        try {
                            $pickup = Pickup::updateOrCreate([
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
                        }catch (Throwable | Exception $e){

                        }
                    }

                    if ($consignee_customer_id > 0) {
                        try {
                            $delivery = Delivery::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'customer_id' => $consignee_customer_id,
                                'delivery_date1' => $delivery_date1,
                                'delivery_time1' => $delivery_time1,
                                'delivery_date2' => $delivery_date2,
                                'delivery_time2' => $delivery_time2
                            ]);
                        }catch (Throwable | Exception $e){

                        }
                    }

                    if ($shipper_customer_id > 0 && $consignee_customer_id > 0) {
                        try{
                            if (($pickup->id ?? 0) > 0){
                                Route::updateOrCreate([
                                    'id' => 0
                                ], [
                                    'order_id' => $order_id,
                                    'pickup_id' => $pickup->id,
                                    'type' => 'pickup'
                                ]);
                            }
                        }catch (Throwable | Exception $e){

                        }

                        try {
                            if (($delivery->id ?? 0) > 0){
                                Route::updateOrCreate([
                                    'id' => 0
                                ], [
                                    'order_id' => $order_id,
                                    'delivery_id' => $delivery->id,
                                    'type' => 'delivery'
                                ]);
                            }
                        }catch (Throwable | Exception $e){

                        }
                    }

                    try {
                        if ($order_customer_rating['total_charges'] > 0) {
                            OrderCustomerRating::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'rate_type_id' => $order_customer_rating['rateTypeId'],
                                'description' => $order_customer_rating['description'],
                                'pieces' => $order_customer_rating['pieces'],
                                'pieces_unit' => 'sk',
                                'weight' => $order_customer_rating['weight'],
                                'total_charges' => $order_customer_rating['total_charges']
                            ]);
                        }
                    }catch (Throwable | Exception $e){

                    }

                    try {
                        if ($order_carrier_rating['total_charges'] > 0) {
                            OrderCarrierRating::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'rate_type_id' => $order_carrier_rating['rateTypeId'],
                                'description' => $order_carrier_rating['description'],
                                'pieces' => $order_carrier_rating['pieces'],
                                'pieces_unit' => 'sk',
                                'weight' => $order_carrier_rating['weight'],
                                'total_charges' => $order_carrier_rating['total_charges']
                            ]);
                        }
                    }catch (Throwable | Exception $e){

                    }

                    try {
                        OrderEvent::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'event_type_id' => $loaded_event['eventTypeId'],
                            'time' => $loaded_event['time'],
                            'event_time' => $loaded_event['eventTime'],
                            'date' => $loaded_event['date'],
                            'event_date' => $loaded_event['eventDate'],
                            'event_location' => $loaded_event['eventLocation'],
                            'event_notes' => $loaded_event['eventNotes']
                        ]);
                    }catch (Throwable | Exception $e){

                    }

                    try {
                        OrderEvent::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'event_type_id' => $delivered_event['eventTypeId'],
                            'time' => $delivered_event['time'],
                            'event_time' => $delivered_event['eventTime'],
                            'date' => $delivered_event['date'],
                            'event_date' => $delivered_event['eventDate'],
                            'event_location' => $delivered_event['eventLocation'],
                            'event_notes' => $delivered_event['eventNotes']
                        ]);
                    }catch (Throwable | Exception $e){

                    }
                }

            }

            return response()->json(['result' => 'OK']);
        }else{
            return response()->json(['result' => 'NO LIST']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function arrayTest(): JsonResponse{
        $arr = [];

        for ($i = 0; $i < 10; $i++){
            $obj = (object)[];
            $obj->order_number = $i;

            $msg = [];
            $msg[] = 'first';
            $msg[] = 'second';
            $msg[] = 'third';
            $msg[] = 'fourth';

            $obj->messages = $msg;

            $arr[] = $obj;
        }

        return response()->json($arr);
    }
}
