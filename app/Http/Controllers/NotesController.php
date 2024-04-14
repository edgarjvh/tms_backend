<?php

namespace App\Http\Controllers;

use App\Models\CarrierNote;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notes(Request $request): JsonResponse
    {
        $NOTE = new Note();
        $customer_id = $request->customer_id;
        $notes = $NOTE->where('customer_id', $customer_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveNote(Request $request): JsonResponse
    {
        $NOTE = new Note();
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $text = $request->text;
        $user_code_id = $request->user_code_id;

        if ($customer_id > 0) {
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $note = $NOTE->where('id', $note->id)->with(['user_code'])->first();
            $notes = $NOTE->where('customer_id', $customer_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        } else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerNote(Request $request): JsonResponse
    {

        $NOTE = new Note();
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $text = $request->text;
        $user_code_id = $request->user_code_id;

        if ($customer_id > 0) {
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $note = $NOTE->where('id', $note->id)->with(['user_code'])->first();
            $notes = $NOTE->where('customer_id', $customer_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        } else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerNote(Request $request): JsonResponse
    {
        $CUSTOMER_NOTE = new Note();

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $CUSTOMER_NOTE->where('id', $id)->delete();

        $notes = $CUSTOMER_NOTE->where('customer_id', $customer_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function carrierNotes(Request $request): JsonResponse
    {
        $NOTE = new CarrierNote();
        $carrier_id = $request->carrier_id;
        $notes = $NOTE->where('carrier_id', $carrier_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveCarrierNote(Request $request)
    {
        $NOTE = new CarrierNote();
        $id = $request->id ?? 0;
        $carrier_id = $request->customer_id ?? 0;
        $text = $request->text;
        $user_code_id = $request->user_code_id;

        if ($carrier_id > 0) {
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'carrier_id' => $carrier_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $note = $NOTE->where('id', $note->id)->with(['user_code'])->first();
            $notes = $NOTE->where('carrier_id', $carrier_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        } else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierNote(Request $request): JsonResponse
    {
        $CARRIER_NOTE = new CarrierNote();

        $id = $request->id ?? 0;
        $carrier_id = $request->carrier_id ?? 0;

        $CARRIER_NOTE->where('id', $id)->delete();

        $notes = $CARRIER_NOTE->where('carrier_id', $carrier_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }
}
