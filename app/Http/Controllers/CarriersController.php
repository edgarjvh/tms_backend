<?php


namespace App\Http\Controllers;

use App\Carrier;
use App\CarrierContact;
use App\CarrierDriver;
use App\CarrierNote;
use App\Equipment;
use App\Insurance;
use App\InsuranceType;
use Illuminate\Http\Request;

class CarriersController extends Controller
{
    public function carriers(Request $json)
    {
        $code = isset($json->code) ? trim($json->code) : '';
        $name = isset($json->name) ? trim($json->name) : '';
        $address1 = isset($json->address1) ? trim($json->address1) : '';
        $address2 = isset($json->address2) ? trim($json->address2) : '';
        $city = isset($json->city) ? trim($json->city) : '';
        $state = isset($json->state) ? trim($json->state) : '';
        $zip = isset($json->zip) ? trim($json->zip) : '';
        $email = isset($json->email) ? trim($json->email) : '';

        $carriers = Carrier::whereRaw("1 = 1")
            ->whereRaw("code like '%$code%'")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("address1 like '%$address1%'")
            ->whereRaw("address2 like '%$address2%'")
            ->whereRaw("city like '%$city%'")
            ->whereRaw("state like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("email like '%$email%'")
            ->with('factoring_company')
            ->orderBy('code', 'ASC')
            ->orderBy('code_number', 'ASC')
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents'])
            ->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    public function carrierSearch(Request $request)
    {
        // error_log($request->search[0]['data']);
        $name = isset($request->search[0]['data']) ? trim($request->search[0]['data']) : '';
        $city = isset($request->search[1]['data']) ? trim($request->search[1]['data']) : '';
        $state = isset($request->search[2]['data']) ? trim($request->search[2]['data']) : '';
        $zip = isset($request->search[3]['data']) ? trim($request->search[3]['data']) : '';
        $contact_name = isset($request->search[4]['data']) ? trim($request->search[4]['data']) : '';
        $contact_phone = isset($request->search[5]['data']) ? trim($request->search[5]['data']) : '';
        $email = isset($request->search[6]['data']) ? trim($request->search[6]['data']) : '';

        $carriers = Carrier::whereRaw("1 = 1")
            // ->whereRaw("code like '%$code%'")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->whereRaw("LOWER(city) like '%$city%'")
            ->whereRaw("LOWER(state) like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("LOWER(contact_name) like '%$contact_name%'")
            ->whereRaw("contact_phone like '%$contact_phone%'")
            ->whereRaw("LOWER(email) like '%$email%'")
            ->orderBy('code', 'ASC')
            ->orderBy('code_number', 'ASC')
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents'])
            ->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    public function saveCarrier(Request $request)
    {
        $id = isset($request->id) ? trim($request->id) : '';
        $factoring_company_id = isset($request->factoring_company_id) ? $request->factoring_company_id : 0;
        $code = isset($request->code) ? trim($request->code) : '';
        $code_number = 0;
        $old_code = isset($request->old_code) ? trim($request->old_code) : '';
        $name = isset($request->name) ? trim($request->name) : '';
        $address1 = isset($request->address1) ? trim($request->address1) : '';
        $address2 = isset($request->address2) ? trim($request->address2) : '';
        $city = isset($request->city) ? trim($request->city) : '';
        $state = isset($request->state) ? trim($request->state) : '';
        $zip = isset($request->zip) ? trim($request->zip) : '';
        $contact_name = isset($request->contact_name) ? trim($request->contact_name) : '';
        $contact_phone = isset($request->contact_phone) ? trim($request->contact_phone) : '';
        $ext = isset($request->ext) ? trim($request->ext) : '';
        $email = isset($request->email) ? trim($request->email) : '';
        $mc_number = isset($request->mc_number) ? trim($request->mc_number) : '';
        $dot_number = isset($request->dot_number) ? trim($request->dot_number) : '';
        $scac = isset($request->scac) ? trim($request->scac) : '';
        $fid = isset($request->fid) ? trim($request->fid) : '';
        $do_not_use = isset($request->do_not_use) ? trim($request->do_not_use) : 0;

        $mailing_code = isset($request->mailing_code) ? trim($request->mailing_code) : '';


        $curCarrier = Carrier::where('id', $id)->first();

        if ($curCarrier) {
            // si es el mismo codigo y numero
            if (($curCarrier->code . ($curCarrier->code_number === 0 ? "" : $curCarrier->code_number)) === $code) {
                $code_number = $curCarrier->code_number;
            } else {
                // verificamos si hay otro registro con el mismo codigo
                $codeExist = Carrier::where('id', '<>', $id)
                    ->where('code', $curCarrier->code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0) {
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }

            if ($curCarrier->code === $curCarrier->mailing_code) {
                $mailing_codeExist = Carrier::where('id', '<>', $id)
                    ->where('mailing_code', $curCarrier->mailing_code)
                    ->orderBy('id', 'asc')->get();

                if (count($mailing_codeExist) > 0) {
                    $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                } else {
                    $mailing_code_number = $curCarrier->code_number + 1;
                }
            } else {
                if (($curCarrier->mailing_code . ($curCarrier->mailing_code_number === 0 ? "" : $curCarrier->mailing_code_number)) === $mailing_code) {
                    $mailing_code_number = $curCarrier->mailing_code_number;
                } else {
                    $mailing_codeExist = Carrier::where('id', '<>', $id)
                        ->where('mailing_code', $curCarrier->mailing_code)
                        ->orderBy('id', 'asc')->get();

                    if (count($mailing_codeExist) > 0) {
                        $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                    }
                }
            }
        } else {

            // verificamos si existe un carrier con el codigo
            $codeExist = Carrier::where('id', '<>', $id)
                ->where('code', $code)
                ->orderBy('id', 'asc')->get();

            if (count($codeExist) > 0) {
                $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || trim($contact_phone) === '' || trim($email) === '') {
//            $contact_name = '';
//            $contact_phone = '';
//            $contact_phone_ext = '';
//            $email = '';
            $with_contact = false;
        }

        $carrier = Carrier::updateOrCreate([
            'id' => $id
        ],
            [
                'factoring_company_id' => $factoring_company_id,
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
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => $scac,
                'fid' => $fid,
                'do_not_use' => $do_not_use
            ]);

        if ($with_contact) {
            $contacts = CarrierContact::where('carrier_id', $carrier->id)->get();

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

                Carrier::where('id', $carrier->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            }
        }

        $carrier = Carrier::where('id', $carrier->id)
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents'])->first();


        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    public function getCarrierPayload(Request $request)
    {
        $carrier_id = $request->carrier_id;

        $contacts = CarrierContact::where('carrier_id', $carrier_id)->orderBy('last_name', 'asc')->get();
        $notes = CarrierNote::where('carrier_id', $carrier_id)->get();
        $drivers = CarrierDriver::where('carrier_id', $carrier_id)->orderBy('first_name', 'ASC')->get();
        $insurances = Insurance::where('carrier_id', $carrier_id)->with('insurance_type')->has('insurance_type')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'notes' => $notes, 'drivers' => $drivers, 'insurances' => $insurances]);
    }

    public function getFullCarriers()
    {
        $carriers = Carrier::with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents'])->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    public function getCarrierPopupItems(){
        $equipments = Equipment::orderBy('name')->get();
        $insurance_types = InsuranceType::orderBy('name')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments, 'insurance_types' => $insurance_types]);
    }
}
