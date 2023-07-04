<?php

namespace App\Http\Controllers;

use App\Models\CompanyOperatorEmergencyContact;
use App\Models\CompanyOperatorLicense;
use App\Models\CompanyOperatorMailingAddress;
use App\Models\CompanyOperatorMedicalCard;
use App\Models\CompanyOperatorTractor;
use App\Models\CompanyOperatorTrailer;
use App\Models\LicenseClass;
use App\Models\LicenseEndorsement;
use App\Models\LicenseRestriction;
use App\Models\OwnerOperator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\CompanyOperator;
use App\Models\Company;
use Throwable;

class CompanyOperatorsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOperatorById(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();
        $id = $request->id ?? null;

        $operator = $OPERATOR->where('id', $id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        return response()->json(['result' => 'OK', 'driver' => $operator]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOperators(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();
        $company_id = $request->company_id ?? null;

        $operators = $OPERATOR->where('company_id', $company_id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->get();

        return response()->json(['result' => 'OK', 'drivers' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOperatorByCode(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();
        $code = strtoupper($request->code ?? '');

        $operator = $OPERATOR->whereRaw("1 = 1")
            ->whereRaw("code LIKE '$code%'")
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        return response()->json(['result' => 'OK', 'driver' => $operator]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOperator(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();

        $id = $request->id ?? null;
        $code = $request->code ?? '';
        $company_id = $request->company_id ?? null;
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
        $mailing_contact_id = $request->mailing_contact_id ?? null;
        $mailing_contact_primary_phone = $request->mailing_contact_primary_phone ?? 'work';
        $mailing_contact_primary_email = $request->mailing_contact_primary_email ?? 'work';
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;

        if (!$id) {
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'company_operators'");
            $code = str_pad($table_info[0]->Auto_increment, 6, "OP0000", STR_PAD_LEFT);
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $operator = $OPERATOR->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => $code,
                'company_id' => $company_id,
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
                'mailing_contact_id' => $mailing_contact_id,
                'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                'mailing_contact_primary_email' => $mailing_contact_primary_email,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
            ]);

        if ($with_contact) {
            $contacts = CompanyOperatorEmergencyContact::where('operator_id', $operator->id)->get();

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
                $contact = new CompanyOperatorEmergencyContact();
                $contact->operator_id = $operator->id;
                $contact->first_name = ucwords(trim($contact_first));
                $contact->last_name = ucwords(trim($contact_last));
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $ext;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = ucwords($city);
                $contact->state = strtoupper($state);
                $contact->zip_code = $zip;
                $contact->is_primary = 0;
                $contact->priority = 1;
                $contact->save();

            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    CompanyOperatorEmergencyContact::where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $ext
                    ]);
                }
            }
        }

        $newOperator = CompanyOperator::where('id', $operator->id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->first();

        $operators = CompanyOperator::where('company_id', $company_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $newOperator, 'drivers' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOperator(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();

        $id = $request->id ?? null;

        $operator = $OPERATOR->where('id', $id)->first();

        $OPERATOR->where('id', $id)->delete();

        $operators = $OPERATOR->where('company_id', $operator->company_id)
            ->with([
                'mailing_address',
                'contacts',
                'license',
                'medical_card',
                'tractor',
                'trailer'
            ])->get();

        return response()->json(['result' => 'OK', 'drivers' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyOperatorsSearch(Request $request): JsonResponse
    {
        $OPERATOR = CompanyOperator::query();

        $name = $request->search[0]['data'] ?? '';
        $address1 = $request->search[1]['data'] ?? '';
        $address2 = $request->search[2]['data'] ?? '';
        $city = $request->search[3]['data'] ?? '';
        $state = $request->search[4]['data'] ?? '';
        $zip = $request->search[5]['data'] ?? '';
        $contact_name = $request->search[6]['data'] ?? '';
        $contact_phone = $request->search[7]['data'] ?? '';
        $contact_phone_ext = $request->search[8]['data'] ?? '';

        $operators = $OPERATOR->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(address1) like '$address1%'")
            ->whereRaw("LOWER(address2) like '$address2%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("LOWER(zip) like '$zip%'")
            ->whereRaw("LOWER(contact_name) like '$contact_name%'")
            ->whereRaw("LOWER(contact_phone) like '$contact_phone%'")
            ->whereRaw("LOWER(contact_phone_ext) like '$contact_phone_ext%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorMailingAddress(Request $request): JsonResponse
    {
        $MAILING_ADDRESS = CompanyOperatorMailingAddress::query();

        $id = $request->id ?? null;
        $company_operator_id = $request->company_operator_id ?? null;
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
            'company_operator_id' => $company_operator_id,
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

        CompanyOperator::query()->updateOrCreate([
            'id' => $company_operator_id
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
    public function deleteCompanyOperatorMailingAddress(Request $request): JsonResponse
    {
        $MAILING_ADDRESS = CompanyOperatorMailingAddress::query();
        $company_operator_id = $request->company_operator_id ?? null;

        $MAILING_ADDRESS->where('company_operator_id', $company_operator_id)->delete();

        CompanyOperator::query()->updateOrCreate([
            'id' => $company_operator_id
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
    public function getCompanyOperatorEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = CompanyOperatorEmergencyContact::query();
        $id = $request->id ?? null;

        $contact = $EMERGENCY_CONTACT->where('id', $id)->first();

        return response()->json(['result' => 'OK', 'contact' => $contact]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = CompanyOperatorEmergencyContact::query();
        $id = $request->id ?? null;
        $operator_id = $request->operator_id ?? null;

        $curContact = $EMERGENCY_CONTACT->where('id', $id)->first();

        $prefix = $request->prefix ?? ($curContact->prefix ?? '');
        $first_name = $request->first_name ?? ($curContact->first_name ?? '');
        $middle_name = $request->middle_name ?? ($curContact->middle_name ?? '');
        $last_name = $request->last_name ?? ($curContact->last_name ?? '');
        $suffix = $request->suffix ?? ($curContact->suffix ?? '');
        $title = $request->title ?? ($curContact->title ?? '');
        $company = $request->company ?? ($curContact->company ?? '');
        $department = $request->department ?? ($curContact->department ?? '');
        $email_work = $request->email_work ?? ($curContact->email_work ?? '');
        $email_personal = $request->email_personal ?? ($curContact->email_personal ?? '');
        $email_other = $request->email_other ?? ($curContact->email_other ?? '');
        $primary_email = $request->primary_email ?? ($curContact->primary_email ?? 'work');
        $phone_work = $request->phone_work ?? ($curContact->phone_work ?? '');
        $phone_work_fax = $request->phone_work_fax ?? ($curContact->phone_work_fax ?? '');
        $phone_mobile = $request->phone_mobile ?? ($curContact->phone_mobile ?? '');
        $phone_direct = $request->phone_direct ?? ($curContact->phone_direct ?? '');
        $phone_other = $request->phone_other ?? ($curContact->phone_other ?? '');
        $primary_phone = $request->primary_phone ?? ($curContact->primary_phone ?? 'work');
        $phone_ext = $request->phone_ext ?? ($curContact->phone_ext ?? '');
        $country = $request->country ?? ($curContact->country ?? '');
        $address1 = $request->address1 ?? ($curContact->address1 ?? '');
        $address2 = $request->address2 ?? ($curContact->address2 ?? '');
        $city = $request->city ?? ($curContact->city ?? '');
        $state = $request->state ?? ($curContact->state ?? '');
        $zip_code = $request->zip_code ?? ($curContact->zip_code ?? '');
        $birthday = $request->birthday ?? ($curContact->birthday ?? '');
        $website = $request->website ?? ($curContact->website ?? '');
        $notes = $request->notes ?? ($curContact->notes ?? '');
        $is_primary = $request->is_primary ?? ($curContact->is_primary ?? 0);
        $is_online = $request->is_online ?? ($curContact->is_online ?? 0);
        $type = $request->type ?? ($curContact->type ?? 'internal');
        $relationship = $request->relationship ?? ($curContact->relationship ?? '');
        $priority = $request->priority ?? ($curContact->priority ?? 0);

        $is_primary = (int)$is_primary;
        $priority = (int)$priority;
        $is_online = (int)$is_online;


        $contact = $EMERGENCY_CONTACT->updateOrCreate([
            'id' => $id
        ], [
            'operator_id' => $operator_id,
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
            'relationship' => $relationship,
            'priority' => $priority
        ]);

        $newContact = CompanyOperatorEmergencyContact::where('id', $contact->id)
            ->with('company_operator')
            ->has('company_operator')
            ->first();

        $contacts = CompanyOperatorEmergencyContact::where('operator_id', $operator_id)
            ->with('company_operator')
            ->has('company_operator')
            ->orderBy('priority')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $newContact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorEmergencyContact(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = CompanyOperatorEmergencyContact::query();
        $id = $request->id ?? null;

        $contact = $EMERGENCY_CONTACT->where('id', $id)->first();

        $EMERGENCY_CONTACT->where('id', $id)->delete();

        $contacts = $EMERGENCY_CONTACT->where('operator_id', $contact->operator_id)
            ->with('company_operator')
            ->has('company_operator')
            ->orderBy('priority')
            ->get();

        return response()->json(['result', 'OK', 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function uploadCompanyOperatorEmergencyContactAvatar(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = CompanyOperatorEmergencyContact::query();

        $contact_id = $_POST['contact_id'];
        $operator_id = $request->operator_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $contact = $EMERGENCY_CONTACT->where('id', $contact_id)->first();
        $cur_avatar = $contact->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))) {
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable|Exception $e) {
                }
            }
        }

        CompanyOperatorEmergencyContact::where('id', $contact_id)->update([
            'avatar' => $new_avatar
        ]);

        $contact = CompanyOperatorEmergencyContact::where('id', $contact_id)
            ->with('company_operator')
            ->has('company_operator')
            ->first();

        $contacts = CompanyOperatorEmergencyContact::where('operator_id', $operator_id)
            ->with('company_operator')
            ->has('company_operator')
            ->orderBy('priority')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCompanyOperatorEmergencyContactAvatar(Request $request): JsonResponse
    {
        $EMERGENCY_CONTACT = CompanyOperatorEmergencyContact::query();

        $contact_id = $request->contact_id ?? ($request->id ?? 0);
        $operator_id = $request->operator_id;

        $contact = $EMERGENCY_CONTACT->where('id', $contact_id)->first();

        if (file_exists(public_path('avatars/' . $contact->avatar))) {
            try {
                unlink(public_path('avatars/' . $contact->avatar));
            } catch (Throwable|Exception $e) {
            }
        }

        CompanyOperatorEmergencyContact::where('id', $contact_id)->update([
            'avatar' => ''
        ]);

        $contact = CompanyOperatorEmergencyContact::where('id', $contact_id)
            ->with('company_operator')
            ->has('company_operator')
            ->first();

        $contacts = CompanyOperatorEmergencyContact::where('operator_id', $operator_id)
            ->with('company_operator')
            ->has('company_operator')
            ->orderBy('priority')
            ->get();

        return response()->json(['result' => 'OK', 'contact' => $contact, 'contacts' => $contacts]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyOperatorLicense(Request $request): JsonResponse
    {
        $OPERATOR_LICENSE = CompanyOperatorLicense::query();
        $id = $request->id ?? null;

        $license = $OPERATOR_LICENSE->where('id', $id)
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
    public function saveCompanyOperatorLicense(Request $request): JsonResponse
    {
        $OPERATOR_LICENSE = CompanyOperatorLicense::query();
        $id = $request->id ?? null;
        $company_operator_id = $request->company_operator_id ?? null;
        $license_number = $request->license_number ?? '';
        $state = $request->state ?? '';
        $cdl = $request->cdl ?? 0;
        $class_id = $request->class_id ?? null;
        $endorsement_id = $request->endorsement_id ?? null;
        $expiration_date = $request->expiration_date ?? null;
        $restriction_id = $request->restriction_id ?? null;

        $license = $OPERATOR_LICENSE->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_id' => $company_operator_id,
            'license_number' => $license_number,
            'state' => strtoupper($state),
            'cdl' => $cdl,
            'class_id' => $class_id,
            'endorsement_id' => $endorsement_id,
            'expiration_date' => $expiration_date,
            'restriction_id' => $restriction_id
        ]);

        $newLicense = CompanyOperatorLicense::where('id', $license->id)
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
    public function deleteCompanyOperatorLicense(Request $request): JsonResponse
    {
        $OPERATOR_LICENSE = CompanyOperatorLicense::query();
        $id = $request->id ?? null;

        $license = CompanyOperatorLicense::where('id', $id)->first();

        if ($license) {
            $OPERATOR_LICENSE->where('id', $id)->delete();

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
    public function uploadOperatorLicenseImage(Request $request): JsonResponse
    {
        $OPERATOR_LICENSE = CompanyOperatorLicense::query();

        $id = $_POST['license_id'];
        $license_number = $_POST['license_number'];
        $state = $_POST['state'];
        $class_id = $_POST['class_id'];
        $cdl = $_POST['cdl'];
        $endorsement_id = $_POST['endorsement_id'];
        $expiration_date = $_POST['expiration_date'];
        $restriction_id = $_POST['restriction_id'];

        $license = $OPERATOR_LICENSE->updateOrCreate([
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

        CompanyOperatorLicense::where('id', $id)->update([
            'image' => $new_image
        ]);


        move_uploaded_file($fileData['tmp_name'], public_path('license-images/' . $new_image));

        return response()->json(['result' => 'OK', 'image' => $new_image]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOperatorLicenseImage(Request $request): JsonResponse
    {
        $OPERATOR_LICENSE = new CompanyOperatorLicense();

        $id = $request->id ?? null;

        $license = $OPERATOR_LICENSE->where('id', $id)->first();

        if (file_exists(public_path('license-images/' . $license->image))) {
            try {
                unlink(public_path('license-images/' . $license->image));
            } catch (Throwable|Exception $e) {
            }
        }

        CompanyOperatorLicense::where('id', $id)->update([
            'image' => ''
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyOperatorMedicalCard(Request $request): JsonResponse
    {
        $OPERATOR_MEDICAL_CARD = CompanyOperatorMedicalCard::query();
        $id = $request->id ?? null;

        $card = $OPERATOR_MEDICAL_CARD->where('id', $id)->first();

        return response()->json(['result' => 'OK', 'card' => $card]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorMedicalCard(Request $request): JsonResponse
    {
        $OPERATOR_MEDICAL_CARD = CompanyOperatorMedicalCard::query();
        $id = $request->id ?? null;
        $company_operator_id = $request->company_operator_id ?? null;
        $issue_date = $request->issue_date ?? null;
        $expiration_date = $request->expiration_date ?? null;

        $card = $OPERATOR_MEDICAL_CARD->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_id' => $company_operator_id,
            'issue_date' => $issue_date,
            'expiration_date' => $expiration_date
        ]);

        $newCard = CompanyOperatorMedicalCard::where('id', $card->id)->first();

        return response()->json(['result' => 'OK', 'card' => $newCard]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorMedicalCard(Request $request): JsonResponse
    {
        $OPERATOR_MEDICAL_CARD = CompanyOperatorMedicalCard::query();
        $id = $request->id ?? null;

        $card = CompanyOperatorMedicalCard::where('id', $id)->first();

        if ($card) {
            $OPERATOR_MEDICAL_CARD->where('id', $id)->delete();

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
    public function uploadOperatorMedicalCardImage(Request $request): JsonResponse
    {
        $OPERATOR_MEDICAL_CARD = CompanyOperatorMedicalCard::query();

        $id = $_POST['medical_card_id'];
        $issue_date = $_POST['issue_date'];
        $expiration_date = $_POST['expiration_date'];

        $card = $OPERATOR_MEDICAL_CARD->updateOrCreate([
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

        CompanyOperatorMedicalCard::where('id', $id)->update([
            'image' => $new_image
        ]);


        move_uploaded_file($fileData['tmp_name'], public_path('medical-card-images/' . $new_image));

        return response()->json(['result' => 'OK', 'image' => $new_image]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOperatorMedicalCardImage(Request $request): JsonResponse
    {
        $OPERATOR_MEDICAL_CARD = new CompanyOperatorMedicalCard();

        $id = $request->id ?? null;

        $card = $OPERATOR_MEDICAL_CARD->where('id', $id)->first();

        if (file_exists(public_path('medical-card-images/' . $card->image))) {
            try {
                unlink(public_path('medical-card-images/' . $card->image));
            } catch (Throwable|Exception $e) {
            }
        }

        CompanyOperatorMedicalCard::where('id', $id)->update([
            'image' => ''
        ]);

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyOperatorTractor(Request $request): JsonResponse
    {
        $OPERATOR_TRACTOR = CompanyOperatorTractor::query();
        $id = $request->id ?? null;

        $tractor = $OPERATOR_TRACTOR->where('id', $id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'tractor' => $tractor]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorTractor(Request $request): JsonResponse
    {
        $OPERATOR_TRACTOR = CompanyOperatorTractor::query();
        $id = $request->id ?? null;
        $company_operator_id = $request->company_operator_id ?? null;
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
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'company_operator_tractors'");
            $number = str_pad($table_info[0]->Auto_increment, 4, "T000", STR_PAD_LEFT);
        }

        $tractor = $OPERATOR_TRACTOR->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_id' => $company_operator_id,
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

        $newTractor = CompanyOperatorTractor::where('id', $tractor->id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'tractor' => $newTractor]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorTractor(Request $request): JsonResponse
    {
        $OPERATOR_TRACTOR = CompanyOperatorTractor::query();
        $id = $request->id ?? null;

        $OPERATOR_TRACTOR->where('id', $id)->delete();

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyOperatorTrailer(Request $request): JsonResponse
    {
        $OPERATOR_TRAILER = CompanyOperatorTrailer::query();
        $id = $request->id ?? null;

        $trailer = $OPERATOR_TRAILER->where('id', $id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'trailer' => $trailer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorTrailer(Request $request): JsonResponse
    {
        $OPERATOR_TRAILER = CompanyOperatorTrailer::query();
        $id = $request->id ?? null;
        $company_operator_id = $request->company_operator_id ?? null;
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
            $table_info = DB::select("SHOW TABLE STATUS LIKE 'company_operator_trailers'");
            $number = str_pad($table_info[0]->Auto_increment, 6, $length . "0000", STR_PAD_LEFT);
        }


        $trailer = $OPERATOR_TRAILER->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_id' => $company_operator_id,
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

        $newTrailer = CompanyOperatorTrailer::where('id', $trailer->id)
            ->with([
                'type'
            ])->first();

        return response()->json(['result' => 'OK', 'trailer' => $newTrailer]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorTrailer(Request $request): JsonResponse
    {
        $OPERATOR_TRAILER = CompanyOperatorTrailer::query();
        $id = $request->id ?? null;

        $OPERATOR_TRAILER->where('id', $id)->delete();

        return response()->json(['result' => 'OK']);
    }
}
