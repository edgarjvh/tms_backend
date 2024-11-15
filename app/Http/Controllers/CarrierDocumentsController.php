<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierDocument;
use App\Models\CarrierDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CarrierDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByCarrier(Request $request): JsonResponse
    {
        $CARRIER_DOCUMENT = new CarrierDocument();

        $carrier_id = $request->carrier_id;
        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierDocument(Request $request): JsonResponse
    {
        $CARRIER_DOCUMENT = new CarrierDocument();

        $carrier_id = $request->carrier_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['files'];
        $link = $request->link ?? '';

        for ($i = 0; $i < count($fileData['name']); $i++){
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = $CARRIER_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'carrier_id' => $carrier_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('carrier-documents/' . $doc_id));

            if (strtolower($link) === 'insurance'){
                $CARRIER = new Carrier();
                $carrier = $CARRIER->where('id', $carrier_id)->first();

                if ($carrier->insurance_flag === 0) {
                    $CARRIER->where('id', $carrier_id)->update(['insurance_flag' => 1]);
                }else{
                    $CARRIER->where('id', $carrier_id)->update(['insurance_flag' => 0]);
                }
            }
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierDocument(Request $request): JsonResponse
    {
        $CARRIER_DOCUMENT = new CarrierDocument();

        $doc_id = $request->doc_id;
        $carrier_id = $request->carrier_id;

        $CARRIER_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('carrier-documents/' . $doc_id))){
            try {
                unlink(public_path('carrier-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByCarrierDocument(Request $request) : JsonResponse
    {
        $CARRIER_DOCUMENT_NOTE = new CarrierDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $CARRIER_DOCUMENT_NOTE->where('carrier_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierDocumentNote(Request $request) : JsonResponse
    {
        $CARRIER_DOCUMENT = new CarrierDocument();
        $CARRIER_DOCUMENT_NOTE = new CarrierDocumentNote();

        $id = $request->id;
        $carrier_id = $request->carrier_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $CARRIER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'carrier_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNote = $CARRIER_DOCUMENT_NOTE->where('id', $documentNote->id)->with(['user_code'])->first();
        $documentNotes = $CARRIER_DOCUMENT_NOTE->where('carrier_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierDocumentNote(Request $request) : JsonResponse{
        $CARRIER_DOCUMENT_NOTE = new CarrierDocumentNote();
        $id = $request->id ?? null;
        $carrier_document_id = $request->carrier_document_id ?? null;

        $CARRIER_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $CARRIER_DOCUMENT_NOTE->where('carrier_document_id', $carrier_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
