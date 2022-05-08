<?php

namespace App\Http\Controllers;

use App\Models\OrderCustomerRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderCustomerRatingsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderCustomerRatings(Request $request) : JsonResponse
    {
        $ORDER_CUSTOMER_RATING = new OrderCustomerRating();
        $order_id = $request->order_id ?? 0;

        $order_customer_ratings = $ORDER_CUSTOMER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_customer_ratings' => $order_customer_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderCustomerRating(Request $request): JsonResponse
    {
        $ORDER_CUSTOMER_RATING = new OrderCustomerRating();
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

        $order_customer_rating = $ORDER_CUSTOMER_RATING->updateOrCreate([
            'id' => $id
        ], [
            'order_id' => $order_id,
            'rate_type_id' => $rate_type_id,
            'description' => $description,
            'rate_subtype_id' => $rate_subtype_id,
            'pieces' => $pieces,
            'pieces_unit' => $pieces_unit,
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

        $order_customer_ratings = $ORDER_CUSTOMER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_customer_rating' => $order_customer_rating, 'order_customer_ratings' => $order_customer_ratings ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteOrderCustomerRating(Request $request): JsonResponse
    {
        $ORDER_CUSTOMER_RATING = new OrderCustomerRating();
        $id = $request->id ?? 0;
        $order_id = $request->order_id ?? 0;

        $ORDER_CUSTOMER_RATING->where('id', $id)->delete();

        $order_customer_ratings = $ORDER_CUSTOMER_RATING->where('order_id', $order_id)
            ->with(['rate_type', 'rate_subtype'])->get();

        return response()->json(['result' => 'OK', 'order_customer_ratings' => $order_customer_ratings ]);
    }
}
