<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    public function getDivisions(){
        $divisions = Division::orderBy('name', 'asc')->get();

        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }
}
