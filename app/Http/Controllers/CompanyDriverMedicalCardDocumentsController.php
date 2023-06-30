<?php

namespace App\Http\Controllers;

use App\Models\CompanyDriverMedicalCardDocument;
use App\Models\CompanyDriverMedicalCardDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CompanyDriverMedicalCardDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByCompanyDriverMedicalCard(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new CompanyDriverMedicalCardDocument();

        $company_driver_medical_card_id = $request->company_driver_medical_card_id;
        $documents = $MEDICAL_CARD_DOCUMENT->where('company_driver_medical_card_id', $company_driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new CompanyDriverMedicalCardDocument();

        $company_driver_medical_card_id = $request->company_driver_medical_card_id;
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

            $document = $MEDICAL_CARD_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'company_driver_medical_card_id' => $company_driver_medical_card_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $MEDICAL_CARD_DOCUMENT->where('company_driver_medical_card_id', $company_driver_medical_card_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('company-driver-medical-card-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new CompanyDriverMedicalCardDocument();

        $doc_id = $request->doc_id;
        $company_driver_medical_card_id = $request->company_driver_medical_card_id;

        $MEDICAL_CARD_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('company-driver-medical-card-documents/' . $doc_id))){
            try {
                unlink(public_path('company-driver-medical-card-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $MEDICAL_CARD_DOCUMENT->where('company_driver_medical_card_id', $company_driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByCompanyDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT_NOTE = new CompanyDriverMedicalCardDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('company_driver_medical_card_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCompanyDriverMedicalCardDocumentNote(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new CompanyDriverMedicalCardDocument();
        $MEDICAL_CARD_DOCUMENT_NOTE = new CompanyDriverMedicalCardDocumentNote();

        $id = $request->id;
        $company_driver_medical_card_id = $request->company_driver_medical_card_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $MEDICAL_CARD_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_driver_medical_card_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('company_driver_medical_card_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $MEDICAL_CARD_DOCUMENT->where('company_driver_medical_card_id', $company_driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCompanyDriverMedicalCardDocumentNote(Request $request) : JsonResponse{
        $MEDICAL_CARD_DOCUMENT_NOTE = new CompanyDriverMedicalCardDocumentNote();
        $id = $request->id ?? null;
        $MEDICAL_CARD_DOCUMENT_id = $request->company_driver_medical_card_document_id ?? null;

        $MEDICAL_CARD_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('company_driver_medical_card_document_id', $MEDICAL_CARD_DOCUMENT_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
