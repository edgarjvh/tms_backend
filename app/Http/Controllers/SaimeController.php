<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SaimeMailable;
use App\Models\Saime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SaimeController extends Controller
{
    public function getSaimeConfig(){
        $saime = Saime::query()->first();

        return response()->json(['result'=>'OK', 'saime'=>$saime]);
    }

    public function saveSaimeConfig(Request $request){
        $id = $request->id ?? null;
        $url = $request->url ?? '';
        $last_status = $request->last_status ?? 'none';
        $date_time = $request->date_time ?? null;

        Saime::query()->updateOrCreate([
            'id'=>$id
        ],[
            'last_status'=>$last_status,
            'date_time'=>$date_time
        ]);

        try {
            Mail::send(new SaimeMailable($url, $last_status,$date_time));

            return response()->json(['result'=>'SENT']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'ERROR', 'message' => $e]);
        }
    }
}
