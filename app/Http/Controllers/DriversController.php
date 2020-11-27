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
        $driver_id = isset($request->driver_id) ? $request->driver_id : 0;
        $carrier_id = $request->carrier_id;

        $first_name = isset($request->first_name) ? $request->first_name : '';
        $last_name = isset($request->last_name) ? $request->last_name : '';
        $phone = isset($request->phone) ? $request->phone : '';
        $email = isset($request->email) ? $request->email : '';
        $equipment = isset($request->equipment) ? $request->equipment : '';
        $truck = isset($request->truck) ? $request->truck : '';
        $trailer = isset($request->trailer) ? $request->trailer : '';
        $notes = isset($request->notes) ? $request->notes : '';

        $driver = CarrierDriver::updateOrCreate([
            'id' => $driver_id
        ], [
            'carrier_id' => $carrier_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'equipment' => $equipment,
            'truck' => $truck,
            'trailer' => $trailer,
            'notes' => $notes,
        ]);

        $newDriver = CarrierDriver::where('id', $driver->id)
            ->with('carrier')
            ->has('carrier')
            ->first();

        $drivers = CarrierDriver::where('carrier_id', $carrier_id)
            ->with('carrier')
            ->has('carrier')
            ->orderBy('last_name', 'asc')
            ->get();

        return response()->json(['result' => 'OK', 'driver' => $newDriver, 'drivers' => $drivers]);
    }
}
