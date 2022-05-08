<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    public function getTemplates(Request $request){
        $name = isset($request->name) ? trim($request->name) : '';

        $templates = Template::whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'templates' => $templates]);
    }
}
