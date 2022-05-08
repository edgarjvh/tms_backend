<?php

namespace App\Http\Controllers;

use App\Models\CarrierEquipment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierEquipmentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierEquipment(Request $request) : JsonResponse
    {
        $CARRIER_EQUIPMENT = new CarrierEquipment();

        $id = empty($request->id) ? null : $request->id;
        $carrier_id = $request->carrier_id ?? 0;
        $equipment_id = $request->equipment_id ?? 0;
        $units = $request->units ?? '';
        $equipment_length = $request->equipment_length ?? '';
        $equipment_length_unit = $request->equipment_length_unit ?? '';
        $equipment_width = $request->equipment_width ?? '';
        $equipment_width_unit = $request->equipment_width_unit ?? '';
        $equipment_height = $request->equipment_height ?? '';
        $equipment_height_unit = $request->equipment_height_unit ?? '';

        $equipment_information = $CARRIER_EQUIPMENT->updateOrCreate([
            'id' => $id
        ], [
            'carrier_id' => $carrier_id,
            'equipment_id' => $equipment_id,
            'units' => $units,
            'equipment_length' => $equipment_length,
            'equipment_length_unit' => $equipment_length_unit,
            'equipment_width' => $equipment_width,
            'equipment_width_unit' => $equipment_width_unit,
            'equipment_height' => $equipment_height,
            'equipment_height_unit' => $equipment_height_unit
        ]);

        $equipments_information = $CARRIER_EQUIPMENT->where('carrier_id', $carrier_id)->with(['equipment'])->get();

        return response()->json(['result' => 'OK', 'equipment_information' => $equipment_information, 'equipments_information' => $equipments_information]);
    }
}
