<?php

namespace App\Http\Controllers;

use App\Models\InternalNotes;
use App\Models\NotesForCarrier;
use App\Models\NotesForDriver;
use App\Models\OrderBillingNote;
use App\Models\OrderInvoiceInternalNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class DispatchNotesController extends Controller
{
    /**
     * @throws Exception
     */
    public function getDispatchNotes() : JsonResponse
    {
        $NOTES_FOR_CARRIER = new NotesForCarrier();
        $INTERNAL_NOTES = new InternalNotes();

        $internal_notes = $INTERNAL_NOTES->with(['user_code'])->get();
        $notes_for_carrier = $NOTES_FOR_CARRIER->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes_for_carrier' => $notes_for_carrier, 'internal_notes' => $internal_notes]);
    }

    /**
     * @throws Exception
     */
    public function getInternalNotes() : JsonResponse
    {
        $INTERNAL_NOTES = new InternalNotes();

        $internal_notes = $INTERNAL_NOTES->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $internal_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveInternalNotes(Request $request) : JsonResponse
    {
        $INTERNAL_NOTES = new InternalNotes();
        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        $internal_note = $INTERNAL_NOTES->updateOrCreate([
            'id' => $id
        ], [
            'order_id' => $order_id,
            'user_code_id' => $user_code_id,
            'text' => $text,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $internal_notes = $INTERNAL_NOTES->where('order_id', $order_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $internal_note, 'notes' => $internal_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteInternalNotes(Request $request) : JsonResponse
    {
        $INTERNAL_NOTES = new InternalNotes();

        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;

        $internal_note = $INTERNAL_NOTES->where('id', $id)->delete();

        $internal_notes = $INTERNAL_NOTES->where('order_id', $order_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $internal_note, 'notes' => $internal_notes]);
    }

    /**
     * @throws Exception
     */
    public function getNotesForCarrier() : JsonResponse
    {
        $NOTES_FOR_CARRIER = new NotesForCarrier();

        $notes_for_carrier = $NOTES_FOR_CARRIER->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes_for_carrier]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveNotesForCarrier(Request $request) : JsonResponse
    {
        $NOTES_FOR_CARRIER = new NotesForCarrier();

        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        if ($order_id > 0){
            $note_for_carrier = $NOTES_FOR_CARRIER->updateOrCreate([
                'id' => $id
            ], [
                'order_id' => $order_id,
                'user_code_id' => $user_code_id,
                'text' => $text,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $notes_for_carrier = $NOTES_FOR_CARRIER->where('order_id', $order_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note_for_carrier, 'notes' => $notes_for_carrier]);
        }else{
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteNotesForCarrier(Request $request) : JsonResponse
    {
        $NOTES_FOR_CARRIER = new NotesForCarrier();

        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;

        $note_for_carrier = $NOTES_FOR_CARRIER->where('id', $id)->delete();

        $notes_for_carrier = $NOTES_FOR_CARRIER->where('order_id', $order_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $note_for_carrier, 'notes' => $notes_for_carrier]);
    }

    /**
     * @throws Exception
     */
    public function getNotesForDriver() : JsonResponse
    {
        $NOTES_FOR_DRIVER = new NotesForDriver();

        $notes_for_driver = $NOTES_FOR_DRIVER->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $notes_for_driver]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveNotesForDriver(Request $request) : JsonResponse
    {
        $NOTES_FOR_DRIVER = new NotesForDriver();

        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        if ($order_id > 0){
            $note_for_driver = $NOTES_FOR_DRIVER->updateOrCreate([
                'id' => $id
            ], [
                'order_id' => $order_id,
                'user_code_id' => $user_code_id,
                'text' => $text,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $notes_for_driver = $NOTES_FOR_DRIVER->where('order_id', $order_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $note_for_driver, 'notes' => $notes_for_driver]);
        }else{
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteNotesForDriver(Request $request) : JsonResponse
    {
        $NOTES_FOR_DRIVER = new NotesForDriver();

        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;

        $note_for_driver = $NOTES_FOR_DRIVER->where('id', $id)->delete();

        $notes_for_driver = $NOTES_FOR_DRIVER->where('order_id', $order_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $note_for_driver, 'notes' => $notes_for_driver]);
    }

    /**
     * @throws Exception
     */
    public function getOrderInvoiceInternalNotes() : JsonResponse
    {
        $INTERNAL_NOTES = OrderInvoiceInternalNote::query();

        $internal_notes = $INTERNAL_NOTES->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $internal_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderInvoiceInternalNotes(Request $request) : JsonResponse
    {
        $INTERNAL_NOTES = OrderInvoiceInternalNote::query();

        $order_id = $request->order_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        $internal_note = $INTERNAL_NOTES->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'user_code_id' => $user_code_id,
            'text' => $text
        ]);

        $query1 = OrderInvoiceInternalNote::query();
        $query1->where('order_id', $order_id);
        $data = $query1->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $internal_note, 'notes' => $data]);
    }

    /**
     * @throws Exception
     */
    public function getOrderBillingNotes() : JsonResponse
    {
        $BILLING_NOTES = OrderBillingNote::query();

        $billing_notes = $BILLING_NOTES->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $billing_notes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderBillingNotes(Request $request) : JsonResponse
    {
        $BILLING_NOTES = OrderBillingNote::query();

        $order_id = $request->order_id ?? 0;
        $user_code_id = $request->user_code_id ?? '';
        $text = $request->text ?? '';

        $billing_note = $BILLING_NOTES->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'user_code_id' => $user_code_id,
            'text' => $text,
            'date_time' => date('Y-m-d H:i:s')
        ]);

        $query1 = OrderBillingNote::query();
        $query1->where('order_id', $order_id);
        $data = $query1->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'note' => $billing_note, 'notes' => $data]);
    }
}
