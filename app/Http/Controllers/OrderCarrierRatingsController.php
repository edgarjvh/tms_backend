<?php

namespace App\Http\Controllers;

use App\Models\OrderCarrierRating;
use App\Models\TemplateCarrierRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderCarrierRatingsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderCarrierRatings(Request $request) : JsonResponse
    {
        $ORDER_CARRIER_RATING = new OrderCarrierRating();
        $order_id = $request->order_id ?? 0;

        $order_carrier_ratings = $ORDER_CARRIER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_carrier_ratings' => $order_carrier_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderCarrierRating(Request $request): JsonResponse
    {
        $ORDER_CARRIER_RATING = new OrderCarrierRating();
        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;
        $rate_type_id = $request->rate_type['id'] ?? null;
        $description = $request->description ?? '';
        $rate_subtype_id = $request->rate_subtype['id'] ?? null;
        $pieces = $request->pieces ?? 0.00;
        $pieces_unit = $request->pieces_unit ?? '';
        $weight = $request->weight ?? 0.00;
        $weight_unit = $request->weight_unit ?? '';
        $feet_required = $request->feet_required ?? 0.00;
        $feet_required_unit = $request->feet_required_unit ?? '';
        $rate = $request->rate ?? 0.00;
        $percentage = $request->percentage ?? 0.00;
        $days = $request->days ?? 0.00;
        $hours = $request->hours ?? 0.00;
        $total_charges = $request->total_charges ?? 0.00;

        if ($order_id === 0){
            return response()->json(['result' => 'NO ORDER']);
        }

//        if ($rate_type_id === 0){
//            return response()->json(['result' => 'NO RATE TYPE']);
//        }

        $order_carrier_rating = $ORDER_CARRIER_RATING->updateOrCreate([
            'id' => $id
        ], [
            'order_id' => $order_id,
            'rate_type_id' => $rate_type_id,
            'description' => $description,
            'rate_subtype_id' => $rate_subtype_id,
            'pieces' => $pieces,
            'pieces_unit' => $pieces > 0 ? $pieces_unit : '',
            'weight' => $weight,
            'weight_unit' => $weight_unit,
            'feet_required' => $feet_required,
            'feet_required_unit' => $feet_required_unit,
            'rate' => $rate,
            'percentage' => $percentage,
            'days' => $days,
            'hours' => $hours,
            'total_charges' => $total_charges,
        ]);

        $order_carrier_ratings = $ORDER_CARRIER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_carrier_rating' => $order_carrier_rating, 'order_carrier_ratings' => $order_carrier_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrderCarrierRating(Request $request): JsonResponse
    {
        $ORDER_CARRIER_RATING = new OrderCarrierRating();
        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;

        $ORDER_CARRIER_RATING->where('id', $id)->delete();

        $order_carrier_ratings = $ORDER_CARRIER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_carrier_ratings' => $order_carrier_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateCarrierRating(Request $request): JsonResponse
    {
        $TEMPLATE_CARRIER_RATING = TemplateCarrierRating::query();
        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;
        $rate_type_id = $request->rate_type['id'] ?? null;
        $description = $request->description ?? '';
        $rate_subtype_id = $request->rate_subtype['id'] ?? null;
        $pieces = $request->pieces ?? 0.00;
        $pieces_unit = $request->pieces_unit ?? '';
        $weight = $request->weight ?? 0.00;
        $weight_unit = $request->weight_unit ?? '';
        $feet_required = $request->feet_required ?? 0.00;
        $feet_required_unit = $request->feet_required_unit ?? '';
        $rate = $request->rate ?? 0.00;
        $percentage = $request->percentage ?? 0.00;
        $days = $request->days ?? 0.00;
        $hours = $request->hours ?? 0.00;
        $total_charges = $request->total_charges ?? 0.00;

        if ($template_id === 0){
            return response()->json(['result' => 'NO TEMPLATE']);
        }

//        if ($rate_type_id === 0){
//            return response()->json(['result' => 'NO RATE TYPE']);
//        }

        $template_carrier_rating = $TEMPLATE_CARRIER_RATING->updateOrCreate([
            'id' => $id
        ], [
            'template_id' => $template_id,
            'rate_type_id' => $rate_type_id,
            'description' => $description,
            'rate_subtype_id' => $rate_subtype_id,
            'pieces' => $pieces,
            'pieces_unit' => $pieces > 0 ? $pieces_unit : '',
            'weight' => $weight,
            'weight_unit' => $weight_unit,
            'feet_required' => $feet_required,
            'feet_required_unit' => $feet_required_unit,
            'rate' => $rate,
            'percentage' => $percentage,
            'days' => $days,
            'hours' => $hours,
            'total_charges' => $total_charges,
        ]);

        $template_carrier_ratings = TemplateCarrierRating::where('template_id', $template_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'template_carrier_rating' => $template_carrier_rating, 'template_carrier_ratings' => $template_carrier_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTemplateCarrierRating(Request $request): JsonResponse
    {
        $TEMPLATE_CARRIER_RATING = TemplateCarrierRating::query();
        $id = $request->id ?? 0;
        $template_id = $request->template_id ?? 0;

        $TEMPLATE_CARRIER_RATING->where('id', $id)->delete();

        $template_carrier_ratings = TemplateCarrierRating::where('template_id', $template_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'template_carrier_ratings' => $template_carrier_ratings ]);
    }
}
