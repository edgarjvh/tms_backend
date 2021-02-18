<?php

namespace App\Http\Controllers;

use App\InternalNotes;
use App\NotesForCarrier;
use Illuminate\Http\Request;

class DispatchNotesController extends Controller
{
    public function getDispatchNotes(){
        $internal_notes = InternalNotes::all();
        $notes_for_carrier = NotesForCarrier::all();

        return response()->json(['result' => 'OK', 'notes_for_carrier' => $notes_for_carrier, 'internal_notes' => $internal_notes]);
    }

    public function getInternalNotes(Request $request)
    {
        $internal_notes = InternalNotes::all();

        return response()->json(['result' => 'OK', 'internal_notes' => $internal_notes]);
    }

    public function saveInternalNotes(Request $request)
    {
        $dispatch_id = isset($request->dispatch_id) ? $request->dispatch_id : 0;
        $user = isset($request->user) ? $request->user : '';
        $date_time = isset($request->date_time) ? $request->date_time : '';
        $note = isset($request->note) ? $request->note : '';

        $internal_note = InternalNotes::updateOrCreate([
            'id' => 0
        ], [
            'dispatch_id' => 0,
            'user' => $user,
            'date_time' => $date_time,
            'note' => $note
        ]);

        $internal_notes = InternalNotes::all();

        return response()->json(['result' => 'OK', 'internal_note' => $internal_note, 'internal_notes' => $internal_notes]);
    }

    public function getNotesForCarrier(Request $request)
    {
        $notes_for_carrier = NotesForCarrier::all();

        return response()->json(['result' => 'OK', 'notes_for_carrier' => $notes_for_carrier]);
    }

    public function saveNotesForCarrier(Request $request)
    {
        $id = isset($request->id) ? $request->id : 0;
        $user = isset($request->user) ? $request->user : '';
        $date_time = isset($request->date_time) ? $request->date_time : '';
        $note = isset($request->note) ? $request->note : '';

        $note_for_carrier = NotesForCarrier::updateOrCreate([
            'id' => $id
        ], [
            'dispatch_id' => 0,
            'user' => $user,
            'date_time' => $date_time,
            'note' => $note
        ]);

        $notes_for_carrier = NotesForCarrier::all();

        return response()->json(['result' => 'OK', 'note_for_carrier' => $note_for_carrier, 'notes_for_carrier' => $notes_for_carrier]);
    }

    public function deleteNotesForCarrier(Request $request)
    {
        $id = isset($request->id) ? $request->id : 0;

        $note_for_carrier = NotesForCarrier::where('id', $id)->delete();

        $notes_for_carrier = NotesForCarrier::all();

        return response()->json(['result' => 'OK', 'note_for_carrier' => $note_for_carrier, 'notes_for_carrier' => $notes_for_carrier]);
    }
}
