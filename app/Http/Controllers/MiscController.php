<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Insurance;
use App\Models\InsuranceType;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function getCarrierDropdownItems(){
        $types = InsuranceType::orderBy('name')->get();
        $companies = Insurance::orderBy('company')->groupBy(['company', 'id'])->get(['id', 'company']);
        $equipments = Equipment::orderBy('name')->get();

        return response()->json(['result' => 'OK', 'types' => $types, 'companies' => $companies, 'equipments' => $equipments]);
    }
}
