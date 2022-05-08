<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function getTerms(Request $request){
        $name = isset($request->name) ? trim($request->name) : '';

        $terms = Term::whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'terms' => $terms]);
    }
}
