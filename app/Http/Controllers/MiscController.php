<?php

namespace App\Http\Controllers;

use App\Equipment;
use App\Insurance;
use App\InsuranceType;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    public function getCarrierDropdownItems(){
        $types = InsuranceType::orderBy('name', 'ASC')->get();
        $companies = Insurance::orderBy('company', 'ASC')->groupBy(['company', 'id'])->get(['id', 'company']);
        $equipments = Equipment::orderBy('name', 'ASC')->get();

        return response()->json(['result' => 'OK', 'types' => $types, 'companies' => $companies, 'equipments' => $equipments]);
    }
}
