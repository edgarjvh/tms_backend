<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        @font-face {
            font-family: 'Mochi';
            src: local('Lato-Regular') url({{storage_path('fonts/Lato-Regular.ttf')}}) format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-Italic') url({{storage_path('fonts/Lato-Italic.ttf')}}) format("truetype");
            font-weight: normal;
            font-style: oblique;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-Bold') url({{storage_path('fonts/Lato-Bold.ttf')}}) format("truetype");
            font-weight: bold;
            font-style: normal;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-BoldItalic') url({{storage_path('fonts/Lato-BoldItalic.ttf')}}) format("truetype");
            font-weight: bold;
            font-style: oblique;
        }

        @page {
            size: 8.5in 11in !important;
            margin: 0.75cm 0.75cm !important;
        }

        .page-block {
            page-break-after: auto !important;
            page-break-before: auto !important;
            page-break-inside: avoid !important;
        }

        .no-print {
            display: none !important;
        }

        .container-sheet {
            box-shadow: initial !important;
            margin: 0 !important;
            page-break-before: always;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            background-color: lightgray;
            line-height: 0.75rem;
            font-size: 0.8rem;
            font-style: italic;
            font-family: 'Mochi';
        }

        .main-container {
            width: 100%;
            height: 100%;
            background-color: white;
            overflow: hidden;
        }

        .black-title-h {
            color: black;
            margin-right: 3px;
            font-weight: bold;
            font-style: italic;
        }

        .red-content-h {
            color: red;
            margin-right: 3px;
            font-style: italic;
        }

        .bol:nth-child(odd){
            color:red;
        }

        .bol:nth-child(even){
            color:darkred;
        }

        .div-table{
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .div-table-row{
            display: table-row;
            width: 100%;
        }
        .div-table-cell{
            display: table-cell;
        }
        .note:not(:first-child){
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="page-block">
        <div>
            <span class="black-title-h">DATE AND TIME SENT:</span>
            <span class="red-content-h">{{date('m/d/Y @ Hi')}}</span>
        </div>
        <div>
            <span class="black-title-h">ATTN:</span>
            <span class="red-content-h">{{$order->carrier_contact_name}}</span>
        </div>
        <div>
            <span class="black-title-h">E-mail:</span>
            <span class="red-content-h">{{$order->carrier_contact_email}}</span>
        </div>
    </div>

    <div style="position: absolute;right: -150px; top: 0;">
        <img src="{{$order->qrcode}}" alt="" width="250" height="250">
    </div>

    <div class="page-block black-title-h" style="text-align: center; font-size: 1rem; font-weight: bold; margin: 1rem 0;">
        LOAD CONFIRMATION AND RATE AGREEMENT


    </div>

    <div class="page-block" style="width: 100%">
        <div>
            <span class="black-title-h">Order Number:</span>
            <span class="red-content-h">{{$order->order_number}}</span>
        </div>
        <div>
            <span class="black-title-h">Total Payment to the Carrier – Inclusive of all Accessorial charges:</span>
            <span class="red-content-h" style="color: #4682b4; font-weight: bold">$ {{number_format($order->total_carrier_rating,2,'.',',')}}</span>
        </div>
        <div style="margin-top: 1rem; font-size: 0.83rem; line-height: 0.83rem;">
            This rate confirmation sheet issued on<span class="red-content-h" style="margin: 0;font-weight: bold">{{' ' . date('m/d/Y') . ' '}}</span>serves
            to supplement the Master Brokerage Agreement between <span class="red-content-h" style="margin: 0; font-weight: bold; font-size: 0.83rem">{{' ' . $order->company_name . ''}}</span>,
            an ICC Property Broker (MC <span class="red-content-h" style="margin: 0;">780648</span>) and:
            <span class="red-content-h" style="margin: 0;font-weight: bold; font-size: 0.83rem">{{' ' . $order->carrier->name . ' '}}</span> a Permitted Carrier
            (MC <span class="red-content-h" style="margin: 0;">{{$order->carrier->mc_number}}</span>), do hereby agree to enter into a mutual agreement on the following load.
        </div>
    </div>

    <div style="margin-top: 1rem;">
        @php
        $route_index = 0;
        $route_length = count($order->routing);
        @endphp
        @foreach($order->routing as $route)
            @if($route->type === 'pickup')
                @php
                    $item = $order->pickups->first(function($item) use ($route){return $item->id === $route->pickup_id;});
                    $item_contact_name = $item->customer->contact_name ?? '';
                    $item_contact_phone = $item->customer->contact_phone ?? '';

                    if($item->contact_id > 0){
                        foreach ($item->customer->contacts as $contact){
                            if ($contact['id'] === $item->contact_id){
                                $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                    ? $contact['phone_work'] ?? ''
                                    : (($item->contact_primary_phone ?? 'work') === 'fax'
                                        ? $contact['phone_work_fax'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                            ? $contact['phone_mobile'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                ? $contact['phone_direct'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'other'
                                                    ? $contact['phone_other'] ?? ''
                                                    : ''))));
                                break;
                            }
                        }
                    }else{
                        foreach ($item->customer->contacts as $contact){
                            if ($contact['is_primary'] === 1){
                                $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                    ? $contact['phone_work'] ?? ''
                                    : (($item->contact_primary_phone ?? 'work') === 'fax'
                                        ? $contact['phone_work_fax'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                            ? $contact['phone_mobile'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                ? $contact['phone_direct'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'other'
                                                    ? $contact['phone_other'] ?? ''
                                                    : ''))));
                                break;
                            }
                        }
                    }
                @endphp
                <div class="page-block div-table route-item" style="margin-right: 0.75cm; margin-bottom: {{$route_index === $route_length - 1 ? '' : '1.5rem'}}">
                    <div class="div-table-row">
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="black-title-h">Pick-Up Information</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->name}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->address1}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->city}}</span>, <span class="red-content-h" style="margin: 0">{{$item->customer->state}}</span> <span class="red-content-h" style="margin: 0">{{$item->customer->zip}}</span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%; padding: 0 5px">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Earliest Time:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->pu_date1}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->pu_time1}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Latest Time:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->pu_date2}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->pu_time2}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Phone:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_phone}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Contact:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><span class="black-title-h">BOL Numbers:</span></div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        @foreach(explode('|', $item->bol_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><span class="black-title-h">PO Numbers:</span></div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        @foreach(explode('|', $item->po_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">REF Numbers:</span></div>
                                    <div class="div-table-cell">
                                        @foreach(explode('|', $item->ref_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">SEAL Number:</span></div>
                                    <div class="div-table-cell"><span class="red-content-h bol">{{$item->seal_number}}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @php
                    $delivery_item = $order->deliveries->first(function($item) use ($route){return $item->id === $route->delivery_id;});
                    $item_contact_name = $delivery_item->customer->contact_name ?? '';
                    $item_contact_phone = $delivery_item->customer->contact_phone ?? '';

                    if($delivery_item->contact_id > 0){
                        foreach ($delivery_item->customer->contacts as $contact){
                            if ($contact['id'] === $delivery_item->contact_id){
                                $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                $item_contact_phone = ($delivery_item->contact_primary_phone ?? 'work') === 'work'
                                    ? $contact['phone_work'] ?? ''
                                    : (($delivery_item->contact_primary_phone ?? 'work') === 'fax'
                                        ? $contact['phone_work_fax'] ?? ''
                                        : (($delivery_item->contact_primary_phone ?? 'work') === 'mobile'
                                            ? $contact['phone_mobile'] ?? ''
                                            : (($delivery_item->contact_primary_phone ?? 'work') === 'direct'
                                                ? $contact['phone_direct'] ?? ''
                                                : (($delivery_item->contact_primary_phone ?? 'work') === 'other'
                                                    ? $contact['phone_other'] ?? ''
                                                    : ''))));
                                break;
                            }
                        }
                    }else{
                        foreach ($delivery_item->customer->contacts as $contact){
                            if ($contact['is_primary'] === 1){
                                $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                $item_contact_phone = ($delivery_item->contact_primary_phone ?? 'work') === 'work'
                                    ? $contact['phone_work'] ?? ''
                                    : (($delivery_item->contact_primary_phone ?? 'work') === 'fax'
                                        ? $contact['phone_work_fax'] ?? ''
                                        : (($delivery_item->contact_primary_phone ?? 'work') === 'mobile'
                                            ? $contact['phone_mobile'] ?? ''
                                            : (($delivery_item->contact_primary_phone ?? 'work') === 'direct'
                                                ? $contact['phone_direct'] ?? ''
                                                : (($delivery_item->contact_primary_phone ?? 'work') === 'other'
                                                    ? $contact['phone_other'] ?? ''
                                                    : ''))));
                                break;
                            }
                        }
                    }
                @endphp
                <div class="page-block div-table route-item" style="margin-right: 0.75cm; margin-bottom: {{$route_index === $route_length - 1 ? '' : '1.5rem'}}">
                    <div class="div-table-row">
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="black-title-h">Delivery Information</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$delivery_item->customer->name}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$delivery_item->customer->address1}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$delivery_item->customer->city}}</span>, <span class="red-content-h" style="margin: 0">{{$delivery_item->customer->state}}</span> <span class="red-content-h" style="margin: 0">{{$delivery_item->customer->zip}}</span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%; padding: 0 5px">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Earliest Time:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$delivery_item->delivery_date1}}</span> @ <span class="red-content-h" style="margin: 0">{{$delivery_item->delivery_time1}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Latest Time:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$delivery_item->delivery_date2}}</span> @ <span class="red-content-h" style="margin: 0">{{$delivery_item->delivery_time2}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Phone:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_phone}}</span></div></div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><div><span class="black-title-h">Contact:</span></div></div>
                                    <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">BOL Numbers:</span></div>
                                    <div class="div-table-cell">
                                        @foreach(explode('|', $delivery_item->bol_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">PO Numbers:</span></div>
                                    <div class="div-table-cell">
                                        @foreach(explode('|', $delivery_item->po_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">REF Numbers:</span></div>
                                    <div class="div-table-cell">
                                        @foreach(explode('|', $delivery_item->ref_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">SEAL Number:</span></div>
                                    <div class="div-table-cell"><span class="red-content-h bol">{{$delivery_item->seal_number}}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @php $route_index++; @endphp
        @endforeach
    </div>

    <table class="page-block" style="width: 100%; border-collapse: collapse; margin-top: 1.5rem;">
        <thead>
            <tr>
                <th style="width: 10%;text-align: center;font-weight: bold; text-decoration: underline; font-style: italic">Pieces/Skids</th>
                <th style="width: 20%;text-align: center;font-weight: bold; text-decoration: underline; font-style: italic">Weight</th>
                <th style="width: 70%;text-align: left;font-weight: bold; text-decoration: underline; font-style: italic">Description</th>
            </tr>
        </thead>
        <tbody style="padding-top: 0.5rem">
            @foreach($order->order_carrier_ratings as $rating)
                @if(strtolower($rating->rate_type->name ?? '') === 'linehaul' || strtolower($rating->rate_type->name ?? '') === 'flat')
                    <tr>
                        <td style="width: 10%;text-align: center;padding: 5px 0; color: #4682b4">
                            {{($rating['pieces'] > 0 ? $rating['pieces'] : '') . ($rating->pieces > 0
                                ? ($rating->pieces_unit ?? '') === 'pc'
                                    ? ' Pieces'
                                    : (($rating->pieces_unit ?? '') === 'sk'
                                        ? ' Skids'
                                        : '')
                                : '')}}
                        </td>
                        <td style="width: 20%;text-align: center;padding: 5px 0; color: #4682b4">{{number_format($rating->weight,is_numeric($rating->weight) && floor($rating->weight) != $rating->weight ? 2 : 0,'.',',')}}</td>
                        <td style="width: 70%;text-align: left;padding: 5px 0; color: #4682b4">{{$rating->description}}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div>
        <div class="page-block black-title-h" style="text-align: left; text-decoration: underline; font-size: 1rem; font-weight: bold; margin-top: 1.5rem;margin-bottom: 0.5rem;">
            SPECIAL INSTRUCTIONS
        </div>

        <div class="red-content-h" style="text-transform: uppercase;">
            @foreach($order->notes_for_carrier as $note)
                <div class="page-block note" style="margin-bottom: 0.5rem;">
                    @foreach(preg_split('/\r\n|\r|\n/', $note->text) as $text)
                        <div>{{$text}}</div>
                    @endforeach
                </div>

            @endforeach
        </div>
    </div>


    <div class="page-block" style="font-style: italic; font-size: 0.83rem; line-height: 0.83rem;margin-top: 1rem;">
        Carrier agrees that this reflects the entire amount due for all services provided and that no other amount will be
        billed to <span class="red-content-h" style="margin: 0; font-weight: bold; font-size: 0.83rem">{{$order->company_name}}</span>. Broker will remit
        payment to carrier within 30 days of receipt of signed bills of lading and signed delivery receipts, completed W-9 forms,
        signed Master Carrier Agreement, Rate confirmation, Contract Authority, and original certificates of Insurance naming
        <span class="red-content-h" style="margin: 0; font-weight: bold; font-size: 0.83rem">{{$order->company_name . ' '}}</span> as certificate holder.
    </div>

    <div class="page-block" style="margin-top: 1.5rem;">
        <div style="line-height: 0.8rem; position: relative;">
            <div class="red-content-h" style="text-transform: uppercase; font-style: italic; font-weight: bold;font-size: 1rem;">{{$order->carrier->name}}</div>
            <div class="red-content-h" style="text-transform: uppercase; font-style: italic;">{{$order->carrier->address1}}</div>
            <div class="red-content-h" style="text-transform: uppercase; font-style: italic;"><span>{{$order->carrier->city}}</span>, <span>{{$order->carrier->state}}</span> <span>{{$order->carrier->zip}}</span></div>

            <div style="position: absolute;left: 300px; top: -20px;">
                <img src="{{$order->qrcode}}" alt="" width="250" height="250">
            </div>
        </div>

        <div class="black-title-h" style="margin-top: 1.6rem; font-size: 0.8rem; font-weight: bold; font-style: italic; width: 20rem;">By: <div style="width: 100%; border-bottom: 1px solid black"></div></div>
        <div class="black-title-h" style="margin-top: 1.6rem; font-size: 0.8rem; font-weight: bold; font-style: italic; width: 20rem;">Print Name: <div style="width: 100%; border-bottom: 1px solid black"></div></div>
        <div class="black-title-h" style="margin-top: 1.6rem; font-size: 0.8rem; font-weight: bold; font-style: italic; width: 20rem;">Date: <span class="red-content-h">{{date('m/d/Y @ Hi')}}</span></div>
    </div>

    <div style="page-break-before: always">
        <div class="page-block black-title-h" style="text-align: center; font-size: 1rem; font-weight: bold; margin: 1.5rem 0;">
            DRIVER INFORMATION SHEET
        </div>

        <div style="margin-top: 1.5rem;">
            @php
            $route_index = 0;
            @endphp
            @foreach($order->routing as $route)
                @if($route->type === 'pickup')
                    @php
                        $item = $order->pickups->first(function($item) use ($route){return $item->id === $route->pickup_id;});
                        $item_contact_name = $item->contact_name ?? '';
                        $item_contact_phone = $item->contact_phone ?? '';

                        if($item->contact_id > 0){
                            foreach ($item->customer->contacts as $contact){
                                if ($contact['id'] === $item->contact_id){
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                        ? $contact['phone_work'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'fax'
                                            ? $contact['phone_work_fax'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                                ? $contact['phone_mobile'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                    ? $contact['phone_direct'] ?? ''
                                                    : (($item->contact_primary_phone ?? 'work') === 'other'
                                                        ? $contact['phone_other'] ?? ''
                                                        : ''))));
                                    break;
                                }
                            }
                        }else{
                            foreach ($item->customer->contacts as $contact){
                                if ($contact['is_primary'] === 1){
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                        ? $contact['phone_work'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'fax'
                                            ? $contact['phone_work_fax'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                                ? $contact['phone_mobile'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                    ? $contact['phone_direct'] ?? ''
                                                    : (($item->contact_primary_phone ?? 'work') === 'other'
                                                        ? $contact['phone_other'] ?? ''
                                                        : ''))));
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="page-block div-table route-item" style="margin-right: 0.75cm; margin-bottom: 1.5rem">
                        <div class="div-table-row">
                            <div class="div-table-cell" style="max-width: 50%;min-width: 50%;">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="black-title-h">Pick-Up Information</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->name}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->address1}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->city}}</span>, <span class="red-content-h" style="margin: 0">{{$item->customer->state}}</span> <span class="red-content-h" style="margin: 0">{{$item->customer->zip}}</span></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="div-table-cell" style="max-width: 50%;min-width: 50%; padding: 0 5px">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Earliest Time:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->pu_date1}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->pu_time1}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Latest Time:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->pu_date2}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->pu_time2}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Phone:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_phone}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Contact:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @php
                        $item = $order->deliveries->first(function($item) use ($route){return $item->id === $route->delivery_id;});
                        $item_contact_name = $item->contact_name ?? '';
                        $item_contact_phone = $item->contact_phone ?? '';

                        if($item->contact_id > 0){
                            foreach ($item->customer->contacts as $contact){
                                if ($contact['id'] === $item->contact_id){
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                        ? $contact['phone_work'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'fax'
                                            ? $contact['phone_work_fax'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                                ? $contact['phone_mobile'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                    ? $contact['phone_direct'] ?? ''
                                                    : (($item->contact_primary_phone ?? 'work') === 'other'
                                                        ? $contact['phone_other'] ?? ''
                                                        : ''))));
                                    break;
                                }
                            }
                        }else{
                            foreach ($item->customer->contacts as $contact){
                                if ($contact['is_primary'] === 1){
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone = ($item->contact_primary_phone ?? 'work') === 'work'
                                        ? $contact['phone_work'] ?? ''
                                        : (($item->contact_primary_phone ?? 'work') === 'fax'
                                            ? $contact['phone_work_fax'] ?? ''
                                            : (($item->contact_primary_phone ?? 'work') === 'mobile'
                                                ? $contact['phone_mobile'] ?? ''
                                                : (($item->contact_primary_phone ?? 'work') === 'direct'
                                                    ? $contact['phone_direct'] ?? ''
                                                    : (($item->contact_primary_phone ?? 'work') === 'other'
                                                        ? $contact['phone_other'] ?? ''
                                                        : ''))));
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="page-block div-table route-item" style="margin-right: 0.75cm; margin-bottom: 1.5rem">
                        <div class="div-table-row">
                            <div class="div-table-cell" style="max-width: 50%;min-width: 50%;">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="black-title-h">Delivery Information</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->name}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->address1}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><div><span class="red-content-h" style="margin: 0">{{$item->customer->city}}</span>, <span class="red-content-h" style="margin: 0">{{$item->customer->state}}</span> <span class="red-content-h" style="margin: 0">{{$item->customer->zip}}</span></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="div-table-cell" style="max-width: 50%;min-width: 50%; padding: 0 5px">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Earliest Time:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->delivery_date1}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->delivery_time1}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Latest Time:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div style="color: red"><span class="red-content-h" style="margin: 0">{{$item->delivery_date2}}</span> @ <span class="red-content-h" style="margin: 0">{{$item->delivery_time2}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Phone:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_phone}}</span></div></div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 25%;"><div><span class="black-title-h">Contact:</span></div></div>
                                        <div class="div-table-cell" style="width: 60%;"><div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <table class="page-block" style="width: 100%; border-collapse: collapse; margin-top: 1.5rem;">
            <thead>
            <tr>
                <th style="width: 10%;text-align: center;font-weight: bold; text-decoration: underline; font-style: italic">Pieces/Skids</th>
                <th style="width: 20%;text-align: center;font-weight: bold; text-decoration: underline; font-style: italic">Weight</th>
                <th style="width: 70%;text-align: left;font-weight: bold; text-decoration: underline; font-style: italic">Description</th>
            </tr>
            </thead>
            <tbody style="padding-top: 0.5rem">
            @foreach($order->order_carrier_ratings as $rating)
                @if(strtolower($rating->rate_type->name ?? '') === 'linehaul' || strtolower($rating->rate_type->name ?? '') === 'flat')
                    <tr>
                        <td style="width: 10%;text-align: center;padding: 5px 0; color: #4682b4">
                            {{($rating['pieces'] > 0 ? $rating['pieces'] : '') . ($rating->pieces > 0
                                ? ($rating->pieces_unit ?? '') === 'pc'
                                    ? ' Pieces'
                                    : (($rating->pieces_unit ?? '') === 'sk'
                                        ? ' Skids'
                                        : '')
                                : '')}}
                        </td>
                        <td style="width: 20%;text-align: center;padding: 5px 0; color: #4682b4">{{number_format($rating->weight,is_numeric($rating->weight) && floor($rating->weight) != $rating->weight ? 2 : 0,'.',',')}}</td>
                        <td style="width: 70%;text-align: left;padding: 5px 0; color: #4682b4">{{$rating->description}}</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>

        <div>
            <div class="page-block black-title-h" style="text-align: left; text-decoration: underline; font-size: 1rem; font-weight: bold; margin-top: 1.5rem;margin-bottom: 0.5rem;">
                SPECIAL INSTRUCTIONS
            </div>

            <div class="red-content-h" style="text-transform: uppercase;">
                @foreach($order->notes_for_driver as $note)
                    <div class="page-block note" style="margin-bottom: 0.5rem;">
                        @foreach(preg_split('/\r\n|\r|\n/', $note->text) as $text)
                            <div>{{$text}}</div>
                        @endforeach
                    </div>

                @endforeach
            </div>
        </div>
    </div>
</div>

</body>
</html>
