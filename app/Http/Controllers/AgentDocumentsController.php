<?php

namespace App\Http\Controllers;

use App\Models\AgentDocument;
use App\Models\AgentDocumentNote;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AgentDocumentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocumentsByAgent(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT = new AgentDocument();

        $agent_id = $request->agent_id;
        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentDocument(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT = new AgentDocument();

        $agent_id = $request->agent_id;
        $date_entered = $request->date_entered ?? '';
        $title = $request->title ?? '';
        $subject = $request->subject ?? '';
        $tags = $request->tags ?? '';
        $user_code_id = $request->user_code_id ?? 0;
        $fileData = $_FILES['doc'];
        $doc_name = $fileData['name'];
        $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
        $doc_id = uniqid() . '.' . $doc_extension;

        $document = $AGENT_DOCUMENT->updateOrCreate([
            'id' => 0
        ], [
            'agent_id' => $agent_id,
            'doc_id' => $doc_id,
            'doc_name' => $doc_name,
            'doc_extension' => $doc_extension,
            'user_code_id' => $user_code_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with(['notes', 'user_code'])->get();

        move_uploaded_file($fileData['tmp_name'], public_path('agent-documents/' . $doc_id));

        return response()->json(['result' => 'OK', 'document' => $document, 'documents' => $documents]);
//        {"name":"generated.pdf","type":"application\/pdf","tmp_name":"C:\\xampp\\tmp\\php1753.tmp","error":0,"size":13213}
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgentDocument(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT = new AgentDocument();

        $doc_id = $request->doc_id;
        $agent_id = $request->agent_id;

        $AGENT_DOCUMENT->where('doc_id', $doc_id)->delete();

        if (file_exists(public_path('agent-documents/' . $doc_id))){
            try {
                unlink(public_path('agent-documents/' . $doc_id));
            } catch (Throwable | Exception $e) {
            }
        }

        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'documents' => $documents]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotesByAgentDocument(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT_NOTE = new AgentDocumentNote();

        $doc_id = $request->doc_id;

        $documentNotes = $AGENT_DOCUMENT_NOTE->where('company_agent_document_id', $doc_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentDocumentNote(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT = new AgentDocument();
        $AGENT_DOCUMENT_NOTE = new AgentDocumentNote();

        $id = $request->id;
        $agent_id = $request->agent_id;
        $doc_id = $request->doc_id;
        $user_code_id = $request->user_code_id;
        $text = $request->text;

        $documentNote = $AGENT_DOCUMENT_NOTE->updateOrCreate([
            'id' => $id
        ], [
            'company_agent_document_id' => $doc_id,
            'text' => $text,
            'user_code_id' => $user_code_id
        ]);

        $documentNotes = $AGENT_DOCUMENT_NOTE->where('company_agent_document_id', $doc_id)->with(['user_code'])->get();
        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with(['notes', 'user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $documentNote, 'notes' => $documentNotes, 'documents' => $documents]);
    }
}
