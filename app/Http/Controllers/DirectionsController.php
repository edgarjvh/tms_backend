<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectionsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function directions(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $customer_id = $request->customer_id;

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $direction_id = $request->direction_id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $direction_text = $request->direction ?? '';
        $direction_user = $request->user ?? '';
        $direction_datetime = $request->datetime ?? '';

        if ($customer_id > 0) {
            $direction = $CUSTOMER_DIRECTION->updateOrCreate([
                'id' => $direction_id
            ], [
                'customer_id' => $customer_id,
                'direction' => $direction_text,
                'user' => $direction_user,
                'date_time' => $direction_datetime
            ]);

            $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();

            return response()->json(['result' => 'OK', 'direction' => $direction, 'directions' => $directions]);
        } else {
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $direction_text = $request->text ?? '';
        $direction_user = $request->user ?? '';
        $direction_datetime = $request->date_time ?? '';

        if ($customer_id > 0){
            $direction = $CUSTOMER_DIRECTION->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $direction_text,
                'user' => $direction_user,
                'date_time' => $direction_datetime
            ]);

            $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();

            return response()->json(['result' => 'OK', 'direction' => $direction, 'data' => $directions]);
        }else{
            return response()->json(['result' => 'NO CUSTOMER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $direction_id = $request->direction_id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $CUSTOMER_DIRECTION->where('id', $direction_id)->delete();

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'directions' => $directions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $direction_id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $CUSTOMER_DIRECTION->where('id', $direction_id)->delete();

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->get();

        return response()->json(['result' => 'OK', 'data' => $directions]);
    }
}
