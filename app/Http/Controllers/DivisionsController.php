<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Division;
use App\Models\DivisionContact;
use App\Models\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'mailing_address',
                'mailing_same'
            ])
            ->first();

        return response()->json(['result' => 'OK', 'division' => $division]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionByCode(Request $request): JsonResponse
    {
        $DIVISION = Division::query();
        $code = $request->code ?? '';

        $division = $DIVISION->whereRaw("CONCAT(`code`,`code_number`) like '$code%'")
            ->with([
                'contacts',
                'documents',
                'hours',
                'notes',
                'mailing_address',
                'mailing_same'
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
                    'mailing_address',
                'mailing_same'
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
    public function getDivisionsDropdown(Request $request): JsonResponse
    {
        $name = strtolower($request->name ?? '');
        $DIVISION = Division::query();

        $DIVISION->whereRaw("1 = 1");
        $DIVISION->whereRaw("LOWER(name) like '$name%'");
        $divisions = $DIVISION->select(['id', 'name', 'type'])->get();

        return response()->json(['result'=>'OK', 'divisions'=>$divisions]);
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
    public function getDivisionsList() : JsonResponse
    {
        /**
         * SETTING UP THE THE QUERY STRING
         */
        $sql =
            /** @lang text */
            "SELECT
                d.id,
                d.code,
                d.code_number,
                d.name
            FROM divisions AS d
            ORDER BY d.code, d.code_number";

        $divisions = DB::select($sql);

        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionOrders(Request $request)
    {
        $id = $request->id ?? 0;

        /**
         * SETTING UP THE THE QUERY STRING
         */
        $sql =
            /** @lang text */
            "SELECT
                o.id,
                o.order_number,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_pickup_city,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_delivery_city,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_pickup_state,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id ASC LIMIT 1))) AS from_delivery_state,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_pickup_city,
                (SELECT c.city FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_city,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_pickups WHERE id = (SELECT pickup_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_pickup_state,
                (SELECT c.state FROM customers AS c WHERE c.id = (SELECT customer_id FROM order_deliveries WHERE id = (SELECT delivery_id FROM order_routing WHERE order_id = o.id ORDER BY id DESC LIMIT 1))) AS to_delivery_state
            FROM orders AS o
            WHERE o.is_imported = 0
                AND o.division_id = ?
            ORDER BY o.order_number DESC";

        $params = [$id];

        $orders = DB::select($sql, $params);

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
        $mailing_address_id = $request->mailing_address_id ?? null;
        $remit_to_address_is_the_same = $request->remit_to_address_is_the_same ?? 0;
        $mailing_division_contact_id = $request->mailing_division_contact_id ?? null;
        $mailing_division_contact_primary_phone = $request->mailing_division_contact_primary_phone ?? 'work';
        $mailing_division_contact_primary_email = $request->mailing_division_contact_primary_email ?? 'work';
        $type = $request->type ?? 'company';

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
                'name' => ucwords($name),
                'address1' => $address1,
                'address2' => $address2,
                'city' => ucwords($city),
                'state' => strtoupper($state),
                'zip' => $zip,
                'contact_first_name' => ucwords($contact_first_name),
                'contact_last_name' => ucwords($contact_last_name),
                'contact_phone' => $contact_phone,
                'ext' => $contact_phone_ext,
                'email' => strtolower($email),
                'mailing_address_id' => $mailing_address_id,
                'remit_to_address_is_the_same' => $remit_to_address_is_the_same,
                'mailing_division_contact_id' => $mailing_division_contact_id,
                'mailing_division_contact_primary_phone' => $mailing_division_contact_primary_phone,
                'mailing_division_contact_primary_email' => $mailing_division_contact_primary_email,
                'type' => strtolower($type)
            ]);

        if ($with_contact) {
            $contacts = $DIVISION_CONTACT->where('division_id', $division->id)->get();

            if (count($contacts) === 0) {
                $contact = new DivisionContact();
                $contact->division_id = $division->id;
                $contact->first_name = ucwords(trim($contact_first_name));
                $contact->last_name = ucwords(trim($contact_last_name));
                $contact->phone_work = $contact_phone;
                $contact->phone_ext = $contact_phone_ext;
                $contact->email_work = strtolower($email);
                $contact->address1 = $address1;
                $contact->address2 = $address2;
                $contact->city = ucwords($city);
                $contact->state = strtoupper($state);
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
                'mailing_address',
                'mailing_same'
            ])->first();

        return response()->json(['result' => 'OK', 'division' => $newDivision]);
    }
}
