<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentContactsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentContacts(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $agent_id = $request->agent_id ?? 0;
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';

        if ($agent_id == 0) {
            $contacts = $AGENT_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('agent')
                ->has('agent')
                ->get();
        } else {
            $contacts = $AGENT_CONTACT->whereRaw("1 = 1")
                ->whereRaw("agent_id = $agent_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('agent')
                ->has('agent')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function agentContactsSearch(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $agent_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($agent_id == 0) {
            $contacts = $AGENT_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('agent')
                ->has('agent')
                ->get();
        } else {
            $contacts = $AGENT_CONTACT->whereRaw("1 = 1")
                ->whereRaw("agent_id = $agent_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('agent')
                ->has('agent')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentContactsByEmail(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $email = $request->email ?? '';

        $contacts = $AGENT_CONTACT->whereRaw("1 = 1")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentContactsByEmailOrName(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $email = $request->email ?? '';

        $contacts = $AGENT_CONTACT->whereRaw("((LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%') OR (LOWER(first_name) like '%$email%' or LOWER(last_name) like '%$email%'))")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @throws Exception
     */
    public function agentContacts(): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $contacts = $AGENT_CONTACT->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentContactById(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $id = $request->id ?? 0;
        $agent_id = $request->agent_id ?? 0;

        $contact = $AGENT_CONTACT->where('id', $id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->first();
        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByAgentId(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $agent_id = $request->agent_id;
        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();
        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'contact' => null]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentContact(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();
        $AGENT = new Agent();

        $id = $request->id ?? 0;
        $agent_id = $request->agent_id ?? 0;

        $curContact = $AGENT_CONTACT->where('id', $id)->first();
        $agent = $AGENT->where('id', $agent_id)->first();

        $prefix = $request->prefix ?? ($curContact ? $curContact->prefix : '');
        $first_name = $request->first_name ?? ($curContact ? $curContact->first_name : '');
        $middle_name = $request->middle_name ?? ($curContact ? $curContact->middle_name : '');
        $last_name = $request->last_name ?? ($curContact ? $curContact->last_name : '');
        $suffix = $request->suffix ?? ($curContact ? $curContact->suffix : '');
        $title = $request->title ?? ($curContact ? $curContact->title : '');
        $department = $request->department ?? ($curContact ? $curContact->department : '');
        $email_work = $request->email_work ?? ($curContact ? $curContact->email_work : '');
        $email_personal = $request->email_personal ?? ($curContact ? $curContact->email_personal : '');
        $email_other = $request->email_other ?? ($curContact ? $curContact->email_other : '');
        $primary_email = $request->primary_email ?? ($curContact ? $curContact->primary_email : 'work');
        $phone_work = $request->phone_work ?? ($curContact ? $curContact->phone_work : '');
        $phone_work_fax = $request->phone_work_fax ?? ($curContact ? $curContact->phone_work_fax : '');
        $phone_mobile = $request->phone_mobile ?? ($curContact ? $curContact->phone_mobile : '');
        $phone_direct = $request->phone_direct ?? ($curContact ? $curContact->phone_direct : '');
        $phone_other = $request->phone_other ?? ($curContact ? $curContact->phone_other : '');
        $primary_phone = $request->primary_phone ?? ($curContact ? $curContact->primary_phone : 'work');
        $phone_ext = $request->phone_ext ?? ($curContact ? $curContact->phone_ext : '');
        $country = $request->country ?? ($curContact ? $curContact->country : '');
        $address1 = $request->address1 ?? ($curContact ? $curContact->address1 : $agent->address1);
        $address2 = $request->address2 ?? ($curContact ? $curContact->address2 : $agent->address2);
        $city = $request->city ?? ($curContact ? $curContact->city : $agent->city);
        $state = $request->state ?? ($curContact ? $curContact->state : $agent->state);
        $zip_code = $request->zip_code ?? ($curContact ? $curContact->zip_code : $agent->zip);
        $birthday = $request->birthday ?? ($curContact ? $curContact->birthday : '');
        $website = $request->website ?? ($curContact ? $curContact->website : '');
        $notes = $request->notes ?? ($curContact ? $curContact->notes : '');
        $is_primary = $request->is_primary ?? ($curContact ? $curContact->is_primary : 0);
        $is_online = $request->is_online ?? ($curContact ? $curContact->is_online : 0);

        $is_primary = (int)$is_primary;

        $contact = $AGENT_CONTACT->updateOrCreate([
            'id' => $id
        ],
            [
                'agent_id' => $agent_id,
                'prefix' => $prefix,
                'first_name' => trim($first_name),
                'middle_name' => trim($middle_name),
                'last_name' => trim($last_name),
                'suffix' => $suffix,
                'title' => $title,
                'department' => $department,
                'email_work' => $email_work,
                'email_personal' => $email_personal,
                'email_other' => $email_other,
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => $country,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => $website,
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online
            ]);

        if ($is_primary === 1) {
            $AGENT->where('id', $agent_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        } else {
            $AGENT->where(['id' => $agent_id, 'primary_contact_id' => $contact->id])->update([
                'primary_contact_id' => null
            ]);
        }

        $newContact = $AGENT_CONTACT->where('id', $contact->id)
            ->with('agent')
            ->has('agent')
            ->first();

        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts, 'work' => $request->phone_work]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAgentContactAvatar(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $id = $_POST['id'];
        $agent_id = $request->agent_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $AGENT_CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))){
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $AGENT_CONTACT->where('id', $id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = $AGENT_CONTACT->where('id', $id)
            ->with('agent')
            ->has('agent')
            ->first();

        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDivisioContactAvatar(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $id = $request->$request->id ?? 0;
        $agent_id = $request->agent_id;

        $contact = $AGENT_CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . contact->avatar))){
            try {
                unlink(public_path('avatars/' . contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $AGENT_CONTACT->where('id', $id)->update([
            'avatar' => ''
        ]);

        $contact = $AGENT_CONTACT->where('id', $id)
            ->with('agent')
            ->has('agent')
            ->first();

        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgentContact(Request $request): JsonResponse
    {
        $AGENT_CONTACT = new AgentContact();

        $id = $request->id ?? 0;
        $agent_id = $request->agent_id ?? 0;

        $AGENT_CONTACT->where('id', $id)->delete();
        $contacts = $AGENT_CONTACT->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetAgentContactPassword (Request $request): JsonResponse {
        $id = $request->id ?? 0;
        $AGENT_CONTACT = new AgentContact();

        if ($id > 0){
            $newPassword = $this->random_str();

            $hashed = Hash::make($newPassword);

            $AGENT_CONTACT->updateOrCreate([
                'id' => $id
            ],[
                'password' => $hashed
            ]);

            $contact = $AGENT_CONTACT->where('id', $id)->first();

            return response()->json(['result' => 'OK', 'contact' => $contact, 'newpass' => $newPassword]);
        }else{
            return response()->json(['result' => 'no contact']);
        }
    }

    function random_str(
        int $length = 10,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
