<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Delivery;
use App\Order;
use App\OrderEvent;
use App\Pickup;
use App\Route;
use Illuminate\Http\Request;
use stdClass;

class OrdersController extends Controller
{
    public function getOrders(Request $request)
    {
        $orders = Order::with([
            'bill_to_company',
            'carrier',
            'driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template'
        ])
            ->orderBy('order_number', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    public function getOrderByOrderNumber(Request $request)
    {
        $order_number = isset($request->order_number) ? $request->order_number : 0;

        $order = Order::where('order_number', $order_number)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template'
            ])
            ->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    public function getOrderByTripNumber(Request $request)
    {
        $trip_number = isset($request->trip_number) ? $request->trip_number : 0;

        $order = Order::where('trip_number', $trip_number)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template'
            ])
            ->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    public function getLastOrderNumber(Request $request)
    {
        $last_order_number = Order::max('order_number');

        if (!$last_order_number) {
            $last_order_number = 0;
        }

        return response()->json(['result' => 'OK', 'last_order_number' => $last_order_number]);
    }

    public function saveOrder(Request $request)
    {
        $pickups = isset($request->pickups) ? $request->pickups : [];
        $deliveries = isset($request->deliveries) ? $request->deliveries : [];
        $routing = isset($request->routing) ? $request->routing : [];
        $order_number = (int)(isset($request->order_number) ? $request->order_number : 0);
        $ae_number = isset($request->ae_number) ? $request->ae_number : 0;
        $trip_number = (int)(isset($request->trip_number) ? $request->trip_number : 0);
        $division_id = isset($request->division_id) ? $request->division_id : 0;
        $load_type_id = isset($request->load_type_id) ? $request->load_type_id : 0;
        $template_id = isset($request->template_id) ? $request->template_id : 0;
        $bill_to_customer_id = isset($request->bill_to_customer_id) ? $request->bill_to_customer_id : 0;
        $shipper_customer_id = isset($request->shipper_customer_id) ? $request->shipper_customer_id : 0;
        $consignee_customer_id = isset($request->consignee_customer_id) ? $request->consignee_customer_id : 0;
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;
        $carrier_load = isset($request->carrier_load) ? $request->carrier_load : '';
        $carrier_driver_id = isset($request->carrier_driver_id) ? $request->carrier_driver_id : 0;
        $agent_code = isset($request->agent_code) ? $request->agent_code : '';
        $agent_commission = isset($request->agent_commission) ? $request->agent_commission : '';
        $salesman_code = isset($request->salesman_code) ? $request->salesman_code : '';
        $salesman_commission = isset($request->salesman_commission) ? $request->salesman_commission : '';
        $miles = isset($request->miles) ? $request->miles : 0;
        $charges = isset($request->charges) ? $request->charges : '';
        $order_cost = isset($request->order_cost) ? $request->order_cost : '';
        $profit = isset($request->profit) ? $request->profit : '';
        $percentage = isset($request->percentage) ? $request->percentage : '';
        $haz_mat = isset($request->haz_mat) ? $request->haz_mat : 0;
        $expedited = isset($request->expedited) ? $request->expedited : 0;

        $last_order_number = Order::max('order_number');
        $last_trip_number = Order::max('trip_number');

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

        $order = Order::updateOrCreate([
            'order_number' => $order_number
        ], [
            'ae_number' => $ae_number,
            'trip_number' => $trip_number,
            'division_id' => $division_id,
            'load_type_id' => $load_type_id,
            'template_id' => $template_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'shipper_customer_id' => $shipper_customer_id,
            'consignee_customer_id' => $consignee_customer_id,
            'carrier_id' => $carrier_id,
            'carrier_load' => $carrier_load,
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
            'expedited' => $expedited
        ]);

        $order->pickups()->detach();

        if (count($pickups) > 0) {
            $pivot_pickup = array();

            for ($i = 0; $i < count($pickups); $i++) {
                $pu = $pickups[$i];
                $data = isset($pu['extra_data']) ? $pu['extra_data'] : [];

                $order->pickups()->attach($pu['id'], [
                    'pu_date1' => isset($data['pu_date1']) ? $data['pu_date1'] : '',
                    'pu_date2' => isset($data['pu_date2']) ? $data['pu_date2'] : '',
                    'pu_time1' => isset($data['pu_time1']) ? $data['pu_time1'] : '',
                    'pu_time2' => isset($data['pu_time2']) ? $data['pu_time2'] : '',
                    'bol_numbers' => isset($data['bol_numbers']) ? $data['bol_numbers'] : '',
                    'po_numbers' => isset($data['po_numbers']) ? $data['po_numbers'] : '',
                    'ref_numbers' => isset($data['ref_numbers']) ? $data['ref_numbers'] : '',
                    'seal_number' => isset($data['seal_number']) ? $data['seal_number'] : '',
                    'special_instructions' => isset($data['special_instructions']) ? $data['special_instructions'] : ''
                ]);
            }
        } else {
            $order->pickups()->detach();
        }

        $order->deliveries()->detach();

        if (count($deliveries) > 0) {
            $pivot_delivery = array();

            for ($i = 0; $i < count($deliveries); $i++) {
                $delivery = $deliveries[$i];
                $data = isset($delivery['extra_data']) ? $delivery['extra_data'] : [];

                $order->deliveries()->attach($delivery['id'], [
                    'delivery_date1' => isset($data['delivery_date1']) ? $data['delivery_date1'] : '',
                    'delivery_date2' => isset($data['delivery_date2']) ? $data['delivery_date2'] : '',
                    'delivery_time1' => isset($data['delivery_time1']) ? $data['delivery_time1'] : '',
                    'delivery_time2' => isset($data['delivery_time2']) ? $data['delivery_time2'] : '',
                    'special_instructions' => isset($data['special_instructions']) ? $data['special_instructions'] : ''
                ]);
            }
        } else {
            $order->deliveries()->detach();
        }

        $order->routing()->detach();

        if (count($routing) > 0) {
            $pivot_routing = array();

            for ($i = 0; $i < count($routing); $i++) {
                $route = $routing[$i];
                $data = isset($route['extra_data']) ? $route['extra_data'] : [];

                $order->routing()->attach($route['id'], [
                    'type' => isset($data['type']) ? $data['type'] : ''
                ]);
            }
        } else {
            $order->routing()->detach();
        }

        $order = Order::where('order_number', $order_number)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template'
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $order, 'order_number' => $order_number]);
    }

    public function getPivotOrder(Request $request)
    {
        $order_id = $request->order_id;

        $order = Order::where('id', $order_id)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template'
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $order]);
    }

    public function savePivotOrder(Request $request)
    {
        $order_id = $request->order_id;


        $order = Order::where('id', $order_id)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template'
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $order]);
    }

    public function saveOrderEvent(Request $request){
        $order_id = isset($request->order_id) ? $request->order_id : 0;
        $event_type = isset($request->event_type) ? $request->event_type : '';
        $shipper_id = isset($request->shipper_id) ? $request->shipper_id : 0;
        $consignee_id = isset($request->consignee_id) ? $request->consignee_id : 0;
        $arrived_customer_id = isset($request->arrived_customer_id) ? $request->arrived_customer_id : 0;
        $departed_customer_id = isset($request->departed_customer_id) ? $request->departed_customer_id : 0;
        $old_carrier_id = isset($request->old_carrier_id) ? $request->old_carrier_id : 0;
        $new_carrier_id = isset($request->new_carrier_id) ? $request->new_carrier_id : 0;
        $time = isset($request->time) ? $request->time : '';
        $event_time = isset($request->event_time) ? $request->event_time : '';
        $date = isset($request->date) ? $request->date : '';
        $event_date = isset($request->event_date) ? $request->event_date : '';
        $user_id = isset($request->user_id) ? $request->user_id : 0;
        $event_location = isset($request->event_location) ? $request->event_location : '';
        $event_notes = isset($request->event_notes) ? $request->event_notes : '';

        if ($order_id === 0){
            return response()->json(['result' => 'ORDER ID NOT VALID', 'order_id' => $order_id]);
        }

        $order_event = OrderEvent::updateOrCreate([
            'event_type' => $event_type,
            'shipper_id' => $shipper_id,
            'consignee_id' => $consignee_id,
            'arrived_customer_id' => $arrived_customer_id,
            'departed_customer_id' => $departed_customer_id,
            'old_carrier_id' => $old_carrier_id,
            'new_carrier_id' => $new_carrier_id,
        ], [
            'order_id' => $order_id,
            'event_type' => $event_type,
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

        $order_events = OrderEvent::where('order_id' , $order_id)
            ->with(['shipper', 'consignee', 'arrived_customer', 'departed_customer', 'old_carrier', 'new_carrier'])
            ->orderBy('updated_at', 'desc')->get();

        return response()->json(['result' => 'OK', 'order_event' => $order_event, 'order_events' => $order_events]);
    }
}
