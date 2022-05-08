<?php

namespace App\Http\Controllers;

use App\Models\CustomerDocument;
use App\Models\CustomerDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CustomerDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByCustomer(Request $request): JsonResponse
    {
        $CUSTOMER_DOCUMENT = new CustomerDocument();

        $customer_id = $request->customer_id;
        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerDocument(Request $request): JsonResponse
    {
        $CUSTOMER_DOCUMENT = new CustomerDocument();

        $customer_id = $request->customer_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_id = $request->user_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $CUSTOMER_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'customer_id' => $customer_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with('notes')->get();

        move_uploaded_file($fileData['tmp_name'], public_path('customer-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerDocument(Request $request): JsonResponse
    {
        $CUSTOMER_DOCUMENT = new CustomerDocument();

        $doc_id = $request->doc_id;
        $customer_id = $request->customer_id;

        $CUSTOMER_DOCUMENT->where('doc_id', $doc_id)->delete();
        try {
            unlink(public_path('customer-documents/' . $doc_id));
        } catch (Throwable | Exception $e) {
        }

        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByCustomerDocument(Request $request): JsonResponse
    {
        $CUSTOMER_DOCUMENT_NOTE = new CustomerDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $CUSTOMER_DOCUMENT_NOTE->where('customer_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerDocumentNote(Request $request): JsonResponse
    {
        $CUSTOMER_DOCUMENT = new CustomerDocument();
        $CUSTOMER_DOCUMENT_NOTE = new CustomerDocumentNote();

        $note_id = $request->note_id;
        $customer_id = $request->customer_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = $CUSTOMER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $note_id
        ], [
            'customer_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = $CUSTOMER_DOCUMENT_NOTE->where('customer_document_id', $doc_id)->get();
        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
