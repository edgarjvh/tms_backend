<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDivisions(Request $request): JsonResponse
    {
        $DIVISION = new Division();

        $name = $request->name ?? '';

        $divisions = $DIVISION->whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'divisions' => $divisions]);
    }
}
