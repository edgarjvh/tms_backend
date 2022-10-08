<?php

namespace App\Http\Controllers;

use App\Models\OperatorDocument;
use App\Models\OperatorDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OperatorDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByOperator(Request $request): JsonResponse
    {
        $OPERATOR_DOCUMENT = new OperatorDocument();

        $operator_id = $request->operator_id;
        $documents = $OPERATOR_DOCUMENT->where('operator_id', $operator_id)->with(['notes','user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOperatorDocument(Request $request): JsonResponse
    {
        $OPERATOR_DOCUMENT = new OperatorDocument();

        $operator_id = $request->operator_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $OPERATOR_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'operator_id' => $operator_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_code_id' => $user_code_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $document = $OPERATOR_DOCUMENT->where('id', $document->id)->with(['notes', 'user_code'])->get();
        $documents = $OPERATOR_DOCUMENT->where('operator_id', $operator_id)->with(['notes', 'user_code'])->get();

        move_uploaded_file($fileData['tmp_name'], public_path('operator-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOperatorDocument(Request $request): JsonResponse
    {
        $OPERATOR_DOCUMENT = new OperatorDocument();

        $doc_id = $request->doc_id;
        $operator_id = $request->operator_id;

        $OPERATOR_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('operator-documents/' . $doc_id))){
            try {
                unlink(public_path('operator-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $OPERATOR_DOCUMENT->where('operator_id', $operator_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByOperatorDocument(Request $request): JsonResponse
    {
        $OPERATOR_DOCUMENT_NOTE = new OperatorDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $OPERATOR_DOCUMENT_NOTE->where('company_operator_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOperatorDocumentNote(Request $request): JsonResponse
    {
        $OPERATOR_DOCUMENT = new OperatorDocument();
        $OPERATOR_DOCUMENT_NOTE = new OperatorDocumentNote();

        $id = $request->id;
        $operator_id = $request->operator_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $OPERATOR_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $OPERATOR_DOCUMENT_NOTE->where('company_operator_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $OPERATOR_DOCUMENT->where('operator_id', $operator_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOperatorDocumentNote(Request $request) : JsonResponse{
        $OPERATOR_DOCUMENT_NOTE = new OperatorDocumentNote();
        $id = $request->id ?? null;
        $company_operator_document_id = $request->company_operator_document_id ?? null;

        $OPERATOR_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $OPERATOR_DOCUMENT_NOTE->where('company_operator_document_id', $company_operator_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
