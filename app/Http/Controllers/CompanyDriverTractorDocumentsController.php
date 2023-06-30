<?php

namespace App\Http\Controllers;

use App\Models\CompanyDriverTractorDocument;
use App\Models\CompanyDriverTractorDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CompanyDriverTractorDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByCompanyDriverTractor(Request $request): JsonResponse
    {
        $TRACTOR_DOCUMENT = new CompanyDriverTractorDocument();

        $company_driver_tractor_id = $request->company_driver_tractor_id;
        $documents = $TRACTOR_DOCUMENT->where('company_driver_tractor_id', $company_driver_tractor_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyDriverTractorDocument(Request $request): JsonResponse
    {
        $TRACTOR_DOCUMENT = new CompanyDriverTractorDocument();

        $company_driver_tractor_id = $request->company_driver_tractor_id;
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

            $document = $TRACTOR_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'company_driver_tractor_id' => $company_driver_tractor_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $TRACTOR_DOCUMENT->where('company_driver_tractor_id', $company_driver_tractor_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('company-driver-tractor-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyDriverTractorDocument(Request $request): JsonResponse
    {
        $TRACTOR_DOCUMENT = new CompanyDriverTractorDocument();

        $doc_id = $request->doc_id;
        $company_driver_tractor_id = $request->company_driver_tractor_id;

        $TRACTOR_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('company-driver-tractor-documents/' . $doc_id))){
            try {
                unlink(public_path('company-driver-tractor-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $TRACTOR_DOCUMENT->where('company_driver_tractor_id', $company_driver_tractor_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByCompanyDriverTractorDocument(Request $request): JsonResponse
    {
        $TRACTOR_DOCUMENT_NOTE = new CompanyDriverTractorDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $TRACTOR_DOCUMENT_NOTE->where('company_driver_tractor_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyDriverTractorDocumentNote(Request $request): JsonResponse
    {
        $TRACTOR_DOCUMENT = new CompanyDriverTractorDocument();
        $TRACTOR_DOCUMENT_NOTE = new CompanyDriverTractorDocumentNote();

        $id = $request->id;
        $company_driver_tractor_id = $request->company_driver_tractor_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $TRACTOR_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_driver_tractor_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $TRACTOR_DOCUMENT_NOTE->where('company_driver_tractor_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $TRACTOR_DOCUMENT->where('company_driver_tractor_id', $company_driver_tractor_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyDriverTractorDocumentNote(Request $request) : JsonResponse{
        $TRACTOR_DOCUMENT_NOTE = new CompanyDriverTractorDocumentNote();
        $id = $request->id ?? null;
        $TRACTOR_DOCUMENT_id = $request->company_driver_tractor_document_id ?? null;

        $TRACTOR_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $TRACTOR_DOCUMENT_NOTE->where('company_driver_tractor_document_id', $TRACTOR_DOCUMENT_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
