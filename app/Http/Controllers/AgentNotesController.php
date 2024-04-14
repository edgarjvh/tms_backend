<?php

namespace App\Http\Controllers;

use App\Models\AgentNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentNotesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentNotes(Request $request): JsonResponse
    {
        $NOTE = new AgentNote();
        $agent_id = $request->agent_id ?? 0;

        $notes = $NOTE->where('agent_id', $agent_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentNoteById(Request $request): JsonResponse
    {
        $NOTE = new AgentNote();
        $id = $request->id ?? 0;

        $note = $NOTE->where('id', $id)->with(['user_code'])->first();

        return response()->json(['result' => 'OK', 'note' => $note]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentNote(Request $request): JsonResponse
    {
        $NOTE = new AgentNote();
        $id = $request->id ?? 0;
        $agent_id = $request->agent_id ?? 0;
        $text = $request->text;
        $user_code_id = $request->user_code_id;

        if ($agent_id > 0) {
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'agent_id' => $agent_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $note = $NOTE->where('id', $note->id)->with(['user_code'])->first();
            $notes = $NOTE->where('agent_id', $agent_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        } else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgentNote(Request $request): JsonResponse
    {
        $NOTE = new AgentNote();

        $id = $request->id ?? 0;
        $agent_id = $request->agent_id ?? 0;

        $NOTE->where('id', $id)->delete();

        $notes = $NOTE->where('agent_id', $agent_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }
}
