<?php

namespace App\Http\Controllers;

use App\Models\DriverMedicalCardDocument;
use App\Models\DriverMedicalCardDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DriverMedicalCardDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByDriverMedicalCard(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new DriverMedicalCardDocument();

        $driver_medical_card_id = $request->driver_medical_card_id;
        $documents = $MEDICAL_CARD_DOCUMENT->where('driver_medical_card_id', $driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new DriverMedicalCardDocument();

        $driver_medical_card_id = $request->driver_medical_card_id;
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
                'driver_medical_card_id' => $driver_medical_card_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $MEDICAL_CARD_DOCUMENT->where('driver_medical_card_id', $driver_medical_card_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('driver-medical-card-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new DriverMedicalCardDocument();

        $doc_id = $request->doc_id;
        $driver_medical_card_id = $request->driver_medical_card_id;

        $MEDICAL_CARD_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('driver-medical-card-documents/' . $doc_id))){
            try {
                unlink(public_path('driver-medical-card-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $MEDICAL_CARD_DOCUMENT->where('driver_medical_card_id', $driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByDriverMedicalCardDocument(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT_NOTE = new DriverMedicalCardDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('driver_medical_card_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverMedicalCardDocumentNote(Request $request): JsonResponse
    {
        $MEDICAL_CARD_DOCUMENT = new DriverMedicalCardDocument();
        $MEDICAL_CARD_DOCUMENT_NOTE = new DriverMedicalCardDocumentNote();

        $id = $request->id;
        $driver_medical_card_id = $request->driver_medical_card_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $MEDICAL_CARD_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'driver_medical_card_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('driver_medical_card_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $MEDICAL_CARD_DOCUMENT->where('driver_medical_card_id', $driver_medical_card_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverMedicalCardDocumentNote(Request $request) : JsonResponse{
        $MEDICAL_CARD_DOCUMENT_NOTE = new DriverMedicalCardDocumentNote();
        $id = $request->id ?? null;
        $MEDICAL_CARD_DOCUMENT_id = $request->driver_medical_card_document_id ?? null;

        $MEDICAL_CARD_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $MEDICAL_CARD_DOCUMENT_NOTE->where('driver_medical_card_document_id', $MEDICAL_CARD_DOCUMENT_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
