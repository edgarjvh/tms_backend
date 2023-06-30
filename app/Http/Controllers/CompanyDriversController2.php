<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\CompanyDriver;
use App\Models\Company;

class CompanyDriversController2 extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriver(Request $request): JsonResponse
    {
        $DRIVER = new CompanyDriver();
        $COMPANY = new Company();

        $driver_id = $request->driver_id ?? ($request->id ?? 0);
        $company_id = $request->company_id ?? 0;

        if ($company_id > 0) {
            $curDriver = $DRIVER->where('id', $driver_id)->first();

            $company = $COMPANY->where('id', $company_id)->first();

            $prefix = $request->prefix ?? ($curDriver ? $curDriver->prefix : '');
            $first_name = $request->first_name ?? ($curDriver ? $curDriver->first_name : '');
            $middle_name = $request->middle_name ?? ($curDriver ? $curDriver->middle_name : '');
            $last_name = $request->last_name ?? ($curDriver ? $curDriver->last_name : '');
            $suffix = $request->suffix ?? ($curDriver ? $curDriver->suffix : '');
            $title = $request->title ?? ($curDriver ? $curDriver->title : '');
            $department = $request->department ?? ($curDriver ? $curDriver->department : '');
            $email_work = $request->email_work ?? ($curDriver ? $curDriver->email_work : '');
            $email_personal = $request->email_personal ?? ($curDriver ? $curDriver->email_personal : '');
            $email_other = $request->email_other ?? ($curDriver ? $curDriver->email_other : '');
            $primary_email = $request->primary_email ?? ($curDriver ? $curDriver->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curDriver ? $curDriver->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curDriver ? $curDriver->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curDriver ? $curDriver->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curDriver ? $curDriver->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curDriver ? $curDriver->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curDriver ? $curDriver->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curDriver ? $curDriver->phone_ext : '');
            $country = $request->country ?? ($curDriver ? $curDriver->country : '');
            $address1 = $request->address1 ?? ($curDriver ? $curDriver->address1 : $company->address1);
            $address2 = $request->address2 ?? ($curDriver ? $curDriver->address2 : $company->address2);
            $city = $request->city ?? ($curDriver ? $curDriver->city : $company->city);
            $state = $request->state ?? ($curDriver ? $curDriver->state : $company->state);
            $zip_code = $request->zip_code ?? ($curDriver ? $curDriver->zip_code : $company->zip);
            $birthday = $request->birthday ?? ($curDriver ? $curDriver->birthday : '');
            $website = $request->website ?? ($curDriver ? $curDriver->website : '');
            $notes = $request->notes ?? ($curDriver ? $curDriver->notes : '');
            $is_primary_admin = $request->is_primary_admin ?? ($curDriver ? $curDriver->is_primary_admin : 0);
            $is_online = $request->is_online ?? ($curDriver ? $curDriver->is_online : 0);

            $driver_manager = $request->driver_manager ?? ($curDriver ? $curDriver->driver_manager : '');
            $division = $request->division ?? ($curDriver ? $curDriver->division : '');
            $unit_number = $request->unit_number ?? ($curDriver ? $curDriver->unit_number : '');
            $trailer_number = $request->trailer_number ?? ($curDriver ? $curDriver->trailer_number : '');
            $tractor_plate = $request->tractor_plate ?? ($curDriver ? $curDriver->tractor_plate : '');
            $trailer_plate = $request->trailer_plate ?? ($curDriver ? $curDriver->trailer_plate : '');
            $drivers_license_number = $request->drivers_license_number ?? ($curDriver ? $curDriver->drivers_license_number : '');
            $driver_state = $request->driver_state ?? ($curDriver ? $curDriver->driver_state : '');
            $expiration_date = $request->expiration_date ?? ($curDriver ? $curDriver->expiration_date : '');
            $endorsements = $request->endorsements ?? ($curDriver ? $curDriver->endorsements : '');
            $hire_date = $request->hire_date ?? ($curDriver ? $curDriver->hire_date : '');
            $termination_date = $request->termination_date ?? ($curDriver ? $curDriver->termination_date : '');
            $physical_date = $request->physical_date ?? ($curDriver ? $curDriver->physical_date : '');
            $renewal_date = $request->renewal_date ?? ($curDriver ? $curDriver->renewal_date : '');
            $drug_test_date = $request->drug_test_date ?? ($curDriver ? $curDriver->drug_test_date : '');
            $pay_rate = $request->pay_rate ?? ($curDriver ? $curDriver->pay_rate : 0.00);
            $per_hour_per_day = $request->per_hour_per_day ?? ($curDriver ? $curDriver->per_hour_per_day : 0.00);
            $per_hour_per_day_unit = $request->per_hour_per_day_unit ?? ($curDriver ? $curDriver->per_hour_per_day_unit : 'hr');



            $is_primary_admin = (int)$is_primary_admin;

            $driver = $DRIVER->updateOrCreate([
                'id' => $driver_id
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
                    'drug_test_date' => $drug_test_date,
                    'pay_rate' => $pay_rate,
                    'per_hour_per_day' => $per_hour_per_day,
                    'per_hour_per_day_unit' => $per_hour_per_day_unit
                ]);

            $newDriver = $DRIVER->where('id', $driver->id)
                ->with('company')
                ->has('company')
                ->first();

            $drivers = $DRIVER->where('company_id', $company_id)
                ->with('company')
                ->has('company')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'driver' => $newDriver, 'drivers' => $drivers]);
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
        $DRIVER = new CompanyDriver();

        $driver_id = $_POST['driver_id'];
        $company_id = $request->company_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $driver = $DRIVER->where('id', $driver_id)->first();
        $cur_avatar = $driver->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))){
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $DRIVER->where('id', $driver_id)->update([
            'avatar' => $new_avatar
        ]);

        $driver = $DRIVER->where('id', $driver_id)
            ->with('company')
            ->has('company')
            ->first();

        $drivers = $DRIVER->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'driver' => $driver, 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $DRIVER = new CompanyDriver();

        $driver_id = $request->driver_id ?? ($request->id ?? 0);
        $company_id = $request->company_id;

        $driver = $DRIVER->where('id', $driver_id)->first();

        if (file_exists(public_path('avatars/' . $driver->avatar))){
            try {
                unlink(public_path('avatars/' . $driver->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $DRIVER->where('id', $driver_id)->update([
            'avatar' => ''
        ]);

        $driver = $DRIVER->where('id', $driver_id)
            ->with('company')
            ->has('company')
            ->first();

        $drivers = $DRIVER->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $driver, 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriver(Request $request): JsonResponse
    {
        $DRIVER = new CompanyDriver();

        $driver_id = $request->driver_id ?? ($request->id ?? 0);

        $driver = $DRIVER->where('id', $driver_id)->first();

        $DRIVER->where('id', $driver_id)->delete();
        $drivers = $DRIVER->where('company_id', $driver->company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyDriversSearch(Request $request): JsonResponse
    {
        $DRIVER = new CompanyDriver();

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
            $drivers = $DRIVER->whereRaw("1 = 1")
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
            $drivers = $DRIVER->whereRaw("1 = 1")
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

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }
}
