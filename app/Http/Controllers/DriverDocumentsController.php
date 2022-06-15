<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use App\Models\DriverDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DriverDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByDriver(Request $request): JsonResponse
    {
        $DRIVER_DOCUMENT = new DriverDocument();

        $driver_id = $request->driver_id;
        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverDocument(Request $request): JsonResponse
    {
        $DRIVER_DOCUMENT = new DriverDocument();

        $driver_id = $request->driver_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_id = $request->user_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $DRIVER_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'driver_id' => $driver_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('driver-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverDocument(Request $request): JsonResponse
    {
        $DRIVER_DOCUMENT = new DriverDocument();

        $doc_id = $request->doc_id;
        $driver_id = $request->driver_id;

        $DRIVER_DOCUMENT->where('doc_id', $doc_id)->delete();
        try {
            unlink(public_path('driver-documents/' . $doc_id));
        } catch (Throwable | Exception $e) {
        }

        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByDriverDocument(Request $request): JsonResponse
    {
        $DRIVER_DOCUMENT_NOTE = new DriverDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $DRIVER_DOCUMENT_NOTE->where('company_driver_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverDocumentNote(Request $request): JsonResponse
    {
        $DRIVER_DOCUMENT = new DriverDocument();
        $DRIVER_DOCUMENT_NOTE = new DriverDocumentNote();

        $note_id = $request->note_id;
        $driver_id = $request->driver_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = $DRIVER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $note_id
        ], [
            'company_driver_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = $DRIVER_DOCUMENT_NOTE->where('company_driver_document_id', $doc_id)->get();
        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
