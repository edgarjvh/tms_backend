<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\AgentContact;

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
                'division'
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
                    'division'
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

        $ORDER = Order::query();

        $ORDER->whereRaw("1 = 1");
        $ORDER->whereHas('user_code', function ($query1) use ($id) {
            $query1->where('agent_id', $id);
        });

        $ORDER->select([
            'id',
            'order_number'
        ]);

        $ORDER->with([
            'bill_to_company',
            'pickups',
            'deliveries',
            'routing'
        ]);


        $ORDER->orderBy('id', 'desc');

        $orders = $ORDER->get();

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
        $agent_pay_company_trucks = $request->agent_pay_company_trucks ?? 0;
        $agent_own_units = $request->agent_own_units ?? 0;
        $agent_pay_own_trucks = $request->agent_pay_own_trucks ?? 0;

        $with_contact = true;

        if (trim($contact_first_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $agent = $AGENT->updateOrCreate([
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
                'agent_pay_company_trucks' => $agent_pay_company_trucks,
                'agent_own_units' => $agent_own_units,
                'agent_pay_own_trucks' => $agent_pay_own_trucks
            ]);

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
                'division'
            ])->first();

        $agents = $AGENT->where('company_id', $company_id)->with(['contacts'])
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'division'
            ])->get();

        return response()->json(['result' => 'OK', 'agent' => $newAgent, 'agents' => $agents]);
    }
}
