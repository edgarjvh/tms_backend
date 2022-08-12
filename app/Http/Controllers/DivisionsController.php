<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Division;
use App\Models\DivisionContact;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionById(Request $request): JsonResponse
    {
        $DIVISION = Division::query();
        $id = $request->id ?? 0;

        $division = $DIVISION->where('id', $id)
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'division' => $division]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisions(Request $request): JsonResponse
    {
        $DIVISION = new Division();
        $code = $request->code ?? '';
        $name = $request->name ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_first_name = $request->contact_first_name ?? '';
        $contact_last_name = $request->contact_last_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $email = $request->email ?? '';
        $with_relations = $request->with_relations ?? 1;

        if ($with_relations === 1) {
            $divisions = $DIVISION->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
                ->whereRaw("LOWER(name) like '$name%'")
                ->whereRaw("LOWER(city) like '$city%'")
                ->whereRaw("LOWER(state) like '$state%'")
                ->whereRaw("zip like '$zip%'")
                ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
                ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
                ->whereRaw("contact_phone like '$contact_phone%'")
                ->whereRaw("LOWER(email) like '$email%'")
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts',
                    'documents',
                    'hours',
                    'notes',
                    'mailing_address'
                ])
                ->get();
        } else {
            $divisions = $DIVISION->whereRaw("1 = 1")
                ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
                ->whereRaw("LOWER(name) like '$name%'")
                ->whereRaw("LOWER(city) like '$city%'")
                ->whereRaw("LOWER(state) like '$state%'")
                ->whereRaw("zip like '$zip%'")
                ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
                ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
                ->whereRaw("contact_phone like '$contact_phone%'")
                ->whereRaw("LOWER(email) like '$email%'")
                ->orderBy('code')
                ->orderBy('code_number')
                ->with([
                    'contacts'
                ])
                ->get();
        }


        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function divisionSearch(Request $request): JsonResponse
    {
        $DIVISION = new Division();

        $code = $request->search[0]['data'] ?? '';
        $name = $request->search[1]['data'] ?? '';
        $city = $request->search[2]['data'] ?? '';
        $state = $request->search[3]['data'] ?? '';
        $zip = $request->search[4]['data'] ?? '';
        $contact_first_name = $request->search[5]['data'] ?? '';
        $contact_last_name = $request->search[6]['data'] ?? '';
        $contact_phone = $request->search[7]['data'] ?? '';
        $email = $request->search[8]['data'] ?? '';

        $divisions = $DIVISION->whereRaw("1 = 1")
            ->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->whereRaw("LOWER(name) like '$name%'")
            ->whereRaw("LOWER(city) like '$city%'")
            ->whereRaw("LOWER(state) like '$state%'")
            ->whereRaw("zip like '$zip%'")
            ->whereRaw("LOWER(contact_first_name) like '$contact_first_name%'")
            ->whereRaw("LOWER(contact_last_name) like '$contact_last_name%'")
            ->whereRaw("contact_phone like '$contact_phone%'")
            ->whereRaw("LOWER(email) like '$email%'")
            ->orderBy('code')
            ->orderBy('code_number')
            ->get();

        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionOrders(Request $request)
    {
        $id = $request->id ?? 0;

        $ORDER = Order::query();

        $ORDER->whereRaw("1 = 1");
        $ORDER->whereHas('division', function ($query1) use ($id) {
            $query1->where('id', $id);
        });

        $ORDER->select([
            'id',
            'order_number'
        ]);

        $ORDER->with([
            'bill_to_company',
            'pickups',
            'deliveries',
            'routing'
        ]);


        $ORDER->orderBy('id', 'desc');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivision(Request $request): JsonResponse
    {
        $DIVISION = new Division();
        $DIVISION_CONTACT = new DivisionContact();

        $id = $request->id ?? '';
        $code = $request->code ?? '';
        $code_number = $request->code_number ?? 0;
        $name = $request->name ?? '';
        $address1 = $request->address1 ?? '';
        $address2 = $request->address2 ?? '';
        $city = $request->city ?? '';
        $state = $request->state ?? '';
        $zip = $request->zip ?? '';
        $contact_first_name = $request->contact_first_name ?? '';
        $contact_last_name = $request->contact_last_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? ($request->ext ?? '');
        $email = $request->email ?? '';

        $curDivision = $DIVISION->where('id', $id)->first();

        if ($curDivision) {
            if ($curDivision->code !== $code) {
                $codeExist = $DIVISION->where('id', '<>', $id)
                    ->where('code', $code)->get();

                if (count($codeExist) > 0) {
                    $max_code_number = $DIVISION->where('code', $code)->max('code_number');
                    $code_number = $max_code_number + 1;
                } else {
                    $code_number = 0;
                }
            }
        } else {
            $codeExist = $DIVISION->where('code', $code)->get();

            if (count($codeExist) > 0) {
                $max_code_number = $DIVISION->where('code', $code)->max('code_number');
                $code_number = $max_code_number + 1;
            } else {
                $code_number = 0;
            }
        }

        $with_contact = true;

        if (trim($contact_first_name) === '' || trim($contact_phone) === '') {
            $with_contact = false;
        }

        $division = $DIVISION->updateOrCreate([
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
                'contact_first_name' => $contact_first_name,
                'contact_last_name' => $contact_last_name,
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email),
            ]);

        if ($with_contact) {
            $contacts = $DIVISION_CONTACT->where('division_id', $division->id)->get();

            if (count($contacts) === 0) {
                $contact = new DivisionContact();
                $contact->division_id = $division->id;
                $contact->first_name = trim($contact_first_name);
                $contact->last_name = trim($contact_last_name);
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

                $DIVISION->where('id', $division->id)->update([
                    'primary_contact_id' => $contact->id
                ]);
            } elseif (count($contacts) === 1) {

                $contact = $contacts[0];
                if ($contact->first_name === $contact_first_name && $contact->last_name === $contact_last_name) {

                    $DIVISION_CONTACT->where('id', $contact->id)->update([
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

        $newDivision = $DIVISION->where('id', $division->id)
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address'
            ])->first();

        return response()->json(['result' => 'OK', 'division' => $newDivision]);
    }
}
