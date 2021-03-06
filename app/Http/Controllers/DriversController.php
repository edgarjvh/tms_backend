<?php

namespace App\Http\Controllers;

use App\CarrierDriver;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    public function getDriversByCarrierId(Request $request){
        $carrier_id = $request->carrier_id;

        $drivers = CarrierDriver::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->get();

        return response()->json(['result' => 'OK', 'drivers' => $drivers]);
    }

    public function saveCarrierDriver(Request $request){
        $driver_id = isset($request->id) ? $request->id : 0;
        $carrier_id = $request->carrier_id;

        $first_name = isset($request->first_name) ? $request->first_name : '';
        $last_name = isset($request->last_name) ? $request->last_name : '';
        $phone = isset($request->phone) ? $request->phone : '';
        $email = isset($request->email) ? $request->email : '';
        $equipment_id = isset($request->equipment_id) ? $request->equipment_id : 0;
        $equipment = isset($request->equipment) ? $request->equipment : '';
        $truck = isset($request->truck) ? $request->truck : '';
        $trailer = isset($request->trailer) ? $request->trailer : '';
        $notes = isset($request->notes) ? $request->notes : '';

        $existDriver = CarrierDriver::where([
            'carrier_id' => $carrier_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
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
            $driver = CarrierDriver::updateOrCreate([
                'id' => $driver_id
            ], [
                'carrier_id' => $carrier_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'email' => $email,
                'equipment_id' => $equipment_id,
                'truck' => $truck,
                'trailer' => $trailer,
                'notes' => $notes,
            ]);

            $newDriver = CarrierDriver::where('id', $driver->id)
                ->with('carrier')
                ->has('carrier')
                ->with(['equipment'])
                ->first();

            $drivers = CarrierDriver::where('carrier_id', $carrier_id)
                ->with('carrier')
                ->has('carrier')
                ->with(['equipment'])
                ->orderBy('first_name', 'asc')
                ->get();

            return response()->json(['result' => 'OK', 'driver' => $newDriver, 'drivers' => $drivers]);
        }
    }

    public function deleteCarrierDriver(Request $request){
        $driver_id = isset($request->driver_id) ? $request->driver_id : (isset($request->id) ? $request->id : 0);
        $carrier_id = $request->carrier_id;

        $driver = CarrierDriver::where('id', $driver_id)->delete();

        $drivers = CarrierDriver::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->with(['equipment'])
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $driver, 'drivers' => $drivers]);
    }
}
