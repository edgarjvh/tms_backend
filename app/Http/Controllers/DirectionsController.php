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

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $directions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $text = $request->text ?? '';
        $user_code_id = $request->user_code_id ?? '';

        if ($customer_id > 0) {
            $direction = $CUSTOMER_DIRECTION->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $direction, 'notes' => $directions]);
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
        $text = $request->text ?? '';
        $user_code_id = $request->user_code_id ?? '';

        if ($customer_id > 0) {
            $direction = $CUSTOMER_DIRECTION->updateOrCreate([
                'id' => $id
            ], [
                'customer_id' => $customer_id,
                'text' => $text,
                'user_code_id' => $user_code_id,
                'date_time' => date('Y-m-d H:i:s')
            ]);

            $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->with(['user_code'])->get();

            return response()->json(['result' => 'OK', 'note' => $direction, 'notes' => $directions]);
        } else {
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

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $CUSTOMER_DIRECTION->where('id', $id)->delete();

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $directions]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCustomerDirection(Request $request): JsonResponse
    {
        $CUSTOMER_DIRECTION = new Direction();

        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;

        $CUSTOMER_DIRECTION->where('id', $id)->delete();

        $directions = $CUSTOMER_DIRECTION->where('customer_id', $customer_id)->with(['user_code'])->get();

        return response()->json(['result' => 'OK', 'notes' => $directions]);
    }
}
