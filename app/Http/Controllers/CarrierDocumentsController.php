<?php

namespace App\Http\Controllers;

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
        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with('notes')->get();

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
        $user_id = $request->user_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $CARRIER_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'carrier_id' => $carrier_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('carrier-documents/' . $doc_id));

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

        try {
            unlink(public_path('carrier-documents/' . $doc_id));
        } catch (Throwable | Exception $e) {
        }

        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with('notes')->get();

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

        $documentNotes = $CARRIER_DOCUMENT_NOTE->where('carrier_document_id', $doc_id)->get();

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

        $note_id = $request->note_id;
        $carrier_id = $request->carrier_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = $CARRIER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $note_id
        ], [
            'carrier_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = $CARRIER_DOCUMENT_NOTE->where('carrier_document_id', $doc_id)->get();
        $documents = $CARRIER_DOCUMENT->where('carrier_id', $carrier_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
