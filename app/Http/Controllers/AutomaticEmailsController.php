<?php

namespace App\Http\Controllers;

use App\Models\AutomaticEmail;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomaticEmailsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAutomaticEmails(Request $request) : JsonResponse
    {
        $AUTOMATIC_EMAIL = new AutomaticEmail();

        $customer_id = $request->customer_id ?? 0;

        $automatic_emails = $AUTOMATIC_EMAIL->where('customer_id', $customer_id)->first();

        return response()->json(['result' => 'OK', 'automatic_emails' => $automatic_emails]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAutomaticEmails(Request $request) : JsonResponse
    {
        $AUTOMATIC_EMAIL = new AutomaticEmail();

        $customer_id = $request->customer_id ?? 0;
        $automatic_emails = $request->automatic_emails ?? [];

        if ($customer_id > 0){
            $AUTOMATIC_EMAIL->where('customer_id', $customer_id)->delete();

            for($i = 0; $i < count($automatic_emails); $i++){
                $item = $automatic_emails[$i];

                $AUTOMATIC_EMAIL->updateOrCreate([
                    'id' => 0
                ], [
                    'customer_id' => $customer_id,
                    'email' => $item['email'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'booked_load' => $item['booked_load'],
                    'check_calls' => $item['check_calls'],
                    'carrier_arrival_shipper' => $item['carrier_arrival_shipper'],
                    'carrier_arrival_consignee' => $item['carrier_arrival_consignee'],
                    'loaded' => $item['loaded'],
                    'empty' => $item['empty'],
                ]);
            }

            $automatic_emails = $AUTOMATIC_EMAIL->where('customer_id', $customer_id)->get();

            return response()->json(['result' => 'OK', 'automatic_emails' => $automatic_emails]);
        }else{
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAutomaticEmail(Request $request) : JsonResponse
    {
        $AUTOMATIC_EMAIL = new AutomaticEmail();

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $automatic_email = $AUTOMATIC_EMAIL->where('id', $id)->delete();
        $automatic_emails = $AUTOMATIC_EMAIL->where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'automatic_email' => $automatic_email, 'automatic_emails' => $automatic_emails]);
    }
}
