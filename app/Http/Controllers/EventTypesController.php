<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventTypesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEventTypes(Request $request):JsonResponse
    {
        $EVENT_TYPE = new EventType();
        $name = strtolower($request->name ?? '');
        $event_types = $EVENT_TYPE->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'event_types' => $event_types, 'name' => $name]);
    }
}
