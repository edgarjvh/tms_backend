<?php


namespace App\Http\Controllers;

use App\Carrier;
use App\CarrierContact;
use App\CarrierDriver;
use App\CarrierNote;
use App\Insurance;
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
            ->orderBy('code_number', 'ASC')->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }

    public function saveCarrier(Request $request){
        $id = isset($request->id) ? trim($request->id) : '';
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
        $mailing_code = isset($request->mailing_code) ? trim($request->mailing_code) : '';
        $mailing_code_number = 0;
        $mailing_old_code = isset($request->mailing_old_code) ? trim($request->mailing_old_code) : '';
        $mailing_name = isset($request->mailing_name) ? trim($request->mailing_name) : '';
        $mailing_address1 = isset($request->mailing_address1) ? trim($request->mailing_address1) : '';
        $mailing_address2 = isset($request->mailing_address2) ? trim($request->mailing_address2) : '';
        $mailing_city = isset($request->mailing_city) ? trim($request->mailing_city) : '';
        $mailing_state = isset($request->mailing_state) ? trim($request->mailing_state) : '';
        $mailing_zip = isset($request->mailing_zip) ? trim($request->mailing_zip) : '';
        $mailing_contact_name = isset($request->mailing_contact_name) ? trim($request->mailing_contact_name) : '';
        $mailing_contact_phone = isset($request->mailing_contact_phone) ? trim($request->mailing_contact_phone) : '';
        $mailing_ext = isset($request->mailing_ext) ? trim($request->mailing_ext) : '';
        $mailing_email = isset($request->mailing_email) ? trim($request->mailing_email) : '';
        $factoring_code = isset($request->factoring_code) ? trim($request->factoring_code) : '';
        $factoring_company_id = isset($request->factoring_company_id) ? $request->factoring_company_id === 0 ? null : $request->factoring_company_id : null;
        $factoring_code_number = 0;
        $factoring_old_code = isset($request->factoring_old_code) ? trim($request->factoring_old_code) : '';
        $factoring_name = isset($request->factoring_name) ? trim($request->factoring_name) : '';
        $factoring_address1 = isset($request->factoring_address1) ? trim($request->factoring_address1) : '';
        $factoring_address2 = isset($request->factoring_address2) ? trim($request->factoring_address2) : '';
        $factoring_city = isset($request->factoring_city) ? trim($request->factoring_city) : '';
        $factoring_state = isset($request->factoring_state) ? trim($request->factoring_state) : '';
        $factoring_zip = isset($request->factoring_zip) ? trim($request->factoring_zip) : '';
        $factoring_contact_name = isset($request->factoring_contact_name) ? trim($request->factoring_contact_name) : '';
        $factoring_contact_phone = isset($request->factoring_contact_phone) ? trim($request->factoring_contact_phone) : '';
        $factoring_ext = isset($request->factoring_ext) ? trim($request->factoring_ext) : '';
        $factoring_email = isset($request->factoring_email) ? trim($request->factoring_email) : '';
        
        $mailing_bill_to = '';

        $curCarrier = Carrier::where('id', $id)->first();

        if ($curCarrier){
            // si es el mismo codigo y numero
            if(($curCarrier->code . ($curCarrier->code_number === 0 ? "" : $curCarrier->code_number)) === $code){
                $code_number = $curCarrier->code_number;
            }else{
                // verificamos si hay otro registro con el mismo codigo
                $codeExist = Carrier::where('id', '<>', $id)
                    ->where('code', $curCarrier->code)
                    ->orderBy('id', 'asc')->get();

                if (count($codeExist) > 0){
                    $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
                }
            }

            if ($mailing_bill_to === ''){
                if(($curCarrier->mailing_code . ($curCarrier->mailing_code_number === 0 ? "" : $curCarrier->mailing_code_number)) === $mailing_code){
                    $mailing_code_number = $curCarrier->mailing_code_number;
                }else{
                    $mailing_codeExist = Carrier::where('id', '<>', $id)
                        ->where('mailing_code', $curCarrier->mailing_code)
                        ->orderBy('id', 'asc')->get();

                    if (count($mailing_codeExist) > 0){
                        $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                    }
                }
            }else{
                if ($curCarrier->code === $curCarrier->mailing_code){
                    $mailing_codeExist = Carrier::where('id', '<>', $id)
                        ->where('mailing_code', $curCarrier->mailing_code)
                        ->orderBy('id', 'asc')->get();

                    if (count($mailing_codeExist) > 0){
                        $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                    }else{
                        $mailing_code_number = $curCarrier->code_number + 1;
                    }
                }else{
                    if(($curCarrier->mailing_code . ($curCarrier->mailing_code_number === 0 ? "" : $curCarrier->mailing_code_number)) === $mailing_code){
                        $mailing_code_number = $curCarrier->mailing_code_number;
                    }else{
                        $mailing_codeExist = Carrier::where('id', '<>', $id)
                            ->where('mailing_code', $curCarrier->mailing_code)
                            ->orderBy('id', 'asc')->get();

                        if (count($mailing_codeExist) > 0){
                            $mailing_code_number = $mailing_codeExist[count($mailing_codeExist) - 1]->mailing_code_number + 1;
                        }
                    }
                }

                if ($curCarrier->code === $curCarrier->factoring_code){
                    $factoring_codeExist = Carrier::where('id', '<>', $id)
                        ->where('factoring_code', $curCarrier->factoring_code)
                        ->orderBy('id', 'asc')->get();

                    if (count($factoring_codeExist) > 0){
                        $factoring_code_number = $factoring_codeExist[count($factoring_codeExist) - 1]->factoring_code_number + 1;
                    }else{
                        $factoring_code_number = $curCarrier->code_number + 1;
                    }
                }else{
                    if(($curCarrier->factoring_code . ($curCarrier->factoring_code_number === 0 ? "" : $curCarrier->factoring_code_number)) === $factoring_code){
                        $factoring_code_number = $curCarrier->factoring_code_number;
                    }else{
                        $factoring_codeExist = Carrier::where('id', '<>', $id)
                            ->where('factoring_code', $curCarrier->factoring_code)
                            ->orderBy('id', 'asc')->get();

                        if (count($factoring_codeExist) > 0){
                            $factoring_code_number = $factoring_codeExist[count($factoring_codeExist) - 1]->factoring_code_number + 1;
                        }
                    }
                }
            }
        }else{

            // verificamos si existe un carrier con el codigo
            $codeExist = Carrier::where('id', '<>', $id)
                ->where('code', $code)
                ->orderBy('id', 'asc')->get();

            if (count($codeExist) > 0){
                $code_number = $codeExist[count($codeExist) - 1]->code_number + 1;
            }
        }

        $with_contact = true;

        if (trim($contact_name) === '' || (trim($contact_phone) === '' || trim($email) === '')){
            $contact_name = '';
            $contact_phone = '';
            $ext = '';
            $email = '';
            $with_contact = false;
        }

        $carrier = Carrier::updateOrCreate([
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
                'email' => $email,
                'mc_number' => $mc_number,
                'dot_number' => $dot_number,
                'scac' => $scac,
                'fid' => $fid,
                'mailing_code' => $mailing_code,
                'mailing_code_number' => $mailing_code_number,
                'mailing_name' => $mailing_name,
                'mailing_address1' => $mailing_address1,
                'mailing_address2' => $mailing_address2,
                'mailing_city' => $mailing_city,
                'mailing_state' => $mailing_state,
                'mailing_zip' => $mailing_zip,
                'mailing_contact_name' => $mailing_contact_name,
                'mailing_contact_phone' => $mailing_contact_phone,
                'mailing_ext' => $mailing_ext,
                'mailing_email' => $mailing_email,
                'factoring_company_id' => $factoring_company_id
            ]);

        if ($with_contact){
            $contacts = CarrierContact::where('carrier_id', $carrier->id)->get();

            $contact_name_splitted = explode(" ", $contact_name);
            $contact_first = $contact_name_splitted[0];
            $contact_last = '';

            if (count($contact_name_splitted) > 0){
                for ($i = 1; $i < count($contact_name_splitted); $i++){
                    $contact_last .= $contact_name_splitted[$i] . " ";
                }
            }

            $contact_last = trim($contact_last);

            if (count($contacts) === 0){
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

        return response()->json(['result' => 'OK', 'carrier' => $carrier]);
    }

    public function getCarrierPayload(Request $request){
        $carrier_id = $request->carrier_id;

        $contacts = CarrierContact::where('carrier_id', $carrier_id)->orderBy('last_name', 'asc')->get();
        $notes = CarrierNote::where('carrier_id', $carrier_id)->get();
        $drivers = CarrierDriver::where('carrier_id', $carrier_id)->orderBy('first_name', 'ASC')->get();
        $insurances = Insurance::where('carrier_id', $carrier_id)->with('insuranceType')->has('insuranceType')->get();

        return response()->json(['result' => 'OK', 'contacts' => $contacts, 'notes' => $notes, 'drivers' => $drivers, 'insurances' => $insurances]);
    }
}
