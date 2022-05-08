<?php

namespace App\Http\Controllers;

use App\Models\FactoringCompanyDocument;
use App\Models\FactoringCompanyDocumentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FactoringCompanyDocumentsController extends Controller
{
    public function getFile($filename){
        $file = Storage::disk('local_public');
        $file = File::get($file);
        return response()->download($file);
//        return response()->json(['result' => 'OK', 'documents' => $file]);
    }

    public function getFactoringCompanyDocuments(Request $request){
        $documents = FactoringCompanyDocument::with('notes')->all();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getDocumentsByFactoringCompany(Request $request){
        $factoring_company_id = $request->factoring_company_id;
        $documents = CustomerDocument::where('factoring_company_id', $factoring_company_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveFactoringCompanyDocument(Request $request){
        $factoring_company_id = $request->factoring_company_id;
        $date_entered = isset($request->date_entered) ? $request->date_entered : '';
        $title = isset($request->title) ? $request->title : '';
        $subject = isset($request->subject) ? $request->subject : '';
        $tags = isset($request->tags) ? $request->tags : '';
        $user_id = isset($request->user_id) ? $request->user_id : 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = FactoringCompanyDocument::updateOrCreate([
            'id' => 0
        ], [
            'factoring_company_id' => $factoring_company_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = FactoringCompanyDocument::where('factoring_company_id', $factoring_company_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('factoring-company-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteFactoringCompanyDocument(Request $request){
        $doc_id = $request->doc_id;
        $factoring_company_id = $request->factoring_company_id;

        $document = FactoringCompanyDocument::where('doc_id', $doc_id)->delete();
        try {
            unlink(public_path('factoring_company-documents/' . $doc_id));
        } catch (\Throwable $e) {

        } catch (\Exception $e) {

        }

        $documents = FactoringCompanyDocument::where('factoring_company_id', $factoring_company_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByFactoringCompanyDocument(Request $request){
        $doc_id = $request->doc_id;

        $documentNotes = FactoringCompanyDocumentNote::where('factoring_company_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveFactoringCompanyDocumentNote(Request $request){
        $note_id = $request->note_id;
        $factoring_company_id = $request->factoring_company_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = FactoringCompanyDocumentNote::updateOrCreate([
            'id' => $note_id
        ], [
            'factoring_company_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = FactoringCompanyDocumentNote::where('factoring_company_document_id', $doc_id)->get();
        $documents = FactoringCompanyDocument::where('factoring_company_id', $factoring_company_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
