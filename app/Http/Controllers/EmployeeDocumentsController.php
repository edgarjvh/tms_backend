<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EmployeeDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByEmployee(Request $request): JsonResponse
    {
        $EMPLOYEE_DOCUMENT = new EmployeeDocument();

        $employee_id = $request->employee_id;
        $documents = $EMPLOYEE_DOCUMENT->where('employee_id', $employee_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveEmployeeDocument(Request $request): JsonResponse
    {
        $EMPLOYEE_DOCUMENT = new EmployeeDocument();

        $employee_id = $request->employee_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $EMPLOYEE_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'employee_id' => $employee_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_code_id' => $user_code_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $EMPLOYEE_DOCUMENT->where('employee_id', $employee_id)->with(['notes', 'user_code'])->get();

        move_uploaded_file($fileData['tmp_name'], public_path('employee-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteEmployeeDocument(Request $request): JsonResponse
    {
        $EMPLOYEE_DOCUMENT = new EmployeeDocument();

        $doc_id = $request->doc_id;
        $employee_id = $request->employee_id;

        $EMPLOYEE_DOCUMENT->where('doc_id', $doc_id)->delete();
        try {
            unlink(public_path('employee-documents/' . $doc_id));
        } catch (Throwable | Exception $e) {
        }

        $documents = $EMPLOYEE_DOCUMENT->where('employee_id', $employee_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByEmployeeDocument(Request $request): JsonResponse
    {
        $EMPLOYEE_DOCUMENT_NOTE = new EmployeeDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $EMPLOYEE_DOCUMENT_NOTE->where('company_employee_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveEmployeeDocumentNote(Request $request): JsonResponse
    {
        $EMPLOYEE_DOCUMENT = new EmployeeDocument();
        $EMPLOYEE_DOCUMENT_NOTE = new EmployeeDocumentNote();

        $id = $request->id;
        $employee_id = $request->employee_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $EMPLOYEE_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_employee_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $EMPLOYEE_DOCUMENT_NOTE->where('company_employee_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $EMPLOYEE_DOCUMENT->where('employee_id', $employee_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $documentNote, 'notes' => $documentNotes, 'documents' => $documents]);
    }
}
