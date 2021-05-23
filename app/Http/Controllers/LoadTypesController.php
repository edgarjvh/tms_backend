<?php

namespace App\Http\Controllers;

use App\LoadType;
use Illuminate\Http\Request;

class LoadTypesController extends Controller
{
    public function getLoadTypes(){
        $load_types = LoadType::orderBy('name', 'asc')->get();

        return response()->json(['result' => 'OK', 'load_types' => $load_types]);
    }
}
