<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompanyById(Request $request) : JsonResponse{
        $id = $request->id ?? 0;

        $COMPANY = new Company();

        $company = $COMPANY->where('id', $id)
            ->with([
                'mailing_address',
                'employees',
                'agents',
                'drivers',
                'operators'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'company' => $company]);
    }

    /**
     * @return JsonResponse
     */
    public function companies(): JsonResponse
    {
        $COMPANY = new Company();

        $code = $request->code ?? '';

        $companies = $COMPANY->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->with([
                'employees',
                'agents',
                'drivers',
                'operators'
            ])
            ->get();

        return response()->json(['result' => 'OK', 'companies' => $companies]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompany(Request $request): JsonResponse
    {
        $COMPANY = new Company();
        $id = $request->id ?? 0;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $main_phone_number = $request->main_phone_number ?? '';
        $main_fax_number = $request->main_fax_number ?? '';
        $website = $request->website ?? '';
        $ein = $request->ein ?? '';
        $zulip_name = $request->zulip_name ?? '';
        $jitsi_name = $request->jitsi_name ?? '';

        $company = $COMPANY->updateOrCreate([
            'id' => $id
        ], [
            'code' => strtoupper($code),
            'code_number' => $code_number,
            'name' => $name,
            'address1' => $address1,
            'address2' => $address2,
            'city' => $city,
            'state' => strtoupper($state),
            'zip' => $zip,
            'main_phone_number' => $main_phone_number,
            'main_fax_number' => $main_fax_number,
            'website' => $website,
            'ein' => $ein,
            'zulip_name' => $zulip_name,
            'jitsi_name' => $jitsi_name
        ]);

        $company = $COMPANY->where('id', $company->id)
            ->with([
                'employees',
                'agents',
                'drivers',
                'operators'
            ])->first();

        return response()->json(['result' => 'OK', 'company' => $company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadCompanyLogo(Request $request): JsonResponse
    {
        $COMPANY = new Company();

        $id = $_POST['id'];
        $fileData = $_FILES['logo'];
        $path = $fileData['name'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $company = $COMPANY->where('id', $id)->first();
        $cur_company_logo = $company->logo;
        $new_company_logo = uniqid() . '.' . $extension;

        if ($cur_company_logo) {
        	if (file_exists(public_path('company-logo/' . $cur_company_logo))) {
     			try {
                	unlink(public_path('company-logo/' . $cur_company_logo));
            	} catch (Throwable|Exception $e) {}
 			}
        }

        $COMPANY->where('id', $id)->update([
            'logo' => $new_company_logo
        ]);

        $company = $COMPANY->where('id', $id)
            ->with([
                'employees',
                'agents',
                'drivers',
                'operators'
            ]) ->first();

        move_uploaded_file($fileData['tmp_name'], public_path('company-logo/' . $new_company_logo));

        return response()->json(['result' => 'OK', 'company' => $company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCompanyLogo(Request $request): JsonResponse
    {
        $COMPANY = new Company();

        $id = $request->id ?? 0;

        $company = $COMPANY->where('id', $id)->first();

        if (file_exists(public_path('company-logo/' . $company->logo))) {
            try {
                unlink(public_path('company-logo/' . $company->logo));
            } catch (Throwable|Exception $e) {}
        }

        $COMPANY->where('id', $id)->update([
            'logo' => ''
        ]);

        $company = $COMPANY->where('id', $id)
            ->first();

        return response()->json(['result' => 'OK', 'company' => $company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCompany(Request $request): JsonResponse
    {
        $COMPANY = new Company();
        $id = $request->id ?? 0;

        $company = $COMPANY->where('id', $id)->delete();

        return response()->json(['result' => 'OK', 'company' => $company]);
    }
}
