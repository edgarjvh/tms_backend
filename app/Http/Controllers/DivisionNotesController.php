<?php

namespace App\Http\Controllers;

use App\Models\DivisionNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionNotesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionNotes(Request $request): JsonResponse
    {
        $NOTE = new DivisionNote();
        $division_id = $request->division_id ?? 0;

        $notes = $NOTE->where('division_id', $division_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionNoteById(Request $request): JsonResponse
    {
        $NOTE = new DivisionNote();
        $id = $request->id ?? 0;

        $note = $NOTE->where('id', $id)->with(['user_code'])->first();

        return response()->json(['result' => 'OK', 'note' => $note]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionNote(Request $request): JsonResponse
    {
        $NOTE = new DivisionNote();
        $id = $request->id ?? 0;
        $division_id = $request->division_id ?? 0;
        $text = $request->text;
        $user_code_id = $request->user_code_id;

        if ($division_id > 0) {
            $note = $NOTE->updateOrCreate([
                'id' => $id
            ], [
                'division_id' => $division_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $note = $NOTE->where('id', $note->id)->with(['user_code'])->first();
            $notes = $NOTE->where('division_id', $division_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note, 'notes' => $notes]);
        } else {
            return response()->json(['result' => 'NO OWNER']);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDivisionNote(Request $request): JsonResponse
    {
        $NOTE = new DivisionNote();

        $id = $request->id ?? 0;
        $division_id = $request->division_id ?? 0;

        $NOTE->where('id', $id)->delete();

        $notes = $NOTE->where('division_id', $division_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes]);
    }
}
