<?php

namespace App\Http\Controllers;

use App\Models\CarrierNote;
use App\Models\Note;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function notes(Request $request){
        $customer_id = $request->customer_id;
        $notes = Note::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveNote(Request $request){
        $customer_id = $request->customer_id;
        $note_text = $request->note;
        $note_user = $request->user;
        $note_datetime = $request->datetime;

        $note = new Note();
        $note->customer_id = $customer_id;
        $note->note = $note_text;
        $note->user = $note_user;
        $note->date_time = $note_datetime;
        $note->save();

        $notes = Note::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
    }

    public function saveCustomerNote(Request $request) {
        $customer_id = $request->customer_id;
        $note_text = $request->text;
        $note_user = $request->user;
        $note_datetime = $request->date_time;

        $note = new Note();
        $note->customer_id = $customer_id;
        $note->text = $note_text;
        $note->user = $note_user;
        $note->date_time = $note_datetime;
        $note->save();

        $notes = Note::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'note' => $note, 'data' => $notes]);
    }

    public function carrierNotes(Request $request){
        $carrier_id = $request->carrier_id;
        $notes = CarrierNote::where('carrier_id', $carrier_id)->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveCarrierNote(Request $request){
        $carrier_id = $request->carrier_id;
        $note_text = $request->text;
        $note_user = $request->user;
        $note_datetime = $request->date_time;

        $note = new CarrierNote();
        $note->carrier_id = $carrier_id;
        $note->text = $note_text;
        $note->user = $note_user;
        $note->date_time = $note_datetime;
        $note->save();

        $notes = CarrierNote::where('carrier_id', $carrier_id)->get();

        return response()->json(['result' => 'OK', 'note' => $note, 'data' => $notes]);
    }
}
