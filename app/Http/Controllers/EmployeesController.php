<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmployees() : JsonResponse{
        $employees = Employee::with(['company'])->get();

        return response()->json(['result' => 'OK', 'employees' => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveEmployee(Request $request): JsonResponse
    {
        $EMPLOYEE = new Employee();
        $COMPANY = new Company();

        $employee_id = $request->employee_id ?? ($request->id ?? 0);
        $company_id = $request->company_id ?? 0;

        if ($company_id > 0) {
            $curEmployee = $EMPLOYEE->where('id', $employee_id)->first();

            $company = $COMPANY->where('id', $company_id)->first();

            $prefix = $request->prefix ?? ($curEmployee ? $curEmployee->prefix : '');
            $first_name = $request->first_name ?? ($curEmployee ? $curEmployee->first_name : '');
            $middle_name = $request->middle_name ?? ($curEmployee ? $curEmployee->middle_name : '');
            $last_name = $request->last_name ?? ($curEmployee ? $curEmployee->last_name : '');
            $suffix = $request->suffix ?? ($curEmployee ? $curEmployee->suffix : '');
            $title = $request->title ?? ($curEmployee ? $curEmployee->title : '');
            $department = $request->department ?? ($curEmployee ? $curEmployee->department : '');
            $email_work = $request->email_work ?? ($curEmployee ? $curEmployee->email_work : '');
            $email_personal = $request->email_personal ?? ($curEmployee ? $curEmployee->email_personal : '');
            $email_other = $request->email_other ?? ($curEmployee ? $curEmployee->email_other : '');
            $primary_email = $request->primary_email ?? ($curEmployee ? $curEmployee->primary_email : 'work');
            $phone_work = $request->phone_work ?? ($curEmployee ? $curEmployee->phone_work : '');
            $phone_work_fax = $request->phone_work_fax ?? ($curEmployee ? $curEmployee->phone_work_fax : '');
            $phone_mobile = $request->phone_mobile ?? ($curEmployee ? $curEmployee->phone_mobile : '');
            $phone_direct = $request->phone_direct ?? ($curEmployee ? $curEmployee->phone_direct : '');
            $phone_other = $request->phone_other ?? ($curEmployee ? $curEmployee->phone_other : '');
            $primary_phone = $request->primary_phone ?? ($curEmployee ? $curEmployee->primary_phone : 'work');
            $phone_ext = $request->phone_ext ?? ($curEmployee ? $curEmployee->phone_ext : '');
            $country = $request->country ?? ($curEmployee ? $curEmployee->country : '');
            $address1 = $request->address1 ?? ($curEmployee ? $curEmployee->address1 : $company->address1);
            $address2 = $request->address2 ?? ($curEmployee ? $curEmployee->address2 : $company->address2);
            $city = $request->city ?? ($curEmployee ? $curEmployee->city : $company->city);
            $state = $request->state ?? ($curEmployee ? $curEmployee->state : $company->state);
            $zip_code = $request->zip_code ?? ($curEmployee ? $curEmployee->zip_code : $company->zip);
            $birthday = $request->birthday ?? ($curEmployee ? $curEmployee->birthday : '');
            $website = $request->website ?? ($curEmployee ? $curEmployee->website : '');
            $notes = $request->notes ?? ($curEmployee ? $curEmployee->notes : '');
            $is_primary_admin = $request->is_primary_admin ?? ($curEmployee ? $curEmployee->is_primary_admin : 0);
            $is_online = $request->is_online ?? ($curEmployee ? $curEmployee->is_online : 0);

            $is_primary_admin = (int)$is_primary_admin;

            $employee = $EMPLOYEE->updateOrCreate([
                'id' => $employee_id
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

            if ($is_primary_admin > 0){
                Employee::query()->where('id','<>', $employee->id)->update([
                   'is_primary_admin' => 0
                ]);
            }

            $newEmployee = $EMPLOYEE->where('id', $employee->id)
                ->with('company')
                ->has('company')
                ->first();

            $employees = $EMPLOYEE->where('company_id', $company_id)
                ->with('company')
                ->has('company')
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'employee' => $newEmployee, 'employees' => $employees]);
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
        $EMPLOYEE = new Employee();

        $employee_id = $_POST['employee_id'];
        $company_id = $request->company_id;
        $fileData = $_FILES['avatar'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $employee = $EMPLOYEE->where('id', $employee_id)->first();
        $cur_avatar = $employee->avatar;
        $new_avatar = uniqid() . '.' . $extension;

        if ($cur_avatar) {
            if (file_exists(public_path('avatars/' . $cur_avatar))){
                try {
                    unlink(public_path('avatars/' . $cur_avatar));
                } catch (Throwable | Exception $e) {
                }
            }
        }

        $EMPLOYEE->where('id', $employee_id)->update([
            'avatar' => $new_avatar
        ]);

        $employee = $EMPLOYEE->where('id', $employee_id)
            ->with('company')
            ->has('company')
            ->first();

        $employees = $EMPLOYEE->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        move_uploaded_file($fileData['tmp_name'], public_path('avatars/' . $new_avatar));

        return response()->json(['result' => 'OK', 'employee' => $employee, 'employees' => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $EMPLOYEE = new Employee();

        $employee_id = $request->employee_id ?? ($request->id ?? 0);
        $company_id = $request->company_id;

        $employee = $EMPLOYEE->where('id', $employee_id)->first();

        if (file_exists(public_path('avatars/' .$employee->avatar))){
            try {
                unlink(public_path('avatars/' . $employee->avatar));
            } catch (Throwable | Exception $e) {
            }
        }

        $EMPLOYEE->where('id', $employee_id)->update([
            'avatar' => ''
        ]);

        $employee = $EMPLOYEE->where('id', $employee_id)
            ->with('company')
            ->has('company')
            ->first();

        $employees = $EMPLOYEE->where('company_id', $company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'employee' => $employee, 'employees' => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteEmployee(Request $request): JsonResponse
    {
        $EMPLOYEE = new Employee();

        $employee_id = $request->employee_id ?? ($request->id ?? 0);

        $employee = $EMPLOYEE->where('id', $employee_id)->first();

        $EMPLOYEE->where('id', $employee_id)->delete();
        $employees = $EMPLOYEE->where('company_id', $employee->company_id)
            ->with('company')
            ->has('company')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'employees' => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function companyEmployeesSearch(Request $request): JsonResponse
    {
        $EMPLOYEE = new Employee();

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
            $employees = $EMPLOYEE->whereRaw("1 = 1")
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
            $employees = $EMPLOYEE->whereRaw("1 = 1")
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

        return response()->json(['result' => 'OK', 'employees' => $employees]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetEmployeePassword (Request $request): JsonResponse {
        $id = $request->id ?? 0;
        $EMPLOYEE = new Employee();

        if ($id > 0){
            $newPassword = $this->random_str();

            $hashed = Hash::make($newPassword);

            $EMPLOYEE->updateOrCreate([
                'id' => $id
            ],[
                'password' => $hashed
            ]);

            $employee = $EMPLOYEE->where('id', $id)->first();

            return response()->json(['result' => 'OK', 'employee' => $employee, 'newpass' => $newPassword]);
        }else{
            return response()->json(['result' => 'no employee']);
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
