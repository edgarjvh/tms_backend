<?php

namespace App\Http\Controllers;

use App\Models\LoadType;
use Illuminate\Http\Request;

class LoadTypesController extends Controller
{
    public function getLoadTypes(Request $request){
        $name = isset($request->name) ? trim($request->name) : '';

        $load_types = LoadType::whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'load_types' => $load_types]);
    }
}
