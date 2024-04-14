<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserCodeWidget;
use App\Models\Widget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetsController extends Controller
{
    public function getWidgets() : JsonResponse
    {
        $widgets = Widget::query()->get();

        return response()->json(['result'=> 'OK', 'widgets' => $widgets]);
    }

    public function saveWidget(Request $request) : JsonResponse
    {
        $widget_id = $request->widget_id ?? null;
        $user_code_id = $request->user_code_id ?? null;
        $top = $request->top ?? 0;
        $left = $request->left ?? 0;

        UserCodeWidget::query()->updateOrCreate([
            'user_code_id' => $user_code_id,
            'widget_id' => $widget_id
        ],[
            'top' => $top,
            'left' => $left
        ]);

        return response()->json(['result'=> 'OK']);
    }
}
