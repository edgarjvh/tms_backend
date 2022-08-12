<?php

namespace App\Http\Controllers;

use App\Models\AgentHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentHoursController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAgentHours(Request $request):JsonResponse
    {
        $HOURS = new AgentHour();
        $agent_id = $request->agent_id;

        $agent_hours = $HOURS->where('agent_id', $agent_id)->first();

        return response()->json(['result' => 'OK', 'agent_hours' => $agent_hours]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentHours(Request $request): JsonResponse
    {
        $HOURS = new AgentHour();

        $agent_id = $request->agent_id ?? 0;
        $hours_open = $request->hours_open ?? '';
        $hours_close = $request->hours_close ?? '';
        $hours_open2 = $request->hours_open2 ?? '';
        $hours_close2 = $request->hours_close2 ?? '';

        $cur_hours = $HOURS->where('agent_id', $agent_id)->first();

        $hours = $HOURS->updateOrCreate([
            'id' => $cur_hours ? $cur_hours->id : 0
        ], [
            'agent_id' => $agent_id,
            'hours_open' => $hours_open,
            'hours_open2' => $hours_open2,
            'hours_close' => $hours_close,
            'hours_close2' => $hours_close2
        ]);

        $hours = $HOURS->where('agent_id', $agent_id)->first();

        return response()->json(['result' => 'OK', 'hours' => $hours]);
    }
}
