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
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();

        $documents = $FACTORING_COMPANY_DOCUMENT->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getDocumentsByFactoringCompany(Request $request){
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();
        $factoring_company_id = $request->factoring_company_id;

        $documents = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_id', $factoring_company_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveFactoringCompanyDocument(Request $request){
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();

        $factoring_company_id = $request->factoring_company_id ?? 0;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? null;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $FACTORING_COMPANY_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'factoring_company_id' => $factoring_company_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_code_id' => $user_code_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_id', $factoring_company_id)->with(['notes', 'user_code'])->get();

        move_uploaded_file($fileData['tmp_name'], public_path('factoring-company-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteFactoringCompanyDocument(Request $request){
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();
        $doc_id = $request->doc_id;
        $factoring_company_id = $request->factoring_company_id;

        $document = $FACTORING_COMPANY_DOCUMENT->where('doc_id', $doc_id)->delete();
        try {
            unlink(public_path('factoring_company-documents/' . $doc_id));
        } catch (\Throwable $e) {

        } catch (\Exception $e) {

        }

        $documents = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_id', $factoring_company_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByFactoringCompanyDocument(Request $request){
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();
        $doc_id = $request->doc_id;

        $documentNotes = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $documentNotes]);
    }

    public function saveFactoringCompanyDocumentNote(Request $request){
        $FACTORING_COMPANY_DOCUMENT = new FactoringCompanyDocument();
        $id = $request->id;
        $factoring_company_id = $request->factoring_company_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $FACTORING_COMPANY_DOCUMENT->updateOrCreate([
            'id' => $id
        ], [
            'factoring_company_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $FACTORING_COMPANY_DOCUMENT->where('factoring_company_id', $factoring_company_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $documentNote, 'notes' => $documentNotes, 'documents' => $documents]);
    }
}
