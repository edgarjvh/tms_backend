<?php

namespace App\Http\Controllers;

use App\Equipment;
use Illuminate\Http\Request;

class EquipmentsController extends Controller
{
    public function getEquipments(Request $request)
    {
        $name = isset($request->name) ? $request->name : '';

        $equipments = Equipment::whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->orderBy('name', 'ASC')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments]);
    }
}
