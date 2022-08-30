<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesmenController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSalesmen(Request $request): JsonResponse
    {
        $SALESMAN = new Salesman();

        $salesmen = $SALESMAN->select(['id', DB::raw("TRIM(CONCAT(`first_name`, ' ', `last_name`)) AS name")])->get();

        return response()->json(['result' => 'OK', 'salesmen' => $salesmen]);
    }
}
