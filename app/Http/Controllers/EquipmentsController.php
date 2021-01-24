<?php

namespace App\Http\Controllers;

use App\Equipment;
use Illuminate\Http\Request;

class EquipmentsController extends Controller
{
    public function getEquipments(){
        $equipments = Equipment::orderBy('name', 'ASC')->get();

        return response()->json(['result' => 'OK', 'equipments' => $equipments]);
    }
}
