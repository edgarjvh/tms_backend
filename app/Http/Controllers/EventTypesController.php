<?php

namespace App\Http\Controllers;

use App\EventType;
use Illuminate\Http\Request;

class EventTypesController extends Controller
{
    public function getEventTypes(){
        $event_types = EventType::orderBy('name', 'asc')->get();

        return response()->json(['result' => 'OK', 'event_types' => $event_types]);
    }
}
