<?php

namespace App\Http\Controllers;

use App\OrderDocument;
use App\OrderDocumentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderDocumentsController extends Controller
{
    public function getFile($filename){
        $file = Storage::disk('local_public');
        $file = File::get($file);
        return response()->download($file);
//        return response()->json(['result' => 'OK', 'documents' => $file]);
    }

    public function getDocuments(Request $request){
        $documents = OrderDocument::with('notes')->all();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getDocumentsByOrder(Request $request){
        $order_id = $request->order_id;
        $documents = OrderDocument::where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveDocument(Request $request){
        $order_id = $request->order_id;
        $date_entered = isset($request->date_entered) ? $request->date_entered : '';
        $title = isset($request->title) ? $request->title : '';
        $subject = isset($request->subject) ? $request->subject : '';
        $tags = isset($request->tags) ? $request->tags : '';
        $user_id = isset($request->user_id) ? $request->user_id : 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = OrderDocument::updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = OrderDocument::where('order_id', $order_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('order-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteOrderDocument(Request $request){
        $doc_id = $request->doc_id;
        $order_id = $request->order_id;

        $document = OrderDocument::where('doc_id', $doc_id)->delete();

        unlink(public_path('order-documents/' . $doc_id));

        $documents = OrderDocument::where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByDocument(Request $request){
        $doc_id = $request->doc_id;

        $documentNotes = OrderDocumentNote::where('order_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveOrderDocumentNote(Request $request){
        $note_id = $request->note_id;
        $order_id = $request->order_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = OrderDocumentNote::updateOrCreate([
            'id' => $note_id
        ], [
            'order_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = OrderDocumentNote::where('order_document_id', $doc_id)->get();
        $documents = OrderDocument::where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
