<?php

namespace App\Http\Controllers;

use App\Models\LoadType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoadTypesController extends Controller
{
    public function getLoadTypes(Request $request){
        $name = $request->name ?? '';
        $withAll = $request->withAll ?? 0;

        $load_types = LoadType::whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name')
            ->get();

        if ($withAll === 1) {
            $load_types->prepend(['id' => -1, 'name' => 'All']);
        }

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
