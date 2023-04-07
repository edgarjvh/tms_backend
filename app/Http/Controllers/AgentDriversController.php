<?php

namespace App\Http\Controllers;

use App\Models\AgentDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @param Request $request
 * @return JsonResponse
 */
class AgentDriversController extends Controller
{
    public function getAgentDriverByCode(Request $request): JsonResponse
    {
        $AGENT_DRIVER = new AgentDriver();
        $code = $request->code ?? null;

        $driver = $AGENT_DRIVER->where('code', $code)->first();

        if ($driver){
            return response()->json(['result' => 'OK', 'driver' => $driver]);
        }else{
            return response()->json(['result' => 'NO DRIVER']);
        }
    }

    public function getDriversByAgentId(Request $request): JsonResponse
    {
        $AGENT_DRIVER = new AgentDriver();

        $agent_id = $request->agent_id ?? 0;

        $drivers = $AGENT_DRIVER->whereRaw("1 = 1")
            ->whereRaw("agent_id = $agent_id")
            ->with(['agent', 'equipment'])
            ->has('agent')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAgentDriver(Request $request): JsonResponse
    {
        $AGENT_DRIVER = new AgentDriver();

        $driver_id = $request->id ?? 0;
        $agent_id = $request->agent_id;
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';
        $equipment_id = $request->equipment_id ?? null;
        $truck = $request->truck ?? '';
        $trailer = $request->trailer ?? '';
        $notes = $request->notes ?? '';

        if (trim($first_name) === '') {
            return response()->json(['result' => 'NO DRIVER FIRST NAME']);
        }

        $existDriver = $AGENT_DRIVER->where([
            'agent_id' => $agent_id,
            'first_name' => ucwords(trim($first_name)),
            'last_name' => ucwords(trim($last_name)),
            'phone' => $phone,
            'email' => strtolower($email),
            'equipment_id' => $equipment_id,
            'truck' => $truck,
            'trailer' => $trailer,
            'notes' => $notes
        ])->first();


        if ($agent_id === 0 && $existDriver) {
            return response()->json(['result' => 'DUPLICATE']);
        } else {
            $driver = $AGENT_DRIVER->updateOrCreate([
                'id' => $driver_id
            ], [
                'agent_id' => $agent_id,
                'first_name' => ucwords(trim($first_name)),
                'last_name' => ucwords(trim($last_name)),
                'phone' => $phone,
                'email' => strtolower($email),
                'equipment_id' => $equipment_id,
                'truck' => $truck,
                'trailer' => $trailer,
                'notes' => $notes,
            ]);

            $newDriver = $AGENT_DRIVER->where('id', $driver->id)
                ->with('agent')
                ->has('agent')
                ->with(['equipment'])
                ->first();

            $drivers = $AGENT_DRIVER->where('agent_id', $agent_id)
                ->with('agent')
                ->has('agent')
                ->with(['equipment'])
                ->orderBy('first_name')
                ->get();

            return response()->json(['result' => 'OK', 'driver' => $newDriver, 'drivers' => $drivers]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAgentDriver(Request $request): JsonResponse
    {
        $AGENT_DRIVER = new AgentDriver();

        $driver_id = $request->driver_id ?? ($request->id ?? 0);
        $agent_id = $request->agent_id;

        $driver = $AGENT_DRIVER->where('id', $driver_id)->delete();

        $drivers = $AGENT_DRIVER->where('agent_id', $agent_id)
            ->with('agent')
            ->has('agent')
            ->with(['equipment'])
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $driver, 'drivers' => $drivers]);
    }
}
