<?php

namespace App\Http\Controllers;

use App\Models\Accessorial;
use App\Models\HandlingUnit;
use App\Models\HazmatClass;
use App\Models\HazmatPackaging;
use App\Models\Order;
use App\Models\OrderLtlUnit;
use App\Models\UnitClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderLtlUnitsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLtlUnitById(Request $request): JsonResponse
    {
        $id = $request->id ?? null;

        $LTL = new OrderLtlUnit();

        $ltl_unit = $LTL->where('id', $id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->first();

        return response()->json(['result' => 'OK', 'ltl_unit' => $ltl_unit]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLtlUnitsByOrderId(Request $request): JsonResponse
    {
        $order_id = $request->order_id ?? null;

        $LTL = new OrderLtlUnit();

        $ltl_units = $LTL->where('order_id', $order_id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->get();

        return response()->json(['result' => 'OK', 'ltl_units' => $ltl_units]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLtlUnitsAccessorialsByOrderId(Request $request): JsonResponse
    {
        $order_id = $request->order_id ?? null;
        $order = Order::find($order_id);
        $LTL = new OrderLtlUnit();

        $ltl_units = $LTL->where('order_id', $order_id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->get();

        $accessorials = $order->accessorials;

        return response()->json(['result' => 'OK', 'ltl_units' => $ltl_units, 'accessorials' => $accessorials]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveLtlUnit(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $order_id = $request->order_id ?? null;
        $hazmat = $request->hazmat ?? 0;
        $units = $request->units ?? null;
        $pieces = $request->pieces ?? null;
        $handling_unit_id = $request->handling_unit_id ?? null;
        $weight = $request->weight ?? 0.00;
        $weight_unit = $request->weight_unit ?? 'lb';
        $length = $request->length ?? 0.00;
        $width = $request->width ?? 0.00;
        $height = $request->height ?? 0.00;
        $dimension_unit = $request->dimension_unit ?? 'ft';
        $unit_class_id = $request->unit_class_id ?? null;
        $nmfc = $request->nmfc ?? null;
        $description = $request->description ?? null;
        $hazmat_name = $hazmat > 0 ? $request->hazmat_name ?? null : null;
        $hazmat_packaging_id = $hazmat > 0 ? $request->hazmat_packaging_id ?? null : null;
        $hazmat_un_na = $hazmat > 0 ? $request->hazmat_un_na ?? null : null;
        $hazmat_group = $hazmat > 0 ? $request->hazmat_group ?? null : null;
        $hazmat_class_id = $hazmat > 0 ? $request->hazmat_class_id ?? null : null;
        $emergency_contact = $hazmat > 0 ? $request->emergency_contact ?? null : null;
        $emergency_phone = $hazmat > 0 ? $request->emergency_phone ?? null : null;

        $LTL = new OrderLtlUnit();

        $ltl = $LTL->updateOrCreate([
            'id' => $id
        ], [
            'order_id' => $order_id,
            'hazmat' => $hazmat,
            'units' => $units,
            'pieces' => $pieces,
            'handling_unit_id' => $handling_unit_id,
            'weight' => $weight,
            'weight_unit' => $weight_unit,
            'length' => $length,
            'width' => $width,
            'height' => $height,
            'dimension_unit' => $dimension_unit,
            'unit_class_id' => $unit_class_id,
            'nmfc' => $nmfc,
            'description' => $description,
            'hazmat_name' => $hazmat_name,
            'hazmat_packaging_id' => $hazmat_packaging_id,
            'hazmat_un_na' => $hazmat_un_na,
            'hazmat_group' => $hazmat_group,
            'hazmat_class_id' => $hazmat_class_id,
            'emergency_contact' => $emergency_contact,
            'emergency_phone' => $emergency_phone
        ]);

        $ltl_units = $LTL->where('order_id', $order_id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->get();
        $ltl_unit = $LTL->where('id', $ltl->id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->first();

        return response()->json(['result' => 'OK', 'ltl_units' => $ltl_units, 'ltl_unit' => $ltl_unit]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteLtlUnit(Request $request): JsonResponse
    {
        $id = $request->id ?? null;
        $order_id = $request->order_id ?? null;

        $LTL = new OrderLtlUnit();

        $ltl = $LTL->where('id', $id)->delete();

        $ltl_units = $LTL->where('order_id', $order_id)->with([
            'handling_unit',
            'unit_class',
            'hazmat_packaging',
            'hazmat_class'
        ])->get();

        return response()->json(['result' => 'OK', 'ltl_units' => $ltl_units]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getHandlingUnits(Request $request): JsonResponse
    {
        $name = $request->name ?? '';
        $handling_units = HandlingUnit::where('name', 'like', "$name%")->get();

        return response()->json(['result' => 'OK', 'handling_units' => $handling_units]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUnitClasses(Request $request): JsonResponse
    {
        $name = $request->name ?? '';
        $unit_classes = UnitClass::where('name', 'like', "$name%")->get();

        return response()->json(['result' => 'OK', 'unit_classes' => $unit_classes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getHazmatPackagings(Request $request): JsonResponse
    {
        $name = $request->name ?? '';
        $hazmat_packagings = HazmatPackaging::where('name', 'like', "$name%")->get();

        return response()->json(['result' => 'OK', 'hazmat_packagings' => $hazmat_packagings]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getHazmatClasses(Request $request): JsonResponse
    {
        $name = $request->name ?? '';
        $hazmat_classes = HazmatClass::where('name', 'like', "$name%")->get();

        return response()->json(['result' => 'OK', 'hazmat_classes' => $hazmat_classes]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccessorials(Request $request): JsonResponse
    {
        $name = $request->name ?? '';
        $accessorials = Accessorial::where('name', 'like', "$name%")->get();

        return response()->json(['result' => 'OK', 'accessorials' => $accessorials]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderAccessorials(Request $request): JsonResponse
    {
        $order_id = $request->order_id ?? null;
        $accessorials = $request->accessorials ?? [];

        $order = Order::find($order_id);
        $order->accessorials()->sync($accessorials);

        $accessorials = $order->accessorials;

        return response()->json(['result' => 'OK', 'accessorials' => $accessorials]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrderAccessorial(Request $request): JsonResponse
    {
        $order_id = $request->order_id ?? null;
        $accessorial_id = $request->accessorial_id ?? null;

        $order = Order::find($order_id);
        $order->accessorials()->detach($accessorial_id);

        $accessorials = $order->accessorials;

        return response()->json(['result' => 'OK', 'accessorials' => $accessorials]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmergencyContacts(Request $request): JsonResponse
    {
        $name = $request->name ?? '';

        $emergency_contacts = OrderLtlUnit::where('emergency_contact', 'like', "%$name%")
            ->distinct()
            ->orderBy('emergency_contact')
            ->get(['emergency_contact', 'emergency_phone']);

        return response()->json(['result' => 'OK', 'emergency_contacts' => $emergency_contacts]);
    }
}
