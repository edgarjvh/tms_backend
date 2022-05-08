<?php

namespace App\Http\Controllers;

use App\Models\CarrierDriver;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDriversByCarrierId(Request $request) : JsonResponse
    {
        $CARRIER_DRIVER = new CarrierDriver();

        $carrier_id = $request->carrier_id ?? 0;
        $name = $request->name ?? '';



        $count = count($CARRIER_DRIVER->where('carrier_id', $carrier_id)->get());

        $drivers = $CARRIER_DRIVER->whereRaw("1 = 1")
            ->whereRaw("carrier_id = $carrier_id")
            ->whereRaw("concat(first_name, ' ', last_name) like '%$name%'")
            ->with(['carrier', 'equipment'])
            ->has('carrier')
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers, 'count' => $count]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCarrierDriver(Request $request) :JsonResponse
    {
        $CARRIER_DRIVER = new CarrierDriver();

        $driver_id = $request->id ?? 0;
        $carrier_id = $request->carrier_id;
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $phone = $request->phone ?? '';
        $email = $request->email ?? '';
        $equipment_id = $request->equipment_id ?? 0;
        $truck = $request->truck ?? '';
        $trailer = $request->trailer ?? '';
        $notes = $request->notes ?? '';

        if (trim($first_name) === ''){
            return response()->json(['result' => 'NO DRIVER FIRST NAME']);
        }

        $existDriver = $CARRIER_DRIVER->where([
            'carrier_id' => $carrier_id,
            'first_name' => trim($first_name),
            'last_name' => trim($last_name),
            'phone' => $phone,
            'email' => $email,
            'equipment_id' => $equipment_id,
            'truck' => $truck,
            'trailer' => $trailer,
            'notes' => $notes
        ])->first();


        if ($carrier_id === 0 && $existDriver){
            return response()->json(['result' => 'DUPLICATE']);
        }else{
            $driver = $CARRIER_DRIVER->updateOrCreate([
                'id' => $driver_id
            ], [
                'carrier_id' => $carrier_id,
                'first_name' => trim($first_name),
                'last_name' => trim($last_name),
                'phone' => $phone,
                'email' => $email,
                'equipment_id' => $equipment_id,
                'truck' => $truck,
                'trailer' => $trailer,
                'notes' => $notes,
            ]);

            $newDriver = $CARRIER_DRIVER->where('id', $driver->id)
                ->with('carrier')
                ->has('carrier')
                ->with(['equipment'])
                ->first();

            $drivers = $CARRIER_DRIVER->where('carrier_id', $carrier_id)
                ->with('carrier')
                ->has('carrier')
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
    public function deleteCarrierDriver(Request $request) : JsonResponse
    {
        $CARRIER_DRIVER = new CarrierDriver();

        $driver_id = $request->driver_id ?? ($request->id ?? 0);
        $carrier_id = $request->carrier_id;

        $driver = $CARRIER_DRIVER->where('id', $driver_id)->delete();

        $drivers = $CARRIER_DRIVER->where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->with(['equipment'])
            ->orderBy('first_name')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $driver, 'drivers' => $drivers]);
    }
}
