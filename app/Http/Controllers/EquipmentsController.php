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
        $withAll = $request->withAll ?? 0;

        $equipments = $EQUIPMENT->whereRaw("1 = 1")
            ->whereRaw("LOWER(name) like '$name%'")
            ->orderBy('name')->get();

        if ($withAll === 1) {
            $equipments->prepend(['id' => -1, 'name' => 'All']);
        }

        return response()->json(['result' => 'OK', 'equipments' => $equipments]);
    }
}
