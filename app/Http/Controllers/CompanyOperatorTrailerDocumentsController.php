<?php

namespace App\Http\Controllers;

use App\Models\CompanyOperatorTrailerDocument;
use App\Models\CompanyOperatorTrailerDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CompanyOperatorTrailerDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByCompanyOperatorTrailer(Request $request): JsonResponse
    {
        $TRAILER_DOCUMENT = new CompanyOperatorTrailerDocument();

        $company_operator_trailer_id = $request->company_operator_trailer_id;
        $documents = $TRAILER_DOCUMENT->where('company_operator_trailer_id', $company_operator_trailer_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorTrailerDocument(Request $request): JsonResponse
    {
        $TRAILER_DOCUMENT = new CompanyOperatorTrailerDocument();

        $company_operator_trailer_id = $request->company_operator_trailer_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++){
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = $TRAILER_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'company_operator_trailer_id' => $company_operator_trailer_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $TRAILER_DOCUMENT->where('company_operator_trailer_id', $company_operator_trailer_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('company-operator-trailer-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorTrailerDocument(Request $request): JsonResponse
    {
        $TRAILER_DOCUMENT = new CompanyOperatorTrailerDocument();

        $doc_id = $request->doc_id;
        $company_operator_trailer_id = $request->company_operator_trailer_id;

        $TRAILER_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('company-operator-trailer-documents/' . $doc_id))){
            try {
                unlink(public_path('company-operator-trailer-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $TRAILER_DOCUMENT->where('company_operator_trailer_id', $company_operator_trailer_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByCompanyOperatorTrailerDocument(Request $request): JsonResponse
    {
        $TRAILER_DOCUMENT_NOTE = new CompanyOperatorTrailerDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $TRAILER_DOCUMENT_NOTE->where('company_operator_trailer_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyOperatorTrailerDocumentNote(Request $request): JsonResponse
    {
        $TRAILER_DOCUMENT = new CompanyOperatorTrailerDocument();
        $TRAILER_DOCUMENT_NOTE = new CompanyOperatorTrailerDocumentNote();

        $id = $request->id;
        $company_operator_trailer_id = $request->company_operator_trailer_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $TRAILER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_operator_trailer_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNotes = $TRAILER_DOCUMENT_NOTE->where('company_operator_trailer_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $TRAILER_DOCUMENT->where('company_operator_trailer_id', $company_operator_trailer_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyOperatorTrailerDocumentNote(Request $request) : JsonResponse{
        $TRAILER_DOCUMENT_NOTE = new CompanyOperatorTrailerDocumentNote();
        $id = $request->id ?? null;
        $TRAILER_DOCUMENT_id = $request->company_operator_trailer_document_id ?? null;

        $TRAILER_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $TRAILER_DOCUMENT_NOTE->where('company_operator_trailer_document_id', $TRAILER_DOCUMENT_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
