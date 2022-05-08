<?php


namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierContact;
use App\Models\CarrierDriver;
use App\Models\CarrierNote;
use App\Models\Equipment;
use App\Models\Insurance;
use App\Models\InsuranceType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarriersController extends Controller
{
    /**
     * @param Request $json
     * @return JsonResponse
     */
    public function getCarrierById(Request $request){
        $CARRIER = Carrier::query();
        $id = $request->id ?? 0;

        $carrier = $CARRIER->where('id', $id)
            ->with([
            'contacts',
            'drivers',
            'notes',
            'insurances',
            'factoring_company',
            'mailing_address',
            'documents',
            'equipments_information'
        ])->first();

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $json
     * @return JsonResponse
     */
    public function carriers(Request $json): JsonResponse
    {
        $CARRIER = new Carrier();

        $code = $json->code ?? '';
        $name = $json->name ?? '';
        $address1 = $json->address1 ?? '';
        $address2 = $json->address2 ?? '';
        $city = $json->city ?? '';
        $state = $json->state ?? '';
        $zip = $json->zip ?? '';
        $email = $json->email ?? '';
        $with_relations = $request->with_relations ?? 1;

        if ($with_relations === 1){
            $carriers = $CARRIER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("name like '%$name%'")
                ->whereRaw("address1 like '%$address1%'")
                ->whereRaw("address2 like '%$address2%'")
                ->whereRaw("city like '%$city%'")
                ->whereRaw("state like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("email like '%$email%'")
                ->with('factoring_company')
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts',
                    'drivers',
                    'notes',
                    'insurances',
                    'factoring_company',
                    'mailing_address',
                    'documents',
                    'equipments_information'
                ])
                ->get();
        }else{
            $carriers = $CARRIER->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '%$code%'")
                ->whereRaw("name like '%$name%'")
                ->whereRaw("address1 like '%$address1%'")
                ->whereRaw("address2 like '%$address2%'")
                ->whereRaw("city like '%$city%'")
                ->whereRaw("state like '%$state%'")
                ->whereRaw("zip like '%$zip%'")
                ->whereRaw("email like '%$email%'")
                ->with('factoring_company')
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts'
                ])
                ->get();
        }

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function carrierSearch(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();

        $name = $request->search[0]['data'] ?? '';
        $city = $request->search[1]['data'] ?? '';
        $state = $request->search[2]['data'] ?? '';
        $zip = $request->search[3]['data'] ?? '';
        $contact_name = $request->search[4]['data'] ?? '';
        $contact_phone = $request->search[5]['data'] ?? '';
        $email = $request->search[6]['data'] ?? '';

        $carriers = $CARRIER->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_name) like '$contact_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_address',
                'documents',
                'equipments_information'
            ])
            ->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrier(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new CarrierContact();

        $id = $request->id ?? '';
        $factoring_company_id = $request->factoring_company_id ?? null;
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
        $mc_number = $request->mc_number ?? '';
        $dot_number = $request->dot_number ?? '';
        $scac = $request->scac ?? '';
        $fid = $request->fid ?? '';
        $do_not_use = $request->do_not_use ?? 0;
        $rating = $request->rating ?? 0;

        $curCarrier = $CARRIER->where('id', $id)->first();

        if ($curCarrier) {
            if ($curCarrier->code !== $code) {
                $codeExist = $CARRIER->where('id', '<>', $id)
                    ->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $CARRIER->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else {
            $codeExist = $CARRIER->where('code', $code)->get();

            if (count($codeExist) > 0) {
                $max_code_number = $CARRIER->where('code', $code)->max('code_number');
                $code_number = $max_code_number + 1;
            } else {
                $code_number = 0;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $carrier = $CARRIER->updateOrCreate([
            'id' => $id
        ],
            [
                'factoring_company_id' => empty($factoring_company_id) ? null : $factoring_company_id,
                'code' => strtoupper($code),
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $ext,
                'email' => strtolower($email),
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => strtoupper($scac),
                'fid' => $fid,
                'do_not_use' => $do_not_use,
                'rating' => $rating,
            ]);

        if ($with_contact) {
            $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier->id)->get();

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
                $contact = new CarrierContact();
                $contact->carrier_id = $carrier->id;
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

                $CARRIER->where('id', $carrier->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CARRIER_CONTACT->where('id', $contact->id)->update([
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

        $carrier = $CARRIER->where('id', $carrier->id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_address',
                'documents',
                'equipments_information'
            ])->first();


        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCarrierImport(Request $request){
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new CarrierContact();

        $id = 0;
        $code = $request->code ?? '';
        $code_number = $request->codeNumber ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_name = $request->contact ?? '';
        $contact_phone = $request->phone ?? '';
        $contact_phone_ext = $request->ext ?? '';
        $email = $request->email ?? '';
        $mc_number = $request->mcNumber ?? '';
        $dot_number = $request->dotNumber ?? '';
        $scac = $request->scac ?? '';
        $fid = $request->fid ?? '';
        $do_not_use = $request->doNotUse ?? 'N';

//        $curCarrier = $CARRIER->where('id', $id)->first();
//
//        if ($curCarrier) {
//            if ($curCarrier->code !== $code) {
//                $codeExist = $CARRIER->where('id', '<>', $id)
//                    ->where('code', $code)->get();
//
//                if (count($codeExist) > 0) {
//                    $max_code_number = $CARRIER->where('code', $code)->max('code_number');
//                    $code_number = $max_code_number + 1;
//                } else {
//                    $code_number = 0;
//                }
//            }
//        } else {
//            $codeExist = $CARRIER->where('code', $code)->get();
//
//            if (count($codeExist) > 0) {
//                $max_code_number = $CARRIER->where('code', $code)->max('code_number');
//                $code_number = $max_code_number + 1;
//            } else {
//                $code_number = 0;
//            }
//        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $carrier = $CARRIER->updateOrCreate([
            'id' => $id
        ],
            [
                'code' => strtoupper($code),
                'code_number' => $code_number,
                'name' => $name,
                'address1' => $address1,
                'address2' => $address2,
                'city' => $city,
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => $contact_name,
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email),
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => strtoupper($scac),
                'fid' => $fid,
                'do_not_use' => strtoupper($do_not_use) === 'Y' ? 1 : 0,
            ]);

        if ($with_contact) {
            $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier->id)->get();

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
                $contact = new CarrierContact();
                $contact->carrier_id = $carrier->id;
                $contact->first_name = trim($contact_first);
                $contact->last_name = trim($contact_last);
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $contact_phone_ext;
                $contact->email_work = $email;
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = $city;
                $contact->state = $state;
                $contact->zip_code = $zip;
                $contact->is_primary = 1;
                $contact->save();

                $CARRIER->where('id', $carrier->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first && $contact->last_name === $contact_last) {

                    $CARRIER_CONTACT->where('id', $contact->id)->update([
                        'phone_work' => ($contact->primary_phone === 'work') ? $contact_phone : $contact->phone_work,
                        'phone_work_fax' => ($contact->primary_phone === 'fax') ? $contact_phone : $contact->phone_work_fax,
                        'phone_mobile' => ($contact->primary_phone === 'mobile') ? $contact_phone : $contact->phone_mobile,
                        'phone_direct' => ($contact->primary_phone === 'direct') ? $contact_phone : $contact->phone_direct,
                        'phone_other' => ($contact->primary_phone === 'other') ? $contact_phone : $contact->phone_other,
                        'phone_ext' => $contact_phone_ext,
                        'email_work' => ($contact->primary_email === 'work') ? $email : $contact->email_work,
                        'email_personal' => ($contact->primary_email === 'personal') ? $email : $contact->email_personal,
                        'email_other' => ($contact->primary_email === 'other') ? $email : $contact->email_other
                    ]);
                }
            }
        }

        $newCarrier = $CARRIER->where('id', $carrier->id)->first();

        return response()->json(['result' => 'OK', 'carrier' => $newCarrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierPayload(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new CarrierContact();
        $CARRIER_NOTE = new CarrierNote();
        $CARRIER_DRIVER = new CarrierDriver();
        $INSURANCE = new Insurance();
        $carrier_id = $request->carrier_id;

        $contacts = $CARRIER_CONTACT->where('carrier_id', $carrier_id)->orderBy('last_name')->get();
        $notes = $CARRIER_NOTE->where('carrier_id', $carrier_id)->get();
        $drivers = $CARRIER_DRIVER->where('carrier_id', $carrier_id)->orderBy('first_name')->get();
        $insurances = $INSURANCE->where('carrier_id', $carrier_id)->with('insurance_type')->has('insurance_type')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'notes' => $notes, 'drivers' => $drivers, 'insurances' => $insurances]);
    }

    /**
     * @throws Exception
     */
    public function getFullCarriers(): JsonResponse
    {
        $CARRIER = new Carrier();

        $carriers = $CARRIER->with([
            'contacts',
            'drivers',
            'notes',
            'insurances',
            'factoring_company',
            'mailing_address',
            'documents',
            'equipments_information'
        ])->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    /**
     * @throws Exception
     */
    public function getCarrierPopupItems(): JsonResponse
    {
        $EQUIPMENT = new Equipment();
        $INSURANCE_TYPE = new InsuranceType();

        $equipments = $EQUIPMENT->orderBy('name')->get();
        $insurance_types = $INSURANCE_TYPE->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments, 'insurance_types' => $insurance_types]);
    }
}
