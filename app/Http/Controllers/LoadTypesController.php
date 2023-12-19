<?php

namespace App\Http\Controllers;

use App\Models\LoadType;
use Illuminate\Http\JsonResponse;
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLoadTypesDropdown(Request $request): JsonResponse
    {
        $name = strtolower($request->name ?? '');
        $LOADTYPE = LoadType::query();

        $LOADTYPE->whereRaw("1 = 1");
        $LOADTYPE->whereRaw("LOWER(name) like '$name%'");
        $load_types = $LOADTYPE->select(['id', 'name'])->get();

        return response()->json(['result'=>'OK', 'loadTypes'=>$load_types]);
    }
}
