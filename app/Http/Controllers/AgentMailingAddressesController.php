<?php

namespace App\Http\Controllers;

use App\Models\AgentMailingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentMailingAddressesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentMailingAddress(Request $request) :JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new AgentMailingAddress();

        $agent_id = $request->agent_id ?? 0;
        $code = $request->code ?? '';
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
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';

        if ($agent_id > 0){
            $CUSTOMER_MAILING_ADDRESS->updateOrCreate([
                'agent_id' => $agent_id
            ],
                [
                    'code' => $code,
                    'name' => $name,
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'ext' => $ext,
                    'email' => $email,
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email
                ]);

            $newMailingAddress = $CUSTOMER_MAILING_ADDRESS->where('agent_id', $agent_id)->with(['mailing_contact', 'agent'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO AGENT']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgentMailingAddress(Request $request) : JsonResponse
    {
        $CUSTOMER_MAILING_ADDRESS = new AgentMailingAddress();

        $agent_id = $request->agent_id ?? 0;

        $mailing_address = $CUSTOMER_MAILING_ADDRESS->where('agent_id', $agent_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }
}
