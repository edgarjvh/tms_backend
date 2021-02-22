<?php

namespace App\Http\Controllers;

use App\AutomaticEmail;
use Illuminate\Http\Request;

class AutomaticEmailsController extends Controller
{
    public function getAutomaticEmails(Request $request){
        $customer_id = $request->customer_id;

        $automatic_emails = AutomaticEmail::where('customer_id', $customer_id)->first();

        return response()->json(['result' => 'OK', 'automatic_emails' => $automatic_emails]);
    }

    public function saveAutomaticEmails(Request $request){
        $customer_id = isset($request->customer_id) ? trim($request->customer_id) : '';
        $automatic_emails_to = isset($request->automatic_emails_to) ? trim($request->automatic_emails_to) : '';
        $automatic_emails_cc = isset($request->automatic_emails_cc) ? trim($request->automatic_emails_cc) : '';
        $automatic_emails_bcc = isset($request->automatic_emails_bcc) ? trim($request->automatic_emails_bcc) : '';
        $automatic_emails_booked_load = isset($request->automatic_emails_booked_load) ? trim($request->automatic_emails_booked_load) : 0;
        $automatic_emails_check_calls = isset($request->automatic_emails_check_calls) ? trim($request->automatic_emails_check_calls) : 0;
        $automatic_emails_carrier_arrival_shipper = isset($request->automatic_emails_carrier_arrival_shipper) ? trim($request->automatic_emails_carrier_arrival_shipper) : 0;
        $automatic_emails_carrier_arrival_consignee = isset($request->automatic_emails_carrier_arrival_consignee) ? trim($request->automatic_emails_carrier_arrival_consignee) : 0;
        $automatic_emails_loaded = isset($request->automatic_emails_loaded) ? trim($request->automatic_emails_loaded) : 0;
        $automatic_emails_empty = isset($request->automatic_emails_empty) ? trim($request->automatic_emails_empty) : 0;

        $cur_automatic_emails = AutomaticEmail::where('customer_id', $customer_id)->first();

        $automatic_emails = AutomaticEmail::updateOrCreate([
            'id' => $cur_automatic_emails ? $cur_automatic_emails->id : 0
        ], [
            'customer_id' => $customer_id,
            'automatic_emails_to' => $automatic_emails_to,
            'automatic_emails_cc' => $automatic_emails_cc,
            'automatic_emails_bcc' => $automatic_emails_bcc,
            'automatic_emails_booked_load' => $automatic_emails_booked_load,
            'automatic_emails_check_calls' => $automatic_emails_check_calls,
            'automatic_emails_carrier_arrival_shipper' => $automatic_emails_carrier_arrival_shipper,
            'automatic_emails_carrier_arrival_consignee' => $automatic_emails_carrier_arrival_consignee,
            'automatic_emails_loaded' => $automatic_emails_loaded,
            'automatic_emails_empty' => $automatic_emails_empty
        ]);

        return response()->json(['result' => 'OK', 'automatic_emails' => $automatic_emails]);
    }
}
