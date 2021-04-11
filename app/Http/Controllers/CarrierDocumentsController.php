<?php

namespace App\Http\Controllers;

use App\CarrierDocument;
use App\CarrierDocumentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarrierDocumentsController extends Controller
{
    public function getFile($filename){
        $file = Storage::disk('local_public');
        dd($file);
        $file = File::get($file);
        return response()->download($file);
//        return response()->json(['result' => 'OK', 'documents' => $file]);
    }

    public function getDocuments(Request $request){
        $documents = CarrierDocument::with('notes')->all();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getDocumentsByCarrier(Request $request){
        $carrier_id = $request->carrier_id;
        $documents = CarrierDocument::where('carrier_id', $carrier_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveDocument(Request $request){
        $carrier_id = $request->carrier_id;
        $date_entered = isset($request->date_entered) ? $request->date_entered : '';
        $title = isset($request->title) ? $request->title : '';
        $subject = isset($request->subject) ? $request->subject : '';
        $tags = isset($request->tags) ? $request->tags : '';
        $user_id = isset($request->user_id) ? $request->user_id : 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = CarrierDocument::updateOrCreate([
            'id' => 0
        ], [
            'carrier_id' => $carrier_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = CarrierDocument::where('carrier_id', $carrier_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('carrier-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteCarrierDocument(Request $request){
        $doc_id = $request->doc_id;
        $carrier_id = $request->carrier_id;

        $document = CarrierDocument::where('doc_id', $doc_id)->delete();

        unlink(public_path('carrier-documents/' . $doc_id));

        $documents = CarrierDocument::where('carrier_id', $carrier_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByDocument(Request $request){
        $doc_id = $request->doc_id;

        $documentNotes = CarrierDocumentNote::where('carrier_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveCarrierDocumentNote(Request $request){
        $note_id = $request->note_id;
        $carrier_id = $request->carrier_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = CarrierDocumentNote::updateOrCreate([
            'id' => $note_id
        ], [
            'carrier_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = CarrierDocumentNote::where('carrier_document_id', $doc_id)->get();
        $documents = CarrierDocument::where('carrier_id', $carrier_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
