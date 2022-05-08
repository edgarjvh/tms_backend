<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * @throws Exception
     */
    public function getConfig() : JsonResponse
    {
        $CONFIG = new Config();

        $config = $CONFIG->all();

        return response()->json(['result' => 'OK', 'config' => $config]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveConfig(Request $request) : JsonResponse
    {
        $CONFIG = new Config();
        $config = $request->config ?? [];

        if (count($config) > 0){
            for ($i = 0; $i < count($config); $i++){

                $CONFIG->updateOrCreate([
                    'name' => $config[$i]['name']
                ],
                [
                    'value' => $config[$i]['value']
                ]);
            }
        }

        $newConfig = $CONFIG->all();
        return response()->json(['result' => 'OK', 'config' => $newConfig]);
    }
}
