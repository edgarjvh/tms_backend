<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\OwnerOperator;
use App\Models\Company;

class OwnerOperatorsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOperator(Request $request): JsonResponse
    {
        $OPERATOR = new OwnerOperator();
        $COMPANY = new Company();

        $operator_id = $request->operator_id ?? ($request->id ?? 0);
        $company_id = $request->company_id ?? 0;

        if ($company_id > 0) {
            $curOperator = $OPERATOR->where('id', $operator_id)->first();

            $company = $COMPANY->where('id', $company_id)->first();

            $prefix = $request->prefix ?? ($curOperator ? $curOperator->prefix : '');
            $first_name = $request->first_name ?? ($curOperator ? $curOperator->first_name : '');
            $middle_name = $request->middle_name ?? ($curOperator ? $curOperator->middle_name : '');
            $last_name = $request->last_name ?? ($curOperator ? $curOperator->last_name : '');
            $suffix = $request->suffix ?? ($curOperator ? $curOperator->suffix : '');
            $title = $request->title ?? ($curOperator ? $curOperator->title : '');
            $department = $request->department ?? ($curOperator ? $curOperator->department : '');
            $email_work = $request->email_work ?? ($curOperator ? $curOperator->email_work : '');
            $email_personal = $request->email_personal ?? ($curOperator ? $curOperator->email_personal : '');
            $email_other = $request->email_other ?? ($curOperator ? $curOperator->email_other : '');
            $primary_email = $request->primary_email ?? ($curOperator ? $curOperator->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curOperator ? $curOperator->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curOperator ? $curOperator->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curOperator ? $curOperator->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curOperator ? $curOperator->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curOperator ? $curOperator->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curOperator ? $curOperator->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curOperator ? $curOperator->phone_ext : '');
            $country = $request->country ?? ($curOperator ? $curOperator->country : '');
            $address1 = $request->address1 ?? ($curOperator ? $curOperator->address1 : $company->address1);
            $address2 = $request->address2 ?? ($curOperator ? $curOperator->address2 : $company->address2);
            $city = $request->city ?? ($curOperator ? $curOperator->city : $company->city);
            $state = $request->state ?? ($curOperator ? $curOperator->state : $company->state);
            $zip_code = $request->zip_code ?? ($curOperator ? $curOperator->zip_code : $company->zip);
            $birthday = $request->birthday ?? ($curOperator ? $curOperator->birthday : '');
            $website = $request->website ?? ($curOperator ? $curOperator->website : '');
            $notes = $request->notes ?? ($curOperator ? $curOperator->notes : '');
            $is_primary_admin = $request->is_primary_admin ?? ($curOperator ? $curOperator->is_primary_admin : 0);
            $is_online = $request->is_online ?? ($curOperator ? $curOperator->is_online : 0);
            $operator_own_units = $request->operator_own_units ?? ($curOperator ? $curOperator->operator_own_units : 0);
            $driver_manager = $request->driver_manager ?? ($curOperator ? $curOperator->driver_manager : '');
            $division = $request->division ?? ($curOperator ? $curOperator->division : '');
            $unit_number = $request->unit_number ?? ($curOperator ? $curOperator->unit_number : '');
            $trailer_number = $request->trailer_number ?? ($curOperator ? $curOperator->trailer_number : '');
            $tractor_plate = $request->tractor_plate ?? ($curOperator ? $curOperator->tractor_plate : '');
            $trailer_plate = $request->trailer_plate ?? ($curOperator ? $curOperator->trailer_plate : '');
            $drivers_license_number = $request->drivers_license_number ?? ($curOperator ? $curOperator->drivers_license_number : '');
            $driver_state = $request->driver_state ?? ($curOperator ? $curOperator->driver_state : '');
            $expiration_date = $request->expiration_date ?? ($curOperator ? $curOperator->expiration_date : '');
            $endorsements = $request->endorsements ?? ($curOperator ? $curOperator->endorsements : '');
            $hire_date = $request->hire_date ?? ($curOperator ? $curOperator->hire_date : '');
            $termination_date = $request->termination_date ?? ($curOperator ? $curOperator->termination_date : '');
            $physical_date = $request->physical_date ?? ($curOperator ? $curOperator->physical_date : '');
            $renewal_date = $request->renewal_date ?? ($curOperator ? $curOperator->renewal_date : '');
            $drug_test_date = $request->drug_test_date ?? ($curOperator ? $curOperator->drug_test_date : '');
            $is_primary_admin = (int)$is_primary_admin;

            $operator = $OPERATOR->updateOrCreate([
                'id' => $operator_id
            ],
                [
                    'company_id' => $company_id,
                    'prefix' => $prefix,
                    'first_name' => ucwords(trim($first_name)),
                    'middle_name' => ucwords(trim($middle_name)),
                    'last_name' => ucwords(trim($last_name)),
                    'suffix' => $suffix,
                    'title' => $title,
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
                    'is_primary_admin' => $is_primary_admin,
                    'is_online' => $is_online,
                    'operator_own_units' => $operator_own_units,
                    'driver_manager' => $driver_manager,
                    'division' => $division,
                    'unit_number' => $unit_number,
                    'trailer_number' => $trailer_number,
                    'tractor_plate' => $tractor_plate,
                    'trailer_plate' => $trailer_plate,
                    'drivers_license_number' => $drivers_license_number,
                    'driver_state' => $driver_state,
                    'expiration_date' => $expiration_date,
                    'endorsements' => $endorsements,
                    'hire_date' => $hire_date,
                    'termination_date' => $termination_date,
                    'physical_date' => $physical_date,
                    'renewal_date' => $renewal_date,
                    'drug_test_date' => $drug_test_date
                ]);

            $newOperator = $OPERATOR->where('id', $operator->id)
                ->with('company')
                ->has('company')
                ->first();

            $operators = $OPERATOR->where('company_id', $company_id)
                ->with('company')
                ->has('company')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'operator' => $newOperator, 'operators' => $operators]);
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
        $OPERATOR = new OwnerOperator();

        $operator_id = $_POST['operator_id'];
        $company_id = $request->company_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $operator = $OPERATOR->where('id', $operator_id)->first();
        $cur_avatar = $operator->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))){
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $OPERATOR->where('id', $operator_id)->update([
            'avatar' => $new_avatar
        ]);

        $operator = $OPERATOR->where('id', $operator_id)
            ->with('company')
            ->has('company')
            ->first();

        $operators = $OPERATOR->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'operator' => $operator, 'operators' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $OPERATOR = new OwnerOperator();

        $operator_id = $request->operator_id ?? ($request->id ?? 0);
        $company_id = $request->company_id;

        $operator = $OPERATOR->where('id', $operator_id)->first();

        if (file_exists(public_path('avatars/' . $operator->avatar))){
            try {
                unlink(public_path('avatars/' . $operator->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $OPERATOR->where('id', $operator_id)->update([
            'avatar' => ''
        ]);

        $operator = $OPERATOR->where('id', $operator_id)
            ->with('company')
            ->has('company')
            ->first();

        $operators = $OPERATOR->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'operator' => $operator, 'operators' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOperator(Request $request): JsonResponse
    {
        $OPERATOR = new OwnerOperator();

        $operator_id = $request->operator_id ?? ($request->id ?? 0);

        $operator = $OPERATOR->where('id', $operator_id)->first();

        $OPERATOR->where('id', $operator_id)->delete();
        $operators = $OPERATOR->where('company_id', $operator->company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'operators' => $operators]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyOperatorsSearch(Request $request): JsonResponse
    {
        $OPERATOR = new OwnerOperator();

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
            $operators = $OPERATOR->whereRaw("1 = 1")
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
            $operators = $OPERATOR->whereRaw("1 = 1")
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

        return response()->json(['result' => 'OK', 'operators' => $operators]);
    }
}
