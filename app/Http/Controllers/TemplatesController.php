<?php

namespace App\Http\Controllers;

use App\Template;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    public function getTemplates(){
        $templates = Template::orderBy('name', 'asc')->get();

        return response()->json(['result' => 'OK', 'templates' => $templates]);
    }
}
