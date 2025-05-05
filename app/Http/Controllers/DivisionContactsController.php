<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\DivisionContact;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DivisionContactsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionContacts(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $division_id = $request->division_id ?? 0;
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';

        if ($division_id == 0) {
            $contacts = $DIVISION_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('division')
                ->has('division')
                ->get();
        } else {
            $contacts = $DIVISION_CONTACT->whereRaw("1 = 1")
                ->whereRaw("division_id = $division_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('division')
                ->has('division')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function divisionContactsSearch(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $division_id = $request->search[0]['data'] ?? 0;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        if ($division_id == 0) {
            $contacts = $DIVISION_CONTACT->whereRaw("1 = 1")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('division')
                ->has('division')
                ->get();
        } else {
            $contacts = $DIVISION_CONTACT->whereRaw("1 = 1")
                ->whereRaw("division_id = $division_id")
                ->whereRaw("LOWER(first_name) like '%$first_name%'")
                ->whereRaw("LOWER(last_name) like '%$last_name%'")
                ->whereRaw("LOWER(address1) like '%$address1%'")
                ->whereRaw("LOWER(address2) like '%$address2%'")
                ->whereRaw("LOWER(city) like '%$city%'")
                ->whereRaw("LOWER(state) like '%$state%'")
                ->whereRaw("(phone_work like '%$phone%' or phone_mobile like '%$phone%' or phone_work_fax like '%$phone%' or phone_direct like '%$phone%' or phone_other like '%$phone%')")
                ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
                ->orderBy('first_name')
                ->with('division')
                ->has('division')
                ->get();
        }

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionContactsByEmail(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $email = $request->email ?? '';

        $contacts = $DIVISION_CONTACT->whereRaw("1 = 1")
            ->whereRaw("(LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%')")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionContactsByEmailOrName(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $email = $request->email ?? '';

        $contacts = $DIVISION_CONTACT->whereRaw("((LOWER(email_work) like '%$email%' or LOWER(email_personal) like '%$email%' or LOWER(email_other) like '%$email%') OR (LOWER(first_name) like '%$email%' or LOWER(last_name) like '%$email%'))")
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @throws Exception
     */
    public function divisionContacts(): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $contacts = $DIVISION_CONTACT->with('division')
            ->has('division')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionContactById(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $id = $request->id ?? 0;
        $division_id = $request->division_id ?? 0;

        $contact = $DIVISION_CONTACT->where('id', $id)
            ->with('division')
            ->has('division')
            ->orderBy('first_name')
            ->first();
        $contacts = $DIVISION_CONTACT->where('division_id', $division_id)
            ->with('division')
            ->has('division')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param $division_id
     * @return array
     */
    public function getDivisionContacts1($division_id): array
    {
        $sql =
            /** @lang text */
            "SELECT
                c.*,
                d.name AS owner_name
            FROM contacts AS c
            LEFT JOIN divisions AS d ON c.division_id = d.id
            WHERE division_id = ?
            ORDER BY first_name";

        $params = [$division_id];

        $contacts = DB::select($sql, $params);

        return $contacts;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByDivisionId(Request $request): JsonResponse
    {
        $division_id = $request->owner_id ?? null;

        $contacts = $this->getDivisionContacts1($division_id);

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionContact(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $division_id = $request->owner_id ?? null;
        $prefix = $request->prefix ?? '';
        $first_name = ucwords($request->first_name ?? '');
        $middle_name = ucwords($request->middle_name ?? '');
        $last_name = ucwords($request->last_name ?? '');
        $suffix = $request->suffix ?? '';
        $title = ucwords($request->title ?? '');
        $company = ucwords($request->company ?? '');
        $department = ucwords($request->department ?? '');
        $email_work = strtolower($request->email_work ?? '');
        $email_personal = strtolower($request->email_personal ?? '');
        $email_other = strtolower($request->email_other ?? '');
        $primary_email = $request->primary_email ?? 'work';
        $phone_work = $request->phone_work ?? '';
        $phone_work_fax = $request->phone_work_fax ?? '';
        $phone_mobile = $request->phone_mobile ?? '';
        $phone_direct = $request->phone_direct ?? '';
        $phone_other = $request->phone_other ?? '';
        $primary_phone = $request->primary_phone ?? 'work';
        $phone_ext = $request->phone_ext ?? '';
        $country = ucwords($request->country ?? '');
        $address1 = ucwords($request->address1 ?? '');
        $address2 = ucwords($request->address2 ?? '');
        $city = ucwords($request->city ?? '');
        $state = strtoupper($request->state ?? '');
        $zip_code = $request->zip_code ?? '';
        $birthday = $request->birthday ?? '';
        $website = strtolower($request->website ?? '');
        $notes = $request->notes ?? '';
        $is_primary = $request->is_primary ?? 0;
        $is_online = $request->is_online ?? 0;
        $is_primary = (int)$is_primary;

        $DIVISION_CONTACT = new DivisionContact();
        $DIVISION = new Division();

        $contact = $DIVISION_CONTACT->updateOrCreate(
            [
                'id' => $id
            ],
            [
                'division_id' => $division_id,
                'prefix' => $prefix,
                'first_name' => ucwords(trim($first_name)),
                'middle_name' => ucwords(trim($middle_name)),
                'last_name' => ucwords(trim($last_name)),
                'suffix' => $suffix,
                'title' => $title,
                'company' => $company,
                'department' => $department,
                'email_work' => strtolower($email_work),
                'email_personal' => strtolower($email_personal),
                'email_other' => strtolower($email_other),
                'primary_email' => $primary_email,
                'phone_work' => $phone_work,
                'phone_work_fax' => $phone_work_fax,
                'phone_mobile' => $phone_mobile,
                'phone_direct' => $phone_direct,
                'phone_other' => $phone_other,
                'primary_phone' => $primary_phone,
                'phone_ext' => $phone_ext,
                'country' => ucwords($country),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip_code' => $zip_code,
                'birthday' => $birthday,
                'website' => strtolower($website),
                'notes' => $notes,
                'is_primary' => $is_primary,
                'is_online' => $is_online
            ]
        );

        if ($is_primary === 1) {
            $DIVISION->where('id', $division_id)->update([
                'primary_contact_id' => $contact->id
            ]);
        } else {
            $DIVISION->where(['id' => $division_id, 'primary_contact_id' => $contact->id])->update([
                'primary_contact_id' => null
            ]);
        }

        $newContact = $DIVISION_CONTACT->where('id', $contact->id)->first();

        $contacts = $this->getDivisionContacts1($division_id);

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadDivisionContactAvatar(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $id = $_POST['id'];
        $division_id = $_POST['owner_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $DIVISION_CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) { // delete old avatar
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $DIVISION_CONTACT->where('id', $id)->update([ // update new avatar
            'avatar' => $new_avatar
        ]);

        $contact = $DIVISION_CONTACT->where('id', $id)->first();

        $contacts = $this->getDivisionContacts1($division_id);

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar)); // move new avatar

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDivisioContactAvatar(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $id = $request->id ?? null;
        $division_id = $request->onwer_id ?? null;

        $contact = $DIVISION_CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) { // delete old avatar
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $DIVISION_CONTACT->where('id', $id)->update([ // update new avatar
            'avatar' => null
        ]);

        $contact = $DIVISION_CONTACT->where('id', $id)->first();

        $contacts = $this->getDivisionContacts1($division_id);

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDivisionContact(Request $request): JsonResponse
    {
        $DIVISION_CONTACT = new DivisionContact();

        $id = $request->id ?? null;
        $division_id = $request->owner_id ?? null;

        $isUserContact = DivisionContact::where('id', $id)->whereNotNull('user_code_id')->first(); // check if contact is a user contact

        if ($isUserContact) {
            $DIVISION_CONTACT->where('id', $id)->update([
                'division_id' => null
            ]);
        }else{
            $DIVISION_CONTACT->where('id', $id)->delete();
        }

        $contacts = $this->getDivisionContacts1($division_id);

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }
}
