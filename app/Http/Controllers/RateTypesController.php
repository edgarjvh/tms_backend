<?php

namespace App\Http\Controllers;

use App\Models\RateSubtype;
use App\Models\RateType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function Illuminate\Events\queueable;

class RateTypesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRateTypes(Request $request) : JsonResponse
    {
        $RATE_TYPE = RateType::query();
        $id = $request->id ?? 0;
        $name = $request->name ?? '';
        $truckload = $request->truckload ?? '';
        $partial = $request->partial ?? '';
        $ltl = $request->ltl ?? '';
        $air_freight = $request->air_freight ?? '';
        $customer = $request->customer ?? '';

        if ($id > 0){
            $RATE_TYPE->where('id', $id);
        }

        $rate_types = $RATE_TYPE->whereRaw("1 = 1")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("truckload like '%$truckload%'")
            ->whereRaw("partial like '%$partial%'")
            ->whereRaw("ltl like '%$ltl%'")
            ->whereRaw("air_freight like '%$air_freight%'")
            ->whereRaw("customer like '%$customer%'")
            ->orderBy('name')
            ->get();

        return response()->json(['result' => 'OK', 'rate_types' => $rate_types]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRateSubtypes(Request $request) : JsonResponse
    {
        $RATE_SUBTYPE = RateSubtype::query();
        $rate_type_id = $request->rate_type_id ?? 0;
        $name = $request->name ?? '';
        $truckload = $request->truckload ?? '';
        $partial = $request->partial ?? '';
        $ltl = $request->ltl ?? '';
        $air_freight = $request->air_freight ?? '';
        $customer = $request->customer ?? '';

        $RATE_SUBTYPE->whereRaw("1 = 1");

        if ($rate_type_id > 0){
            $RATE_SUBTYPE->whereRaw("1 = 1")->where('rate_type_id', $rate_type_id);
        }

        $RATE_SUBTYPE
            ->whereRaw("name like '%$name%'")
            ->whereRaw("truckload like '%$truckload%'")
            ->whereRaw("partial like '%$partial%'")
            ->whereRaw("ltl like '%$ltl%'")
            ->whereRaw("air_freight like '%$air_freight%'")
            ->whereRaw("customer like '%$customer%'")
            ->orderBy('name');

        $rate_subtypes = $RATE_SUBTYPE->get();

        return response()->json(['result' => 'OK', 'rate_subtypes' => $rate_subtypes]);
    }

}
