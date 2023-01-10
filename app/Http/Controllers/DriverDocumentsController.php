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
        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with(['notes', 'user_code'])->get();

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
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++){
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = $DRIVER_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'driver_id' => $driver_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('driver-documents/' . $doc_id));
        }

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

        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with(['notes', 'user_code'])->get();

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

        $documentNotes = $DRIVER_DOCUMENT_NOTE->where('company_driver_document_id', $doc_id)->with(['user_code'])->get();

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

        $id = $request->id;
        $driver_id = $request->driver_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $DRIVER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_driver_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $DRIVER_DOCUMENT_NOTE->where('company_driver_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $DRIVER_DOCUMENT->where('driver_id', $driver_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $documentNote, 'notes' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverDocumentNote(Request $request) : JsonResponse{
        $DRIVER_DOCUMENT_NOTE = new DriverDocumentNote();
        $id = $request->id ?? null;
        $company_driver_document_id = $request->company_driver_document_id ?? null;

        $DRIVER_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $DRIVER_DOCUMENT_NOTE->where('company_driver_document_id', $company_driver_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
