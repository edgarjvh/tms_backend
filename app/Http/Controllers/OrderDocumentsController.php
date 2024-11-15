<?php

namespace App\Http\Controllers;

use App\Models\OrderDocument;
use App\Models\OrderDocumentNote;
use App\Models\OrderBillingDocument;
use App\Models\OrderInvoiceCarrierDocument;
use App\Models\OrderBillingDocumentNote;
use App\Models\OrderInvoiceCarrierDocumentNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OrderDocumentsController extends Controller
{
    public function getDocumentsByOrder(Request $request){
        $ORDER_DOCUMENT = new OrderDocument();
        $order_id = $request->order_id;

        $documents = $ORDER_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveOrderDocument(Request $request){
        $ORDER_DOCUMENT = new OrderDocument();

        $order_id = $request->order_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $link = strtolower($request->link ?? '');
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++){

            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = $ORDER_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $document = $ORDER_DOCUMENT->where('id', $document->id)->with(['notes', 'user_code'])->first();
            $documents = $ORDER_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('order-documents/' . $doc_id));

            $ORDER_BILLING_DOCUMENT = new OrderBillingDocument();

            $billing_documents = [];

            if ($link === 'signed bol'){
                $new_doc_id = uniqid() . '.' . $doc_extension;

                $ORDER_BILLING_DOCUMENT->updateOrCreate([
                    'id' => 0
                ], [
                    'order_id' => $order_id,
                    'doc_id' => $new_doc_id,
                    'doc_name' => $doc_name,
                    'doc_extension' => $doc_extension,
                    'user_code_id' => $user_code_id,
                    'date_entered' => $date_entered,
                    'title' => $title,
                    'subject' => $subject,
                    'tags' => $tags
                ]);

                copy(public_path('order-documents/' . $doc_id), public_path('order-billing-documents/' . $new_doc_id));

                $billing_documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();
            }
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents, 'billing_documents' => $billing_documents]);
    }

    public function deleteOrderDocument(Request $request){
        $ORDER_DOCUMENT = new OrderDocument();
        $doc_id = $request->doc_id;
        $order_id = $request->order_id;

        $ORDER_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('order-documents/' . $doc_id))){
            try {
                unlink(public_path('order-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $ORDER_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByOrderDocument(Request $request){
        $ORDER_DOCUMENT_NOTE = new OrderDocumentNote();
        $doc_id = $request->doc_id;

        $documentNotes = $ORDER_DOCUMENT_NOTE->where('order_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveOrderDocumentNote(Request $request){
        $ORDER_DOCUMENT_NOTE = new OrderDocumentNote();
        $ORDER_DOCUMENT = new OrderDocument();
        $id = $request->id;
        $order_id = $request->order_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $ORDER_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'order_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNotes = $ORDER_DOCUMENT_NOTE->where('order_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $ORDER_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrderDocumentNote(Request $request) : JsonResponse{
        $ORDER_DOCUMENT_NOTE = new OrderDocumentNote();
        $id = $request->id ?? null;
        $order_document_id = $request->order_document_id ?? null;

        $ORDER_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $ORDER_DOCUMENT_NOTE->where('order_document_id', $order_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }

    public function getBillingDocumentsByOrder(Request $request){
        $ORDER_BILLING_DOCUMENT = new OrderBillingDocument();
        $order_id = $request->order_id;
        $documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getOrderBillingDocumentsByOrder(Request $request){
        $ORDER_BILLING_DOCUMENT = new OrderDocument();
        $order_id = $request->order_id;

        $documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveOrderBillingDocument(Request $request){
        $ORDER_BILLING_DOCUMENT = new OrderBillingDocument();
        $order_id = $request->order_id;
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

            $document = $ORDER_BILLING_DOCUMENT->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $document = $ORDER_BILLING_DOCUMENT->where('id', $document->id)->with(['notes', 'user_code'])->first();
            $documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('order-billing-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteOrderBillingDocument(Request $request){
        $ORDER_BILLING_DOCUMENT = new OrderBillingDocument();
        $doc_id = $request->doc_id;
        $order_id = $request->order_id;

        $ORDER_BILLING_DOCUMENT->where('doc_id', $doc_id)->delete();

        try {
            unlink(public_path('order-billing-documents/' . $doc_id));
        } catch (Throwable $e) {

        }

        $documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByOrderBillingDocument(Request $request){
        $ORDER_BILLING_DOCUMENT_NOTE = new OrderBillingDocumentNote();
        $doc_id = $request->doc_id;

        $documentNotes = $ORDER_BILLING_DOCUMENT_NOTE->where('order_billing_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveOrderBillingDocumentNote(Request $request){
        $ORDER_BILLING_DOCUMENT_NOTE = new OrderBillingDocumentNote();
        $ORDER_BILLING_DOCUMENT = new OrderBillingDocument();
        $id = $request->id;
        $order_id = $request->order_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $ORDER_BILLING_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'order_billing_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $ORDER_BILLING_DOCUMENT_NOTE->where('order_billing_document_id', $doc_id)->get();
        $documents = $ORDER_BILLING_DOCUMENT->where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrderBillingDocumentNote(Request $request) : JsonResponse{
        $ORDER_BILLING_DOCUMENT_NOTE = new OrderBillingDocumentNote();
        $id = $request->id ?? null;
        $order_billing_document_id = $request->order_billing_document_id ?? null;

        $ORDER_BILLING_DOCUMENT_NOTE->where('id',$id)->delete();

        $documentNotes = $ORDER_BILLING_DOCUMENT_NOTE->where('order_billing_document_id', $order_billing_document_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'data' => $documentNotes]);
    }

    public function getInvoiceCarrierDocumentsByOrder(Request $request){
        $order_id = $request->order_id;
        $documents = OrderInvoiceCarrierDocument::query()->where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveOrderInvoiceCarrierDocument(Request $request){
        $order_id = $request->order_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? null;
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++){
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            $document = OrderInvoiceCarrierDocument::query()->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'doc_id' => $doc_id,
                'doc_name' => $doc_name,
                'doc_extension' => $doc_extension,
                'user_code_id' => $user_code_id,
                'date_entered' => $date_entered,
                'title' => $title,
                'subject' => $subject,
                'tags' => $tags
            ]);

            $documents = OrderInvoiceCarrierDocument::query()->where('order_id', $order_id)->with('notes')->get();

            move_uploaded_file($fileData['tmp_name'][$i], public_path('order-invoice-carrier-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteOrderInvoiceCarrierDocument(Request $request){
        $doc_id = $request->doc_id;
        $order_id = $request->order_id;

        OrderInvoiceCarrierDocument::query()->where('doc_id', $doc_id)->delete();

        try {
            unlink(public_path('order-invoice-carrier-documents/' . $doc_id));
        } catch (Throwable $e) {

        }

        $documents = OrderInvoiceCarrierDocument::query()->where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getNotesByOrderInvoiceCarrierDocument(Request $request){
        $doc_id = $request->doc_id;

        $documentNotes = OrderInvoiceCarrierDocumentNote::query()->where('order_invoice_carrier_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    public function saveOrderInvoiceCarrierDocumentNote(Request $request){
        $note_id = $request->note_id;
        $order_id = $request->order_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = OrderInvoiceCarrierDocumentNote::query()->updateOrCreate([
            'id' => $note_id
        ], [
            'order_invoice_carrier_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $documentNotes = OrderInvoiceCarrierDocumentNote::query()->where('order_invoice_carrier_document_id', $doc_id)->get();
        $documents = OrderInvoiceCarrierDocument::query()->where('order_id', $order_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
