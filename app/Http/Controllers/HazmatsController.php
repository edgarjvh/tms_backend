<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hazmat;
use App\Models\HazmatClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HazmatsController extends Controller
{
    public function getHazmats(Request $request): JsonResponse
    {
        $HAZMAT = new Hazmat();
        $name = $request->name ?? '';

        $hazmats = $HAZMAT->where('name', 'like', "$name%")->with(['hazmat_class'])->get();

        return response()->json(['result' => 'OK', 'hazmats' => $hazmats]);
    }
}
