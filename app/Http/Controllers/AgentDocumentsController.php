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
        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with('notes')->get();

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
        $user_id = $request->user_id ?? 0;
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
            'user_id' => $user_id,
            'date_entered' => $date_entered,
            'title' => $title,
            'subject' => $subject,
            'tags' => $tags
        ]);

        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with('notes')->get();

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
        try {
            unlink(public_path('agent-documents/' . $doc_id));
        } catch (Throwable | Exception $e) {
        }

        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with('notes')->get();

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

        $documentNotes = $AGENT_DOCUMENT_NOTE->where('company_agent_document_id', $doc_id)->get();

        return response()->json(['result' => 'OK', 'documentNotes' => $documentNotes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentDocumentNote(Request $request): JsonResponse
    {
        $AGENT_DOCUMENT = new AgentDocument();
        $AGENT_DOCUMENT_NOTE = new AgentDocumentNote();

        $note_id = $request->note_id;
        $agent_id = $request->agent_id;
        $doc_id = $request->doc_id;
        $user = $request->user;
        $date_time = $request->date_time;
        $note = $request->text;

        $documentNote = $AGENT_DOCUMENT_NOTE->updateOrCreate([
            'id' => $note_id
        ], [
            'company_agent_document_id' => $doc_id,
            'text' => $note,
            'user' => $user,
            'date_time' => $date_time
        ]);

        $documentNotes = $AGENT_DOCUMENT_NOTE->where('company_agent_document_id', $doc_id)->get();
        $documents = $AGENT_DOCUMENT->where('agent_id', $agent_id)->with('notes')->get();

        return response()->json(['result' => 'OK', 'documentNote' => $documentNote, 'data' => $documentNotes, 'documents' => $documents]);
    }
}
