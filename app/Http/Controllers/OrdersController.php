<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function getOrders(Request $request)
    {
        $orders = Order::with(['bill_to_company', 'shipper_company', 'consignee_company', 'carrier', 'driver', 'notes_for_carrier', 'internal_notes'])
            ->orderBy('order_number', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    public function getOrderByOrderNumber(Request $request)
    {
        $order_number = isset($request->order_number) ? $request->order_number : 0;

        $order = Order::where('order_number', $order_number)
            ->with(['bill_to_company', 'shipper_company', 'consignee_company', 'carrier', 'driver', 'notes_for_carrier', 'internal_notes'])
            ->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    public function getOrderByTripNumber(Request $request)
    {
        $trip_number = isset($request->trip_number) ? $request->trip_number : 0;

        $order = Order::where('trip_number', $trip_number)
            ->with(['bill_to_company', 'shipper_company', 'consignee_company', 'carrier', 'driver', 'notes_for_carrier', 'internal_notes'])
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
        $order_number = (int)(isset($request->order_number) ? $request->order_number : 0);
        $ae_number = isset($request->ae_number) ? $request->ae_number : 0;
        $trip_number = (int)(isset($request->trip_number) ? $request->trip_number : 0);
        $division = isset($request->division) ? $request->division : '';
        $load_type = isset($request->load_type) ? $request->load_type : '';
        $template = isset($request->template) ? $request->template : '';
        $bill_to_customer_id = isset($request->bill_to_customer_id) ? $request->bill_to_customer_id : 0;
        $shipper_customer_id = isset($request->shipper_customer_id) ? $request->shipper_customer_id : 0;
        $consignee_customer_id = isset($request->consignee_customer_id) ? $request->consignee_customer_id : 0;
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id : 0;
        $carrier_load = isset($request->carrier_load) ? $request->carrier_load : '';
        $carrier_driver_id = isset($request->carrier_driver_id) ? $request->carrier_driver_id : 0;
        $pu_date1 = isset($request->pu_date1) ? $request->pu_date1 : '';
        $pu_date2 = isset($request->pu_date2) ? $request->pu_date2 : '';
        $pu_time1 = isset($request->pu_time1) ? $request->pu_time1 : '';
        $pu_time2 = isset($request->pu_time2) ? $request->pu_time2 : '';
        $bol_numbers = isset($request->bol_numbers) ? $request->bol_numbers : '';
        $po_numbers = isset($request->po_numbers) ? $request->po_numbers : '';
        $ref_numbers = isset($request->ref_numbers) ? $request->ref_numbers : '';
        $seal_number = isset($request->seal_number) ? $request->seal_number : '';
        $shipper_special_instructions = isset($request->shipper_special_instructions) ? $request->shipper_special_instructions : '';
        $delivery_date1 = isset($request->delivery_date1) ? $request->delivery_date1 : '';
        $delivery_date2 = isset($request->delivery_date2) ? $request->delivery_date2 : '';
        $delivery_time1 = isset($request->delivery_time1) ? $request->delivery_time1 : '';
        $delivery_time2 = isset($request->delivery_time2) ? $request->delivery_time2 : '';
        $consignee_special_instructions = isset($request->consignee_special_instructions) ? $request->consignee_special_instructions : '';
        $pu1 = isset($request->pu1) ? $request->pu1 : '';
        $pu2 = isset($request->pu2) ? $request->pu2 : '';
        $pu3 = isset($request->pu3) ? $request->pu3 : '';
        $pu4 = isset($request->pu4) ? $request->pu4 : '';
        $pu5 = isset($request->pu5) ? $request->pu5 : '';
        $delivery1 = isset($request->delivery1) ? $request->delivery1 : '';
        $delivery2 = isset($request->delivery2) ? $request->delivery2 : '';
        $delivery3 = isset($request->delivery3) ? $request->delivery3 : '';
        $delivery4 = isset($request->delivery4) ? $request->delivery4 : '';
        $delivery5 = isset($request->delivery5) ? $request->delivery5 : '';
        $agent_code = isset($request->agent_code) ? $request->agent_code : '';
        $agent_commission = isset($request->agent_commission) ? $request->agent_commission : '';
        $salesman_code = isset($request->salesman_code) ? $request->salesman_code : '';
        $salesman_commission = isset($request->salesman_commission) ? $request->salesman_commission : '';
        $miles = isset($request->miles) ? $request->miles : '';
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
        }else{
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
            'division' => $division,
            'load_type' => $load_type,
            'template' => $template,
            'bill_to_customer_id' => $bill_to_customer_id,
            'shipper_customer_id' => $shipper_customer_id,
            'consignee_customer_id' => $consignee_customer_id,
            'carrier_id' => $carrier_id,
            'carrier_load' => $carrier_load,
            'carrier_driver_id' => $carrier_driver_id,
            'pu_date1' => $pu_date1,
            'pu_date2' => $pu_date2,
            'pu_time1' => $pu_time1,
            'pu_time2' => $pu_time2,
            'bol_numbers' => $bol_numbers,
            'po_numbers' => $po_numbers,
            'ref_numbers' => $ref_numbers,
            'seal_number' => $seal_number,
            'shipper_special_instructions' => $shipper_special_instructions,
            'delivery_date1' => $delivery_date1,
            'delivery_date2' => $delivery_date2,
            'delivery_time1' => $delivery_time1,
            'delivery_time2' => $delivery_time2,
            'consignee_special_instructions' => $consignee_special_instructions,
            'pu1' => $pu1,
            'pu2' => $pu2,
            'pu3' => $pu3,
            'pu4' => $pu4,
            'pu5' => $pu5,
            'delivery1' => $delivery1,
            'delivery2' => $delivery2,
            'delivery3' => $delivery3,
            'delivery4' => $delivery4,
            'delivery5' => $delivery5,
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

        $order = Order::where('order_number', $order_number)
            ->with(['bill_to_company', 'shipper_company', 'consignee_company', 'carrier', 'driver', 'notes_for_carrier', 'internal_notes'])
            ->first();

        return response()->json(['result' => 'OK', 'order' => $order, 'order_number' => $order_number]);
    }
}
