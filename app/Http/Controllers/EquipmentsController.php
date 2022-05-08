<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEquipments(Request $request): JsonResponse
    {
        $EQUIPMENT = new Equipment();

        $name = $request->name ?? '';

        $equipments = $EQUIPMENT->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '%$name%'")
            ->orderBy('name')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments]);
    }
}
