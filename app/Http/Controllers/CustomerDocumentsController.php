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
        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with(['notes', 'user_code'])->get();

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
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++){
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = $CUSTOMER_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'customer_id' => $customer_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('customer-documents/' . $doc_id));
        }

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

        if (file_exists(public_path('customer-documents/' . $doc_id))){
            try {
                unlink(public_path('customer-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with(['notes', 'user_code'])->get();

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

        $documentNotes = $CUSTOMER_DOCUMENT_NOTE->where('customer_document_id', $doc_id)->with(['user_code'])->get();

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

        $id = $request->id;
        $customer_id = $request->customer_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $CUSTOMER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'customer_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $CUSTOMER_DOCUMENT_NOTE->where('customer_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $CUSTOMER_DOCUMENT->where('customer_id', $customer_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerDocumentNote(Request $request) : JsonResponse{
        $CUSTOMER_DOCUMENT_NOTE = new CustomerDocumentNote();
        $id = $request->id ?? null;
        $customer_document_id = $request->customer_document_id ?? null;

        $CUSTOMER_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $CUSTOMER_DOCUMENT_NOTE->where('customer_document_id', $customer_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }
}
