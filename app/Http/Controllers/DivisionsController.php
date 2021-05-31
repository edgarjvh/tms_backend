<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    public function getDivisions(Request $request){
        $name = isset($request->name) ? trim($request->name) : '';

        $divisions = Division::whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name', 'ASC')
            ->get();

        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }
}
