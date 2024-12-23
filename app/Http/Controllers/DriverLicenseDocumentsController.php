<?php

namespace App\Http\Controllers;

use App\Models\DriverLicenseDocument;
use App\Models\DriverLicenseDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DriverLicenseDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByDriverLicense(Request $request): JsonResponse
    {
        $LICENSE_DOCUMENT = new DriverLicenseDocument();

        $driver_license_id = $request->driver_license_id;
        $documents = $LICENSE_DOCUMENT->where('driver_license_id', $driver_license_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverLicenseDocument(Request $request): JsonResponse
    {
        $LICENSE_DOCUMENT = new DriverLicenseDocument();

        $driver_license_id = $request->driver_license_id;
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

            $document = $LICENSE_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'driver_license_id' => $driver_license_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $LICENSE_DOCUMENT->where('driver_license_id', $driver_license_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('driver-license-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverLicenseDocument(Request $request): JsonResponse
    {
        $LICENSE_DOCUMENT = new DriverLicenseDocument();

        $doc_id = $request->doc_id;
        $driver_license_id = $request->driver_license_id;

        $LICENSE_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('driver-license-documents/' . $doc_id))){
            try {
                unlink(public_path('driver-license-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $LICENSE_DOCUMENT->where('driver_license_id', $driver_license_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByDriverLicenseDocument(Request $request): JsonResponse
    {
        $LICENSE_DOCUMENT_NOTE = new DriverLicenseDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $LICENSE_DOCUMENT_NOTE->where('driver_license_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDriverLicenseDocumentNote(Request $request): JsonResponse
    {
        $LICENSE_DOCUMENT = new DriverLicenseDocument();
        $LICENSE_DOCUMENT_NOTE = new DriverLicenseDocumentNote();

        $id = $request->id;
        $driver_license_id = $request->driver_license_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $LICENSE_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'driver_license_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNotes = $LICENSE_DOCUMENT_NOTE->where('driver_license_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $LICENSE_DOCUMENT->where('driver_license_id', $driver_license_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDriverLicenseDocumentNote(Request $request) : JsonResponse{
        $LICENSE_DOCUMENT_NOTE = new DriverLicenseDocumentNote();
        $id = $request->id ?? null;
        $LICENSE_DOCUMENT_id = $request->driver_license_document_id ?? null;

        $LICENSE_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $LICENSE_DOCUMENT_NOTE->where('driver_license_document_id', $LICENSE_DOCUMENT_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
