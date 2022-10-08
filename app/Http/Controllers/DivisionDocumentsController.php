<?php

namespace App\Http\Controllers;

use App\Models\DivisionDocument;
use App\Models\DivisionDocumentNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByDivision(Request $request): JsonResponse
    {
        $DIVISION_DOCUMENT = new DivisionDocument();

        $division_id = $request->division_id;
        $documents = $DIVISION_DOCUMENT->where('division_id', $division_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionDocument(Request $request): JsonResponse
    {
        $DIVISION_DOCUMENT = new DivisionDocument();

        $division_id = $request->division_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $DIVISION_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'division_id' => $division_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_code_id' => $user_code_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $DIVISION_DOCUMENT->where('division_id', $division_id)->with(['notes', 'user_code'])->get();

        move_uploaded_file($fileData['tmp_name'], public_path('division-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDivisionDocument(Request $request): JsonResponse
    {
        $DIVISION_DOCUMENT = new DivisionDocument();

        $doc_id = $request->doc_id;
        $division_id = $request->division_id;

        $DIVISION_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('division-documents/' . $doc_id))){
            try {
                unlink(public_path('division-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $DIVISION_DOCUMENT->where('division_id', $division_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByDivisionDocument(Request $request): JsonResponse
    {
        $DIVISION_DOCUMENT_NOTE = new DivisionDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $DIVISION_DOCUMENT_NOTE->where('division_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionDocumentNote(Request $request): JsonResponse
    {
        $DIVISION_DOCUMENT = new DivisionDocument();
        $DIVISION_DOCUMENT_NOTE = new DivisionDocumentNote();

        $id = $request->id;
        $division_id = $request->division_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $DIVISION_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'division_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $DIVISION_DOCUMENT_NOTE->where('division_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $DIVISION_DOCUMENT->where('division_id', $division_id)->with(['notes'])->get();

        return response()->json(['result' => 'OK', 'note' => $documentNote, 'notes' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDivisionDocumentNote(Request $request) : JsonResponse{
        $DIVISION_DOCUMENT_NOTE = new DivisionDocumentNote();
        $id = $request->id ?? null;
        $division_document_id = $request->division_document_id ?? null;

        $DIVISION_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $DIVISION_DOCUMENT_NOTE->where('division_document_id', $division_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
