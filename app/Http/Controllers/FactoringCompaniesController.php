<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\FactoringCompany;
use App\Models\FactoringCompanyContact;
use App\Models\FactoringCompanyMailingAddress;
use App\Models\FactoringCompanyNote;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FactoringCompaniesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFactoringCompanyById(Request $request){
        $FACTORING_COMPANY = FactoringCompany::query();
        $id = $request->id ?? 0;

        $factoring_company = $FACTORING_COMPANY->where('id', $id)
            ->with(['documents', 'contacts', 'invoices', 'mailing_address', 'notes'])->first();

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function factoringCompanies(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY = new FactoringCompany();

        $code = $request->code ?? '';
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $email = $request->email ?? '';

        $factoring_companies = $FACTORING_COMPANY->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("address1 like '%$address1%'")
            ->whereRaw("address2 like '%$address2%'")
            ->whereRaw("city like '%$city%'")
            ->whereRaw("state like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("email like '%$email%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes'])->get();

        return response()->json(['result' => 'OK', 'factoring_companies' => $factoring_companies]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function factoringCompanySearch(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY = new FactoringCompany();

        $name = $request->search[0]['data'] ?? '';
        $address1 = $request->search[1]['data'] ?? '';
        $address2 = $request->search[2]['data'] ?? '';
        $city = $request->search[3]['data'] ?? '';
        $state = $request->search[4]['data'] ?? '';
        $zip = $request->search[5]['data'] ?? '';
        $email = $request->search[6]['data'] ?? '';

        $factoring_companies = $FACTORING_COMPANY->whereRaw("1 = 1")
            // ->whereRaw("code like '%$code%'")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->whereRaw("LOWER(address1) like '%$address1%'")
            ->whereRaw("LOWER(address2) like '%$address2%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("LOWER(email) like '%$email%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes'])->get();

        return response()->json(['result' => 'OK', 'factoring_companies' => $factoring_companies]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFactoringCompany(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY = new FactoringCompany();

        $id = $request->id ?? '';

        $factoring_company = $FACTORING_COMPANY->where('id', $id)->delete();

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFactoringCompany(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY = new FactoringCompany();
        $FACTORING_COMPANY_CONTACT = new FactoringCompanyContact();
        $CARRIER = new Carrier();

        $id = isset($request->id) ? (int)$request->id : 0;
        $carrier_id = $request->carrier_id ?? null;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $ext = $request->ext ?? '';
        $email = $request->email ?? '';

        $curFactoringCompany = $FACTORING_COMPANY->where('id', $id)->first();

        if ($curFactoringCompany) {
            // si no es el mismo codigo
            if ($curFactoringCompany->code !== $code) {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $codeExist = $FACTORING_COMPANY->where('id', '<>', $id)
                    ->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $FACTORING_COMPANY->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else {
            // verificamos si hay otro registro con el nuevo codigo
            // para asignarle el code_number
            $codeExist = $FACTORING_COMPANY->where('code', $code)->get();

            if (count($codeExist) > 0) {
                $max_code_number = $FACTORING_COMPANY->where('code', $code)->max('code_number');
                $code_number = $max_code_number + 1;
            } else {
                $code_number = 0;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $factoring_company = $FACTORING_COMPANY->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => $code,
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $ext,
                'email' => $email
            ]);

        if ($with_contact) {
            $contacts = $FACTORING_COMPANY_CONTACT->where('factoring_company_id', $factoring_company->id)->get();

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
                $contact = new FactoringCompanyContact();
                $contact->factoring_company_id = $factoring_company->id;
                $contact->first_name = $contact_first;
                $contact->last_name = $contact_last;
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $ext;
                $contact->email_work = $email;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = $city;
                $contact->state = $state;
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->save();

                $FACTORING_COMPANY->where('id', $factoring_company->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $FACTORING_COMPANY_CONTACT->where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $ext,
                        'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        // si se recibe un carrier se actualiza factoring company foreing key
        if ($carrier_id) {
            $CARRIER->where('id', $carrier_id)->update([
                'factoring_company_id' => $factoring_company->id
            ]);
        }

        $new_factoring_company = $FACTORING_COMPANY->where('id', $factoring_company->id)
            ->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes'])->first();

        return response()->json(['result' => 'OK', 'factoring_company' => $new_factoring_company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFactoringCompanyMailingAddress(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY_MAILING_ADDRESS = new FactoringCompanyMailingAddress();

        $factoring_company_id = $request->factoring_company_id ?? 0;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
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

        $curMailingAddress = $FACTORING_COMPANY_MAILING_ADDRESS->where('factoring_company_id', $factoring_company_id)->first();

        if ($factoring_company_id > 0){
            if ($curMailingAddress) {
                // si no es el mismo codigo
                if ($curMailingAddress->code !== $code) {
                    // verificamos si hay otro registro con el nuevo codigo
                    // para asignarle el code_number
                    $codeExist = $FACTORING_COMPANY_MAILING_ADDRESS->where('factoring_company_id', '<>', $factoring_company_id)
                        ->where('code', $code)->get();

                    if (count($codeExist) > 0) {
                        $max_code_number = $FACTORING_COMPANY_MAILING_ADDRESS->where('code', $code)->max('code_number');
                        $code_number = $max_code_number + 1;
                    }
                }
            } else {
                // verificamos si hay otro registro con el nuevo codigo
                // para asignarle el code_number
                $codeExist = $FACTORING_COMPANY_MAILING_ADDRESS->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $FACTORING_COMPANY_MAILING_ADDRESS->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                }
            }

            $FACTORING_COMPANY_MAILING_ADDRESS->updateOrCreate([
                'factoring_company_id' => $factoring_company_id
            ],
                [
                    'code' => $code,
                    'code_number' => $code_number,
                    'name' => $name,
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'ext' => $ext,
                    'email' => $email,
                    'mailing_contact_id' => $mailing_contact_id,
                    'mailing_contact_primary_phone' => $mailing_contact_primary_phone,
                    'mailing_contact_primary_email' => $mailing_contact_primary_email,
                ]);

            $newMailingAddress = $FACTORING_COMPANY_MAILING_ADDRESS->where('factoring_company_id', $factoring_company_id)->with(['mailing_contact'])->first();

            return response()->json(['result' => 'OK', 'mailing_address' => $newMailingAddress]);
        }else{
            return response()->json(['result' => 'NO FACTORING COMPANY']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFactoringCompanyMailingAddress(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY_MAILING_ADDRESS = new FactoringCompanyMailingAddress();

        $factoring_company_id = $request->factoring_company_id ?? 0;

        $mailing_address = $FACTORING_COMPANY_MAILING_ADDRESS->where('factoring_company_id', $factoring_company_id)->delete();

        return response()->json(['result' => 'OK', 'mailing_address' => $mailing_address]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFactoringCompanyNotes(Request $request) : JsonResponse
    {
        $FACTORING_COMPANY_NOTE = new FactoringCompanyNote();

        $factoring_company_id = $request->factoring_company_id ?? 0;
        $user = $request->user ?? '';
        $date_time = $request->date_time ?? '';
        $note = $request->text ?? '';

        $factoring_company_note = $FACTORING_COMPANY_NOTE->updateOrCreate([
            'id' => 0
        ], [
            'factoring_company_id' => $factoring_company_id,
            'user' => $user,
            'date_time' => $date_time,
            'text' => $note
        ]);

        $factoring_company_notes = $FACTORING_COMPANY_NOTE->where('factoring_company_id', $factoring_company_id)->get();

        return response()->json(['result' => 'OK', 'factoring_company_note' => $factoring_company_note, 'data' => $factoring_company_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAchWiringInfo(Request $request) : JsonResponse {
        $factoring_company_id = $request->factoring_company_id ?? 0;
        $ach_banking_info = $request->ach_banking_info ?? '';
        $ach_account_info = $request->ach_account_info ?? '';
        $ach_aba_routing = $request->ach_aba_routing ?? '';
        $ach_remittence_email = $request->ach_remittence_email ?? '';
        $ach_type = $request->ach_type ?? 'checking';
        $wiring_banking_info = $request->wiring_banking_info ?? '';
        $wiring_account_info = $request->wiring_account_info ?? '';
        $wiring_aba_routing = $request->wiring_aba_routing ?? '';
        $wiring_remittence_email = $request->wiring_remittence_email ?? '';
        $wiring_type = $request->wiring_type ?? 'checking';

        $FACTORING_COMPANY = new FactoringCompany();

        $FACTORING_COMPANY->updateOrCreate([
            'id'=>$factoring_company_id
        ],[
            'ach_banking_info' => $ach_banking_info,
            'ach_account_info' => $ach_account_info,
            'ach_aba_routing' => $ach_aba_routing,
            'ach_remittence_email' => strtolower($ach_remittence_email),
            'ach_type' => strtolower($ach_type),
            'wiring_banking_info' => $wiring_banking_info,
            'wiring_account_info' => $wiring_account_info,
            'wiring_aba_routing' => $wiring_aba_routing,
            'wiring_remittence_email' => strtolower($wiring_remittence_email),
            'wiring_type' => strtolower($wiring_type),
        ]);

        $factoring_company = $FACTORING_COMPANY->where('id', $factoring_company_id)
            ->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes'])->first();

        return response()->json(['result' => 'OK', 'factoring_company' => $factoring_company]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getFactoringCompanyOutstandingInvoices(Request $request) : JsonResponse {
        $factoring_company_id = $request->factoring_company_id ?? 0;

        $ORDER = Order::query();

        $ORDER->whereHas('carrier', function ($query1) use ($factoring_company_id){
            $query1->whereHas('factoring_company', function ($query2) use ($factoring_company_id){
                $query2->where('id', $factoring_company_id);
            });
        });

        $ORDER->where('invoice_date_paid', '<>', '');
        $ORDER->where('carrier_check_number', '<>', '');

        $orders = $ORDER->orderBy('order_number', 'desc')->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }
}
