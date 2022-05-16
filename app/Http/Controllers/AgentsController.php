<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Agent;
use App\Models\Company;

class AgentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgent(Request $request): JsonResponse
    {
        $AGENT = new Agent();
        $COMPANY = new Company();

        $agent_id = $request->agent_id ?? ($request->id ?? 0);
        $company_id = $request->company_id ?? 0;

        if ($company_id > 0) {
            $curAgent = $AGENT->where('id', $agent_id)->first();

            $company = $COMPANY->where('id', $company_id)->first();

            $prefix = $request->prefix ?? ($curAgent ? $curAgent->prefix : '');
            $first_name = $request->first_name ?? ($curAgent ? $curAgent->first_name : '');
            $middle_name = $request->middle_name ?? ($curAgent ? $curAgent->middle_name : '');
            $last_name = $request->last_name ?? ($curAgent ? $curAgent->last_name : '');
            $suffix = $request->suffix ?? ($curAgent ? $curAgent->suffix : '');
            $title = $request->title ?? ($curAgent ? $curAgent->title : '');
            $department = $request->department ?? ($curAgent ? $curAgent->department : '');
            $email_work = $request->email_work ?? ($curAgent ? $curAgent->email_work : '');
            $email_personal = $request->email_personal ?? ($curAgent ? $curAgent->email_personal : '');
            $email_other = $request->email_other ?? ($curAgent ? $curAgent->email_other : '');
            $primary_email = $request->primary_email ?? ($curAgent ? $curAgent->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curAgent ? $curAgent->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curAgent ? $curAgent->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curAgent ? $curAgent->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curAgent ? $curAgent->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curAgent ? $curAgent->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curAgent ? $curAgent->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curAgent ? $curAgent->phone_ext : '');
            $country = $request->country ?? ($curAgent ? $curAgent->country : '');
            $address1 = $request->address1 ?? ($curAgent ? $curAgent->address1 : $company->address1);
            $address2 = $request->address2 ?? ($curAgent ? $curAgent->address2 : $company->address2);
            $city = $request->city ?? ($curAgent ? $curAgent->city : $company->city);
            $state = $request->state ?? ($curAgent ? $curAgent->state : $company->state);
            $zip_code = $request->zip_code ?? ($curAgent ? $curAgent->zip_code : $company->zip);
            $birthday = $request->birthday ?? ($curAgent ? $curAgent->birthday : '');
            $website = $request->website ?? ($curAgent ? $curAgent->website : '');
            $notes = $request->notes ?? ($curAgent ? $curAgent->notes : '');
            $is_primary_admin = $request->is_primary_admin ?? ($curAgent ? $curAgent->is_primary_admin : 0);
            $is_online = $request->is_online ?? ($curAgent ? $curAgent->is_online : 0);

            $is_primary_admin = (int)$is_primary_admin;

            $agent = $AGENT->updateOrCreate([
                'id' => $agent_id
            ],
                [
                    'company_id' => $company_id,
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
                    'is_primary_admin' => $is_primary_admin,
                    'is_online' => $is_online
                ]);

            $newAgent = $AGENT->where('id', $agent->id)
                ->with('company')
                ->has('company')
                ->first();

            $agents = $AGENT->where('company_id', $company_id)
                ->with('company')
                ->has('company')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'agent' => $newAgent, 'agents' => $agents]);
        } else {
            return response()->json(['result' => 'NO COMPANY']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $AGENT = new Agent();

        $agent_id = $_POST['agent_id'];
        $company_id = $request->company_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $agent = $AGENT->where('id', $agent_id)->first();
        $cur_avatar = $agent->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            try {
                unlink(public_path('avatars/' . $cur_avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $AGENT->where('id', $agent_id)->update([
            'avatar' => $new_avatar
        ]);

        $agent = $AGENT->where('id', $agent_id)
            ->with('company')
            ->has('company')
            ->first();

        $agents = $AGENT->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'agent' => $agent, 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $AGENT = new Agent();

        $agent_id = $request->agent_id ?? ($request->id ?? 0);
        $company_id = $request->company_id;

        $agent = $AGENT->where('id', $agent_id)->first();

        try {
            unlink(public_path('avatars/' . $agent->avatar));
        } catch (Throwable | Exception $e) {
        }

        $AGENT->where('id', $agent_id)->update([
            'avatar' => ''
        ]);

        $agent = $AGENT->where('id', $agent_id)
            ->with('company')
            ->has('company')
            ->first();

        $agents = $AGENT->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'agent' => $agent, 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgent(Request $request): JsonResponse
    {
        $AGENT = new Agent();

        $agent_id = $request->agent_id ?? ($request->id ?? 0);

        $agent = $AGENT->where('id', $agent_id)->first();

        $AGENT->where('id', $agent_id)->delete();
        $agents = $AGENT->where('company_id', $agent->company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyAgentsSearch(Request $request): JsonResponse
    {
        $AGENT = new Agent();

        $company_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($company_id == 0) {
            $agents = $AGENT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('company')
                ->has('company')
                ->get();
        } else {
            $agents = $AGENT->whereRaw("1 = 1")
                ->whereRaw("company_id = $company_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('company')
                ->has('company')
                ->get();
        }

        return response()->json(['result' => 'OK', 'agents' => $agents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetAgentPassword (Request $request): JsonResponse {
        $id = $request->id ?? 0;
        $AGENT = new Agent();

        if ($id > 0){
            $newPassword = $this->random_str();

            $hashed = Hash::make($newPassword);

            $AGENT->updateOrCreate([
                'id' => $id
            ],[
                'password' => $hashed
            ]);

            $agent = $AGENT->where('id', $id)->first();

            return response()->json(['result' => 'OK', 'agent' => $agent, 'newpass' => $newPassword]);
        }else{
            return response()->json(['result' => 'no agent']);
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
