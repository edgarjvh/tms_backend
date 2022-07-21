<?php

namespace App\Http\Controllers;

use App\Models\CarrierNote;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function notes(Request $request){
        $customer_id = $request->customer_id;
        $notes = Note::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveNote(Request $request){
        $NOTE = new Note();
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $note_text = $request->note;
        $note_user = $request->user;
        $note_datetime = $request->datetime;

        if ($customer_id > 0){
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $note_text,
                'user' => $note_user,
                'date_time' => $note_datetime
            ]);

            $notes = $NOTE->where('customer_id', $customer_id)->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        }else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }

    public function saveCustomerNote(Request $request) {
        $NOTE = new Note();
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $note_text = $request->text;
        $note_user = $request->user;
        $note_date_time = $request->date_time;

        if ($customer_id > 0){
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $note_text,
                'user' => $note_user,
                'date_time' => $note_date_time
            ]);

            $notes = $NOTE->where('customer_id', $customer_id)->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'data' => $notes]);
        }else {
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

        $notes = $CUSTOMER_NOTE->where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'data' => $notes]);
    }

    public function carrierNotes(Request $request){
        $carrier_id = $request->carrier_id;
        $notes = CarrierNote::where('carrier_id', $carrier_id)->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveCarrierNote(Request $request){
        $NOTE = new CarrierNote();
        $id = $request->id ?? 0;
        $carrier_id = $request->customer_id ?? 0;
        $note_text = $request->text;
        $note_user = $request->user;
        $note_date_time = $request->date_time;

        if ($carrier_id > 0){
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'carrier_id' => $carrier_id,
                'text' => $note_text,
                'user' => $note_user,
                'date_time' => $note_date_time
            ]);

            $notes = $NOTE->where('carrier_id', $carrier_id)->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'data' => $notes]);
        }else {
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

        $notes = $CARRIER_NOTE->where('carrier_id', $carrier_id)->get();

        return response()->json(['result' => 'OK', 'data' => $notes]);
    }
}
