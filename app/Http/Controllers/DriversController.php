<?php

namespace App\Http\Controllers;

use App\Models\DriverEmergencyContact;
use App\Models\DriverLicense;
use App\Models\DriverMailingAddress;
use App\Models\DriverMedicalCard;
use App\Models\DriverTractor;
use App\Models\DriverTrailer;
use App\Models\LicenseClass;
use App\Models\LicenseEndorsement;
use App\Models\LicenseRestriction;
use App\Models\OwnerOperator;
use App\Models\Relationship;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Driver;
use App\Models\Company;
use Throwable;

class DriversController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverById(Request $request): JsonResponse
    {
        $DRIVER = Driver::query();
        $id = $request->id ?? null;

        $driver = $DRIVER->where('id', $id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        return response()->json(['result' => 'OK', 'driver' => $driver]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDrivers(Request $request): JsonResponse
    {
        $DRIVER = Driver::query();
        $company_id = $request->company_id ?? null;

        $drivers = $DRIVER->where('company_id', $company_id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverByCode(Request $request): JsonResponse
    {
        $DRIVER = Driver::query();
        $code = strtoupper($request->code ?? '');

        $driver = $DRIVER->whereRaw("1 = 1")
            ->whereRaw("code LIKE '$code%'")
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        return response()->json(['result' => 'OK', 'driver' => $driver]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriver(Request $request): JsonResponse
    {
        $DRIVER = Driver::query();

        $id = $request->id ?? null;
        $code = $request->code ?? '';
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $ext = $request->ext ?? '';
        $email = $request->email ?? '';
        $notes = $request->notes ?? '';
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;
        $sub_origin = $request->sub_origin ?? null;
        $company_id = $request->company_id ?? null;
        $carrier_id = $request->carrier_id ?? null;
        $agent_id = $request->agent_id ?? null;
        $tractor = $request->tractor ?? '';
        $type_id = $request->type_id ?? null;
        $trailer = $request->trailer ?? '';

        if (!$id) {
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'drivers'");
            $code_prefix =
                $sub_origin === 'driver'
                    ? 'CD'
                    : (($sub_origin === 'operator' || $sub_origin === 'agent')
                    ? 'OP'
                    : ($sub_origin === 'carrier'
                        ? 'DV'
                        : ''));
            $code = str_pad($table_info[0]->Auto_increment, 6, $code_prefix . "0000", STR_PAD_LEFT);
        } else {
            $curDriver = Driver::query()->where('id', '=', $id)->first();

            $company_id = $request->company_id ?? ($curDriver?->company_id);
            $carrier_id = $request->carrier_id ?? ($curDriver?->carrier_id);
            $agent_id = $request->agent_id ?? ($curDriver?->agent_id);
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $driver = $DRIVER->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => $code,
                'company_id' => $company_id,
                'carrier_id' => $carrier_id,
                'agent_id' => $agent_id,
                'first_name' => ucwords($first_name),
                'last_name' => ucwords($last_name),
                'address1' => ucwords($address1),
                'address2' => ucwords($address2),
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => ucwords($contact_name),
                'contact_phone' => $contact_phone,
                'ext' => $ext,
                'email' => $email,
                'notes' => $notes,
                'mailing_contact_id' => $mailing_contact_id,
                'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                'mailing_contact_primary_email' => $mailing_contact_primary_email,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
            ]);

        if ($with_contact) {
            $contacts = DriverEmergencyContact::where('driver_id', $driver->id)->get();

            $contact_name_splitted = explode(" ", $contact_name);
            $contact_first = $contact_name_splitted[0];
            $contact_last = '';

            if (count($contact_name_splitted) > 0) {
                for ($i = 1; $i < count($contact_name_splitted); $i++) {
                    $contact_last .= $contact_name_splitted[$i] . " ";
                }
            }

            $contact_last = trim($contact_last);

            if (count($contacts) === 0) {
                $contact = new DriverEmergencyContact();
                $contact->driver_id = $driver->id;
                $contact->first_name = ucwords(trim($contact_first));
                $contact->last_name = ucwords(trim($contact_last));
                $contact->phone_work = $contact_phone;
                $contact->email_work = $email;
                $contact->phone_ext = $ext;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = ucwords($city);
                $contact->state = strtoupper($state);
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->priority = 1;
                $contact->save();

            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    DriverEmergencyContact::where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $ext,
                        'email_work' => ($contact->primary_phone === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_phone === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_phone === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        if ($sub_origin === 'carrier'){
            if ($tractor !== '' || $type_id !== null){
                DriverTractor::query()->updateOrCreate([
                    'driver_id' => $driver->id
                ],[
                    'number'=> $tractor,
                    'type_id'=>$type_id
                ]);
            }

            if ($trailer !== ''){
                DriverTrailer::query()->updateOrCreate([
                    'driver_id' => $driver->id
                ],[
                    'number'=> $trailer
                ]);
            }
        }

        $newDriver = Driver::where('id', $driver->id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        $DRIVER = Driver::query();

        if ($sub_origin === 'driver'){
            $DRIVER->where('company_id', $company_id)->where('owner_type', 'company');
        }
        if ($sub_origin === 'operator'){
            $DRIVER->where('company_id', $company_id)->where('owner_type', 'operator');
        }
        if ($sub_origin === 'carrier'){
            $DRIVER->where('carrier_id', $carrier_id)->where('owner_type', 'carrier');
        }
        if ($sub_origin === 'agent'){
            $DRIVER->where('agent_id', $agent_id)->where('owner_type', 'agent');
        }
        $drivers = $DRIVER->with(['contacts', 'tractor', 'trailer'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $newDriver, 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriver(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $sub_origin = $request->sub_origin ?? null;

        $driver = Driver::where('id', $id)->first();

        Driver::where('id', $id)->delete();

        $DRIVER = Driver::query();

        if ($sub_origin === 'driver' || $sub_origin === 'operator'){
            $DRIVER->where('company_id', $driver->company_id);
        }
        if ($sub_origin === 'carrier'){
            $DRIVER->where('carrier_id', $driver->carrier_id);
        }
        if ($sub_origin === 'agent'){
            $DRIVER->where('agent_id', $driver->agent_id);
        }

        $drivers = $DRIVER
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyDriversSearch(Request $request): JsonResponse
    {
        $DRIVER = Driver::query();
        $company_id = $request->search[0]['data'] ?? null;
        $first_name = $request->search[1]['data'] ?? '';
        $last_name = $request->search[2]['data'] ?? '';
        $address1 = $request->search[3]['data'] ?? '';
        $address2 = $request->search[4]['data'] ?? '';
        $city = $request->search[5]['data'] ?? '';
        $state = $request->search[6]['data'] ?? '';
        $zip = $request->search[7]['data'] ?? '';

        $drivers = $DRIVER->whereRaw("1 = 1")
            ->whereRaw("LOWER(first_name) like '$first_name%'")
            ->whereRaw("LOWER(last_name) like '$last_name%'")
            ->whereRaw("LOWER(address1) like '$address1%'")
            ->whereRaw("LOWER(address2) like '$address2%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("LOWER(zip) like '$zip%'")
            ->with(['company'])
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverMailingAddress(Request $request): JsonResponse
    {
        $MAILING_ADDRESS = DriverMailingAddress::query();

        $id = $request->id ?? null;
        $driver_id = $request->driver_id ?? null;
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

        $mailing_address = $MAILING_ADDRESS->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
            'address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'state' => strtoupper($state),
            'zip' => $zip,
            'contact_name' => $contact_name,
            'contact_phone' => $contact_phone,
            'ext' => $ext,
            'email' => strtolower($email),
        ]);

        Driver::query()->updateOrCreate([
            'id' => $driver_id
        ], [
            'mailing_contact_id' => $mailing_contact_id,
            'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
            'mailing_contact_primary_email' => $mailing_contact_primary_email,
            'remit_to_address_is_the_same' => 1
        ]);

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverMailingAddress(Request $request): JsonResponse
    {
        $MAILING_ADDRESS = DriverMailingAddress::query();
        $driver_id = $request->driver_id ?? null;

        $MAILING_ADDRESS->where('driver_id', $driver_id)->delete();

        Driver::query()->updateOrCreate([
            'id' => $driver_id
        ], [
            'mailing_contact_id' => null,
            'mailing_contact_primary_phone' => 'work',
            'mailing_contact_primary_email' => 'work',
            'remit_to_address_is_the_same' => 0
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = DriverEmergencyContact::query();
        $id = $request->id ?? null;

        $contact = $EMERGENCY_CONTACT->where('id', $id)
            ->with(['driver', 'relationship'])
            ->first();

        return response()->json(['result' => 'OK', 'contact' => $contact]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getContactsByDriverId(Request $request): JsonResponse
    {
        $driver_id = $request->driver_id ?? null;

        $contacts = $this->getDriverContacts($driver_id);

        return response()->json(['result' => 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param $driver_id
     * @return array
     */
    public function getDriverContacts($driver_id): array
    {
        $sql =
            /** @lang text */
            "SELECT
                c.*,
                d.name AS owner_name
            FROM contacts AS c
            LEFT JOIN drivers AS d ON c.driver_id = d.id
            WHERE driver_id = ?
            ORDER BY priority";

        $params = [$driver_id];

        $contacts = DB::select($sql, $params);

        return $contacts;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = DriverEmergencyContact::query();
        $id = $request->id ?? null;
        $driver_id = $request->owner_id ?? null;

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
        $type = $request->type ?? 'internal';
        $is_primary = (int)$is_primary;
        $relationship_id = $request->relationship_id ?? null;
        $priority = $request->priority ?? 0;
        $is_primary = (int)$is_primary;
        $priority = (int)$priority;
        $is_online = (int)$is_online;

        $_contact = DriverEmergencyContact::query()->where('priority', $priority)->first();

        $contact = $EMERGENCY_CONTACT->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
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
            'is_online' => $is_online,
            'type' => $type,
            'relationship_id' => $relationship_id,
            'priority' => $priority
        ]);

        $maxPriority = DriverEmergencyContact::query()->where('driver_id', $driver_id)->max('priority');

        if ($_contact) {
            DriverEmergencyContact::query()->updateOrCreate([
                'id' => $_contact->id
            ], [
                'priority' => ((int)$maxPriority + 1)
            ]);
        }

        $_contact = DriverEmergencyContact::query()->where('driver_id', $driver_id)->orderBy('priority')->first();

        DriverEmergencyContact::query()
            ->where('driver_id', $driver_id)
            ->where('id', $_contact->id)
            ->update([
                'is_primary' => 1
            ]);

        DriverEmergencyContact::query()
            ->where('driver_id', $driver_id)
            ->whereNot('id', $_contact->id)
            ->update([
                'is_primary' => 0
            ]);

        $newContact = DriverEmergencyContact::where('id', $contact->id)->first();

        $contacts = DriverEmergencyContact::where('driver_id', $driver_id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->orderBy('priority')
            ->get();

        $driver = Driver::where('id', $driver_id)->with(['contacts'])->first();

        $DRIVER = Driver::query();

        if ($driver->owner_type === 'company'){
            $DRIVER->where('company_id', $driver->company_id)->where('owner_type', 'company');
        }

        if ($driver->owner_type === 'operator'){
            $DRIVER->where('company_id', $driver->company_id)->where('owner_type', 'operator');
        }

        if ($driver->owner_type === 'carrier'){
            $DRIVER->where('carrier_id', $driver->carrier_id)->where('owner_type', 'carrier');
        }

        if ($driver->owner_type === 'agent'){
            $DRIVER->where('agent_id', $driver->agent_id)->where('owner_type', 'agent');
        }

        $drivers = $DRIVER->with(['contacts'])->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts, 'driver' => $driver, 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = DriverEmergencyContact::query();
        $id = $request->id ?? null;
        $driver_id = $request->owner_id ?? null;

        $isUserContact = $EMERGENCY_CONTACT->where('id', $id)->whereNotNull('user_code_id')->first();

        if ($isUserContact) {
            $EMERGENCY_CONTACT->where('id', $id)->update([
                'driver_id' => null
            ]);
        } else {
            $EMERGENCY_CONTACT->where('id', $id)->delete();
        }

        $contacts = $EMERGENCY_CONTACT->where('driver_id', $driver_id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->orderBy('priority')
            ->get();

        return response()->json(['result', 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function uploadDriverEmergencyContactAvatar(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = DriverEmergencyContact::query();

        $id = $_POST['id'];
        $driver_id = $_POST['owner_id'];
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $EMERGENCY_CONTACT->where('id', $id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))) { // check if file exists
                try {
                    unlink(public_path('avatars/' . $cur_avatar)); // delete the file
                } catch (Throwable|Exception $e) {
                }
            }
        }

        DriverEmergencyContact::where('id', $id)->update([ // update the record
            'avatar' => $new_avatar
        ]);

        $contact = DriverEmergencyContact::where('id', $id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->first();

        $contacts = DriverEmergencyContact::where('driver_id', $driver_id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->orderBy('priority')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar)); // move the uploaded file

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDriverEmergencyContactAvatar(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = DriverEmergencyContact::query();

        $id = $request->id ?? null;
        $driver_id = $request->owner_id ?? null;

        $contact = $EMERGENCY_CONTACT->where('id', $id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) { // check if file exists
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable|Exception $e) {
            }
        }

        DriverEmergencyContact::where('id', $id)->update([ // update the record
            'avatar' => null
        ]);

        $contact = DriverEmergencyContact::where('id', $id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->first();

        $contacts = DriverEmergencyContact::where('driver_id', $driver_id)
            ->with(['driver', 'relationship'])
            ->has('driver')
            ->orderBy('priority')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    public function getRelationships(Request $request): JsonResponse
    {
        $RELATIONSHIPS = Relationship::query();

        $name = $request->name ?? '';

        $relationships = $RELATIONSHIPS->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'relationships' => $relationships]);
    }

    public function getDriverLicense(Request $request): JsonResponse
    {
        $DRIVER_LICENSE = DriverLicense::query();
        $id = $request->id ?? null;

        $license = $DRIVER_LICENSE->where('id', $id)
            ->with([
                'class',
                'endorsement',
                'restriction'
            ])->first();

        return response()->json(['result' => 'OK', 'license' => $license]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverLicense(Request $request): JsonResponse
    {
        $DRIVER_LICENSE = DriverLicense::query();
        $id = $request->id ?? null;
        $driver_id = $request->driver_id ?? null;
        $license_number = $request->license_number ?? '';
        $state = $request->state ?? '';
        $cdl = $request->cdl ?? 0;
        $class_id = $request->class_id ?? null;
        $endorsement_id = $request->endorsement_id ?? null;
        $expiration_date = $request->expiration_date ?? null;
        $restriction_id = $request->restriction_id ?? null;

        $license = $DRIVER_LICENSE->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
            'license_number' => $license_number,
            'state' => strtoupper($state),
            'cdl' => $cdl,
            'class_id' => $class_id,
            'endorsement_id' => $endorsement_id,
            'expiration_date' => $expiration_date,
            'restriction_id' => $restriction_id
        ]);

        $newLicense = DriverLicense::where('id', $license->id)
            ->with([
                'class',
                'endorsement',
                'restriction'
            ])->first();

        return response()->json(['result' => 'OK', 'license' => $newLicense]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverLicense(Request $request): JsonResponse
    {
        $DRIVER_LICENSE = DriverLicense::query();
        $id = $request->id ?? null;

        $license = DriverLicense::where('id', $id)->first();

        if ($license) {
            $DRIVER_LICENSE->where('id', $id)->delete();

            if (file_exists(public_path('license-images/' . $license->image))) {
                try {
                    unlink(public_path('license-images/' . $license->image));
                } catch (Throwable|Exception $e) {
                }
            }
        }

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLicenseEndorsements(Request $request): JsonResponse
    {
        $ENDORSEMENT = LicenseEndorsement::query();

        $name = $request->name ?? '';

        $endorsements = $ENDORSEMENT->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'endorsements' => $endorsements]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLicenseClasses(Request $request): JsonResponse
    {
        $CLASSES = LicenseClass::query();

        $name = $request->name ?? '';

        $classes = $CLASSES->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'classes' => $classes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLicenseRestrictions(Request $request): JsonResponse
    {
        $RESTRICTIONS = LicenseRestriction::query();

        $name = $request->name ?? '';

        $restrictions = $RESTRICTIONS->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'restrictions' => $restrictions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadDriverLicenseImage(Request $request): JsonResponse
    {
        $DRIVER_LICENSE = DriverLicense::query();

        $id = $_POST['license_id'];
        $license_number = $_POST['license_number'];
        $state = $_POST['state'];
        $class_id = $_POST['class_id'];
        $cdl = $_POST['cdl'];
        $endorsement_id = $_POST['endorsement_id'];
        $expiration_date = $_POST['expiration_date'];
        $restriction_id = $_POST['restriction_id'];

        $license = $DRIVER_LICENSE->updateOrCreate([
            'id' => $id
        ], [
            'license_number' => $license_number,
            'state' => $state,
            'class_id' => $class_id,
            'cdl' => $cdl,
            'endorsement_id' => $endorsement_id,
            'expiration_date' => $expiration_date,
            'restriction_id' => $restriction_id
        ]);

        $fileData = $_FILES['image'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $cur_image = $license->image;
        $new_image = uniqid() . '.' . $extension;

        if ($cur_image) {
            if (file_exists(public_path('license-images/' . $cur_image))) {
                try {
                    unlink(public_path('license-images/' . $cur_image));
                } catch (Throwable|Exception $e) {
                }
            }
        }

        DriverLicense::where('id', $id)->update([
            'image' => $new_image
        ]);


        move_uploaded_file($fileData['tmp_name'], public_path('license-images/' . $new_image));

        return response()->json(['result' => 'OK', 'image' => $new_image]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDriverLicenseImage(Request $request): JsonResponse
    {
        $DRIVER_LICENSE = new DriverLicense();

        $id = $request->id ?? null;

        $license = $DRIVER_LICENSE->where('id', $id)->first();

        if (file_exists(public_path('license-images/' . $license->image))) {
            try {
                unlink(public_path('license-images/' . $license->image));
            } catch (Throwable|Exception $e) {
            }
        }

        DriverLicense::where('id', $id)->update([
            'image' => ''
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverMedicalCard(Request $request): JsonResponse
    {
        $DRIVER_MEDICAL_CARD = DriverMedicalCard::query();
        $id = $request->id ?? null;

        $card = $DRIVER_MEDICAL_CARD->where('id', $id)->first();

        return response()->json(['result' => 'OK', 'card' => $card]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverMedicalCard(Request $request): JsonResponse
    {
        $DRIVER_MEDICAL_CARD = DriverMedicalCard::query();
        $id = $request->id ?? null;
        $driver_id = $request->driver_id ?? null;
        $issue_date = $request->issue_date ?? null;
        $expiration_date = $request->expiration_date ?? null;

        $card = $DRIVER_MEDICAL_CARD->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
            'issue_date' => $issue_date,
            'expiration_date' => $expiration_date
        ]);

        $newCard = DriverMedicalCard::where('id', $card->id)->first();

        return response()->json(['result' => 'OK', 'card' => $newCard]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverMedicalCard(Request $request): JsonResponse
    {
        $DRIVER_MEDICAL_CARD = DriverMedicalCard::query();
        $id = $request->id ?? null;

        $card = DriverMedicalCard::where('id', $id)->first();

        if ($card) {
            $DRIVER_MEDICAL_CARD->where('id', $id)->delete();

            if (file_exists(public_path('medical-card-images/' . $card->image))) {
                try {
                    unlink(public_path('medical-card-images/' . $card->image));
                } catch (Throwable|Exception $e) {
                }
            }
        }

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadDriverMedicalCardImage(Request $request): JsonResponse
    {
        $DRIVER_MEDICAL_CARD = DriverMedicalCard::query();

        $id = $_POST['medical_card_id'];
        $issue_date = $_POST['issue_date'];
        $expiration_date = $_POST['expiration_date'];

        $card = $DRIVER_MEDICAL_CARD->updateOrCreate([
            'id' => $id
        ], [
            'issue_date' => $issue_date,
            'expiration_date' => $expiration_date
        ]);

        $fileData = $_FILES['image'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $cur_image = $card->image;
        $new_image = uniqid() . '.' . $extension;

        if ($cur_image) {
            if (file_exists(public_path('medical-card-images/' . $cur_image))) {
                try {
                    unlink(public_path('medical-card-images/' . $cur_image));
                } catch (Throwable|Exception $e) {
                }
            }
        }

        DriverMedicalCard::where('id', $id)->update([
            'image' => $new_image
        ]);


        move_uploaded_file($fileData['tmp_name'], public_path('medical-card-images/' . $new_image));

        return response()->json(['result' => 'OK', 'image' => $new_image]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeDriverMedicalCardImage(Request $request): JsonResponse
    {
        $DRIVER_MEDICAL_CARD = new DriverMedicalCard();

        $id = $request->id ?? null;

        $card = $DRIVER_MEDICAL_CARD->where('id', $id)->first();

        if (file_exists(public_path('medical-card-images/' . $card->image))) {
            try {
                unlink(public_path('medical-card-images/' . $card->image));
            } catch (Throwable|Exception $e) {
            }
        }

        DriverMedicalCard::where('id', $id)->update([
            'image' => ''
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverTractor(Request $request): JsonResponse
    {
        $DRIVER_TRACTOR = DriverTractor::query();
        $id = $request->id ?? null;

        $tractor = $DRIVER_TRACTOR->where('id', $id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'tractor' => $tractor]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverTractor(Request $request): JsonResponse
    {
        $DRIVER_TRACTOR = DriverTractor::query();
        $id = $request->id ?? null;
        $driver_id = $request->driver_id ?? null;
        $number = $request->number ?? '';
        $plate_state = $request->plate_state ?? '';
        $plate_number = $request->plate_number ?? '';
        $year = $request->year ?? '';
        $make = $request->make ?? '';
        $model = $request->model ?? '';
        $vin = $request->vin ?? '';
        $color = $request->color ?? '';
        $type_id = $request->type_id ?? null;
        $axle = $request->axle ?? null;

        if (!$id) {
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'driver_tractors'");
            $number = str_pad($table_info[0]->Auto_increment, 4, "T000", STR_PAD_LEFT);
        }

        $tractor = $DRIVER_TRACTOR->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
            'number' => $number,
            'plate_state' => strtoupper($plate_state),
            'plate_number' => $plate_number,
            'year' => $year,
            'make' => $make,
            'model' => $model,
            'vin' => $vin,
            'color' => $color,
            'type_id' => $type_id,
            'axle' => $axle
        ]);

        $newTractor = DriverTractor::where('id', $tractor->id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'tractor' => $newTractor]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverTractor(Request $request): JsonResponse
    {
        $DRIVER_TRACTOR = DriverTractor::query();
        $id = $request->id ?? null;

        $DRIVER_TRACTOR->where('id', $id)->delete();

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriverTrailer(Request $request): JsonResponse
    {
        $DRIVER_TRAILER = DriverTrailer::query();
        $id = $request->id ?? null;

        $trailer = $DRIVER_TRAILER->where('id', $id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'trailer' => $trailer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverTrailer(Request $request): JsonResponse
    {
        $DRIVER_TRAILER = DriverTrailer::query();
        $id = $request->id ?? null;
        $driver_id = $request->driver_id ?? null;
        $number = $request->number ?? '';
        $plate_state = $request->plate_state ?? '';
        $plate_number = $request->plate_number ?? '';
        $year = $request->year ?? '';
        $make = $request->make ?? '';
        $model = $request->model ?? '';
        $vin = $request->vin ?? '';
        $tarps = $request->tarps ?? 0;
        $dimensions = $request->dimensions ?? '';
        $length = $request->length ?? 0.00;
        $width = $request->width ?? 0.00;
        $height = $request->height ?? 0.00;
        $type_id = $request->type_id ?? null;
        $liftgate = $request->liftgate ?? 0;
        $ramps = $request->ramps ?? 0;

        $length = filter_var($length, FILTER_SANITIZE_NUMBER_INT);
        $length = str_pad($length, 2, "00", STR_PAD_LEFT);

        $width = filter_var($width, FILTER_SANITIZE_NUMBER_INT);
        $width = str_pad($width, 3, "000", STR_PAD_LEFT);

        $height = filter_var($height, FILTER_SANITIZE_NUMBER_INT);
        $height = str_pad($height, 3, "000", STR_PAD_LEFT);

        if (!$id) {
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'driver_trailers'");
            $number = str_pad($table_info[0]->Auto_increment, 6, $length . "0000", STR_PAD_LEFT);
        }


        $trailer = $DRIVER_TRAILER->updateOrCreate([
            'id' => $id
        ], [
            'driver_id' => $driver_id,
            'number' => $number,
            'plate_state' => strtoupper($plate_state),
            'plate_number' => $plate_number,
            'year' => $year,
            'make' => $make,
            'model' => $model,
            'vin' => $vin,
            'tarps' => $tarps,
            'dimensions' => $dimensions,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'type_id' => $type_id,
            'liftgate' => $liftgate,
            'ramps' => $ramps
        ]);

        $newTrailer = DriverTrailer::where('id', $trailer->id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'trailer' => $newTrailer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverTrailer(Request $request): JsonResponse
    {
        $DRIVER_TRAILER = DriverTrailer::query();
        $id = $request->id ?? null;

        $DRIVER_TRAILER->where('id', $id)->delete();

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriversByCarrierId(Request $request) : JsonResponse
    {
        $CARRIER_DRIVER = new Driver();

        $carrier_id = $request->carrier_id ?? null;
        $agent_id = $request->agent_id ?? null;
        $owner_type = $request->owner_type ?? '';
        $name = $request->name ?? '';
        $count = 0;
        $drivers = [];

        if ($carrier_id){
            $count = count($CARRIER_DRIVER->where('carrier_id', $carrier_id)->get());

            $drivers = $CARRIER_DRIVER->whereRaw("1 = 1")
                ->whereRaw("carrier_id = $carrier_id")
                ->whereRaw("name like '$name%'")
                ->with(['contacts', 'tractor', 'trailer'])
                ->has('carrier')
                ->orderBy('name')
                ->get();

            return response()->json(['result' => 'OK', 'drivers' => $drivers, 'count' => $count]);
        }

        if ($agent_id){
            $count = count($CARRIER_DRIVER->where('agent_id', $agent_id)->get());

            $drivers = $CARRIER_DRIVER->whereRaw("1 = 1")
                ->whereRaw("agent_id = $agent_id")
                ->whereRaw("name like '$name%'")
                ->with(['contacts', 'tractor', 'trailer'])
                ->has('agent')
                ->orderBy('name')
                ->get();
        }

        return response()->json(['result' => 'OK', 'drivers' => $drivers, 'count' => $count]);
    }
}
