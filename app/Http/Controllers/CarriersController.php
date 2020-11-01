<?php

namespace App\Http\Controllers;

use App\Carrier;
use Illuminate\Http\Request;

class CarriersController extends Controller
{
    public function carriers(Request $json)
    {
        $code = isset($json->code) ? trim($json->code) : '';
        $name = isset($json->name) ? trim($json->name) : '';
        $city = isset($json->city) ? trim($json->city) : '';
        $state = isset($json->state) ? trim($json->state) : '';
        $zip = isset($json->zip) ? trim($json->zip) : '';
        $contact_name = isset($json->contact_name) ? trim($json->contact_name) : '';
        $contact_phone = isset($json->contact_phone) ? trim($json->contact_phone) : '';
        $email = isset($json->email) ? trim($json->email) : '';

        $carriers = Carrier::whereRaw("1 = 1")
            ->whereRaw("code like '%$code%'")
            ->whereRaw("name like '%$name%'")
            ->whereRaw("city like '%$city%'")
            ->whereRaw("state like '%$state%'")
            ->whereRaw("zip like '%$zip%'")
            ->whereRaw("contact_name like '%$contact_name%'")
            ->whereRaw("contact_phone like '%$contact_phone%'")
            ->whereRaw("email like '%$email%'")
            ->orderBy('name', 'ASC')->get();

        return response()->json(['result' => 'OK', 'carriers' => $carriers]);
    }
}
