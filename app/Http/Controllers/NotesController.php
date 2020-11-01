<?php

namespace App\Http\Controllers;

use App\Note;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function notes(Request $request){
        $notes = Note::whereRaw("1 = 1")->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    public function saveNote(Request $request){
        $note_text = $request->note;
        $note_user = $request->user;
        $note_datetime = $request->datetime;

        $note = new Note();
        $note->note = $note_text;
        $note->user = $note_user;
        $note->date_time = $note_datetime;
        $note->save();

        $notes = Note::whereRaw("1 = 1")->get();

        return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
    }
}
