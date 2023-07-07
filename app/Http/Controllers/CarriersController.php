<?php


namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierContact;
use App\Models\Contact;
use App\Models\CarrierDriver;
use App\Models\CarrierNote;
use App\Models\Equipment;
use App\Models\Insurance;
use App\Models\InsuranceType;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarriersController extends Controller
{
    /**
     * @param Request $json
     * @return JsonResponse
     */
    public function getCarrierById(Request $request)
    {
        $CARRIER = Carrier::query();
        $id = $request->id ?? 0;

        $carrier = $CARRIER->where('id', $id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
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

        if ($with_relations === 1) {
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
                    'mailing_same',
                    'mailing_address',
                    'mailing_carrier',
                    'documents',
                    'equipments_information'
                ])
                ->get();
        } else {
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

        $code = $request->search[0]['data'] ?? '';
        $name = $request->search[1]['data'] ?? '';
        $city = $request->search[2]['data'] ?? '';
        $state = $request->search[3]['data'] ?? '';
        $zip = $request->search[4]['data'] ?? '';
        $contact_name = $request->search[5]['data'] ?? '';
        $contact_phone = $request->search[6]['data'] ?? '';
        $email = $request->search[7]['data'] ?? '';
        $mc_number = $request->search[8]['data'] ?? '';
        $dot_number = $request->search[9]['data'] ?? '';
        $scac = $request->search[10]['data'] ?? '';
        $fid = $request->search[11]['data'] ?? '';

        $carriers = $CARRIER->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_name) like '$contact_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'")
            ->whereRaw("LOWER(mc_number) like '$mc_number%'")
            ->whereRaw("LOWER(dot_number) like '$dot_number%'")
            ->whereRaw("LOWER(scac) like '$scac%'")
            ->whereRaw("LOWER(fid) like '$fid%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->without([
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
    public function getCarrierOrders(Request $request)
    {
        $id = $request->id ?? 0;

        $ORDER = Order::query();

        $ORDER->whereRaw("1 = 1");
        $ORDER->whereHas('carrier', function ($query1) use ($id) {
            $query1->where('id', $id);
        });

        $ORDER->select([
            'id',
            'order_number'
        ]);

        $ORDER->with([
            'carrier',
            'pickups',
            'deliveries',
            'routing'
        ]);


        $ORDER->orderBy('order_number', 'desc')->limit(20);

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrier(Request $request): JsonResponse
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new Contact();

        $id = $request->id ?? '';
        $factoring_company_id = $request->factoring_company_id ?? null;
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $full_code = $code . $code_number;
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

        $mailing_address_id = $request->mailing_address_id ?? null;
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;
        $mailing_carrier_id = $request->mailing_carrier_id ?? null;
        $mailing_carrier_contact_id = $request->mailing_carrier_contact_id ?? null;
        $mailing_carrier_contact_primary_phone = $request->mailing_carrier_contact_primary_phone ?? 'work';
        $mailing_carrier_contact_primary_email = $request->mailing_carrier_contact_primary_email ?? 'work';

        $codeExist = [];

        $curCarrier = $CARRIER
            ->where('id', $id)
            ->whereRaw("CONCAT(code,code_number) = '$full_code'")
            ->first();

        if ($curCarrier) {
            if ($curCarrier->code !== $code) {
                $codeExist = $CARRIER
                    ->where('id', '<>', $id)
                    ->where('code', $code)
                    ->orderBy('code_number', 'desc')
                    ->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[0]->code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else {
            $codeExist = $CARRIER
                ->where('code', $code)
                ->orderBy('code_number', 'desc')
                ->get();

            if (count($codeExist) > 0) {
                $code_number = $codeExist[0]->code_number + 1;
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
                'name' => ucwords($name),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_name' => ucwords($contact_name),
                'contact_phone' => $contact_phone,
                'ext' => $ext,
                'email' => strtolower($email),
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => strtoupper($scac),
                'fid' => $fid,
                'do_not_use' => $do_not_use,
                'rating' => $rating,
                'mailing_address_id' => $mailing_address_id,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
                'mailing_carrier_id' => $mailing_carrier_id,
                'mailing_carrier_contact_id' => $mailing_carrier_contact_id,
                'mailing_carrier_contact_primary_phone' => $mailing_carrier_contact_primary_phone,
                'mailing_carrier_contact_primary_email' => $mailing_carrier_contact_primary_email
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
                $contact = new Contact();
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

        $CARRIER_CONTACT->where('carrier_id', $carrier->id)->update([
            'address1' => $carrier->address1,
            'address2' => $carrier->address2,
            'city' => $carrier->city,
            'state' => $carrier->state,
            'zip_code' => $carrier->zip,
        ]);

        $carrier = $CARRIER->where('id', $carrier->id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])->first();


        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierAchWiringInfo(Request $request): JsonResponse
    {
        $carrier_id = $request->carrier_id ?? 0;
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

        $CARRIER = new Carrier();

        $CARRIER->updateOrCreate([
            'id' => $carrier_id
        ], [
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

        $carrier = $CARRIER->where('id', $carrier_id)
            ->with([
                'contacts',
                'drivers',
                'notes',
                'insurances',
                'factoring_company',
                'mailing_same',
                'mailing_address',
                'mailing_carrier',
                'documents',
                'equipments_information'
            ])->first();

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCarrierImport(Request $request)
    {
        $CARRIER = new Carrier();
        $CARRIER_CONTACT = new Contact();

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
                $contact = new Contact();
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
    public function submitCarrierImport2(Request $request)
    {
        $list = $request->list ?? [];

        if (count($list) > 0) {
            for ($i = 0; $i < count($list); $i++) {
                $item = $list[$i];

                $code = $item['code'] ?? null;
                $code_number = $item['codeNumber'] ?? 0;
                $name = $item['name'] ?? '';
                $address1 = $item['address1'] ?? '';
                $address2 = $item['address2'] ?? '';
                $city = $item['city'] ?? '';
                $state = $item['state'] ?? '';
                $zip = $item['zip'] ?? '';
                $contact_name = $item['contact'] ?? '';
                $contact_first_name = $item['contactFirstName'] ?? '';
                $contact_last_name = $item['contactLastName'] ?? '';
                $contact_phone = $item['phone'] ?? '';
                $ext = $item['ext'] ?? '';
                $email = $item['email'] ?? '';
                $mc_number = $item['mcNumber'] ?? '';
                $dot_number = $item['dotNumber'] ?? '';
                $scac = $item['scac'] ?? '';
                $fid = $item['fid'] ?? '';
                $do_not_use = $item['doNotUse'] ?? 0;

                $carrier_id = 0;

                $zip = str_replace(" ", "", $zip);

                if (preg_match('/[a-z]/i', $zip)) {
                    $zip = str_replace("-", "", $zip);
                    $len = strlen($zip);
                    $rem = $len - 6;

                    if ($rem > 0) {
                        $zip = substr_replace($zip, "", 0, $rem);
                    }

                    $zip = substr_replace($zip, " ", 3, 0);
                } else if (preg_match('/[0-9]/', $zip)) {
                    $zip = explode("-", $zip)[0];

                    $len = strlen($zip);

                    if ($len < 5) {
                        $zip = str_pad($zip, 5, "0", STR_PAD_LEFT);
                    }
                }

                try {
                    $saved_carrier = Carrier::updateOrCreate([
                        'id' => 0
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
                            'ext' => $ext,
                            'email' => strtolower($email),
                            'mc_number' => $mc_number,
                            'dot_number' => $dot_number,
                            'scac' => strtoupper($scac),
                            'fid' => $fid,
                            'do_not_use' => $do_not_use
                        ]);

                    $carrier_id = $saved_carrier->id;
                } catch (Throwable|Exception $e) {
                    $carrier_id = 0;
                }

                if ($carrier_id > 0) {
                    try {
                        $saved_contact = CarrierContact::updateOrCreate([
                            'id' => 0
                        ], [
                            'carrier_id' => $carrier_id,
                            'first_name' => $contact_first_name,
                            'last_name' => $contact_last_name,
                            'phone_work' => $contact_phone,
                            'phone_ext' => $ext,
                            'email_work' => $email,
                            'address1' => $address1,
                            'address2' => $address2,
                            'city' => $city,
                            'state' => $state,
                            'zip_code' => $zip,
                            'is_primary' => 1
                        ]);

                        Carrier::where('id', $carrier_id)->update([
                            'primary_contact_id' => $saved_contact->id
                        ]);
                    } catch (Throwable|Exception $e) {

                    }
                }
            }

            return response()->json(['result' => 'OK']);
        } else {
            return response()->json(['result' => 'NO LIST']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierPayload(Request $request): JsonResponse
    {
        $CARRIER_CONTACT = new Contact();
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
            'mailing_same',
            'mailing_address',
            'mailing_carrier',
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

    /**
     * @throws Exception
     */
    public function getMcNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('mc_number');
        $CARRIER->where('mc_number', '<>', '');
        $CARRIER->orderBy('mc_number');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'mc_number']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getDotNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('dot_number');
        $CARRIER->where('dot_number', '<>', '');
        $CARRIER->orderBy('dot_number');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'dot_number']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getScacNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('scac');
        $CARRIER->where('scac', '<>', '');
        $CARRIER->orderBy('scac');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'scac']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }

    /**
     * @throws Exception
     */
    public function getFidNumbers(): JsonResponse
    {
        $CARRIER = Carrier::query();

        $CARRIER->whereNotNull('fid');
        $CARRIER->where('fid', '<>', '');
        $CARRIER->orderBy('fid');
        $CARRIER->select(['id', 'code', 'code_number', 'name', 'fid']);
        $mc_numbers = $CARRIER->get();

        return response()->json(['result' => 'OK', 'mc_numbers' => $mc_numbers]);
    }
}
