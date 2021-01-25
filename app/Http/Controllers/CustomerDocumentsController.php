<?php

namespace App\Http\Controllers;

use App\CustomerDocument;
use Illuminate\Http\Request;

class CustomerDocumentsController extends Controller
{
    public function getDocuments(Request $request){
        $documents = CustomerDocument::all();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function getDocumentsByCustomer(Request $request){
        $customer_id = $request->customer_id;
        $documents = CustomerDocument::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    public function saveDocument(Request $request){
        $customer_id = $request->customer_id;
        $date_entered = isset($request->date_entered) ? $request->date_entered : '';
        $title = isset($request->title) ? $request->title : '';
        $subject = isset($request->subject) ? $request->subject : '';
        $tags = isset($request->tags) ? $request->tags : '';
        $user_id = isset($request->user_id) ? $request->user_id : 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = CustomerDocument::updateOrCreate([
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

        $documents = CustomerDocument::where('customer_id', $customer_id)->get();

        move_uploaded_file($fileData['tmp_name'], public_path('customer-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    public function deleteCustomerDocument(Request $request){
        $doc_id = $request->doc_id;
        $customer_id = $request->customer_id;

        $document = CustomerDocument::where('doc_id', $doc_id)->delete();

        unlink(public_path('customer-documents/' . $doc_id));

        $documents = CustomerDocument::where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }
}
