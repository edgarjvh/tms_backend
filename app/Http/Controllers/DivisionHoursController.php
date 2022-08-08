<?php

namespace App\Http\Controllers;

use App\Models\DivisionHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionHoursController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisionHours(Request $request):JsonResponse
    {
        $HOURS = new DivisionHour();
        $division_id = $request->division_id;

        $division_hours = $HOURS->where('division_id', $division_id)->first();

        return response()->json(['result' => 'OK', 'division_hours' => $division_hours]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDivisionHours(Request $request): JsonResponse
    {
        $HOURS = new DivisionHour();

        $division_id = $request->division_id ?? 0;
        $hours_open = $request->hours_open ?? '';
        $hours_close = $request->hours_close ?? '';
        $delivery_hours_open = $request->delivery_hours_open ?? '';
        $delivery_hours_close = $request->delivery_hours_close ?? '';
        $hours_open2 = $request->hours_open2 ?? '';
        $hours_close2 = $request->hours_close2 ?? '';
        $delivery_hours_open2 = $request->delivery_hours_open2 ?? '';
        $delivery_hours_close2 = $request->delivery_hours_close2 ?? '';

        $cur_hours = $HOURS->where('division_id', $division_id)->first();

        $hours = $HOURS->updateOrCreate([
            'id' => $cur_hours ? $cur_hours->id : 0
        ], [
            'division_id' => $division_id,
            'hours_open' => $hours_open,
            'hours_open2' => $hours_open2,
            'hours_close' => $hours_close,
            'hours_close2' => $hours_close2,
            'delivery_hours_open' => $delivery_hours_open,
            'delivery_hours_open2' => $delivery_hours_open2,
            'delivery_hours_close' => $delivery_hours_close,
            'delivery_hours_close2' => $delivery_hours_close2
        ]);

        $hours = $HOURS->where('division_id', $division_id)->first();

        return response()->json(['result' => 'OK', 'hours' => $hours]);
    }
}
