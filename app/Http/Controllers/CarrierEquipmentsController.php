<?php

namespace App\Http\Controllers;

use App\Models\CarrierEquipment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarrierEquipmentsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCarrierEquipments(Request $request) : JsonResponse{
        $CARRIER_EQUIPMENT = new CarrierEquipment();
        $carrier_id = $request->carrier_id ?? 0;

        $sql = "SELECT e.name, ce.* FROM carrier_equipments AS ce LEFT JOIN equipments AS e ON ce.equipment_id = e.id WHERE ce.carrier_id = ?";

        $equipments_information = DB::select($sql, [$carrier_id]);        

        return response()->json(['result' => 'OK', 'equipments_information' => $equipments_information]);
    }


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

        $sql1 = "SELECT e.name, ce.* FROM carrier_equipments AS ce LEFT JOIN equipments AS e ON ce.equipment_id = e.id WHERE ce.id = ?";
        $sql2 = "SELECT e.name, ce.* FROM carrier_equipments AS ce LEFT JOIN equipments AS e ON ce.equipment_id = e.id WHERE ce.carrier_id = ?";

        $equipment_information = DB::select($sql1, [$equipment_information->id]);
        $equipments_information = DB::select($sql2, [$carrier_id]);

        return response()->json(['result' => 'OK', 'equipment_information' => $equipment_information[0], 'equipments_information' => $equipments_information]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCarrierEquipment(Request $request): JsonResponse{
        $CARRIER_EQUIPMENT = new CarrierEquipment();

        $id = $request->id ?? null;
        $carrier_id = $request->carrier_id ?? 0;

        $CARRIER_EQUIPMENT->where('id', $id)->delete();

        $sql = "SELECT e.name, ce.* FROM carrier_equipments AS ce LEFT JOIN equipments AS e ON ce.equipment_id = e.id WHERE ce.carrier_id = ?";
        $equipments_information = DB::select($sql, [$carrier_id]);   

        return response()->json(['result' => 'OK', 'equipments_information' => $equipments_information]);
    }
}
