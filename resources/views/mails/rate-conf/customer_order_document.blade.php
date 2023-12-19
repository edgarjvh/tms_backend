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
            font-family: 'Mochi Med Oblique';
            src: local('Lato-Italic') url({{storage_path('fonts/Lato-Italic.ttf')}}) format("truetype");
            font-weight: normal;
            font-style: oblique;
        }

        @font-face {
            font-family: 'Mochi Bold';
            src: local('Lato-BoldItalic') url({{storage_path('fonts/Lato-BoldItalic.ttf')}}) format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @page {
            size: 8.5in 11in !important;
            margin: 0.5cm 0.5cm !important;
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
            font-size: 0.8rem;
            line-height: 0.8rem;
            font-style: italic;
            font-family: "Mochi Med Oblique";
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

        .bol:nth-child(odd) {
            color: red;
        }

        .bol:nth-child(even) {
            color: darkred;
        }

        .div-table {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .div-table-row {
            display: table-row;
            width: 100%;
        }

        .div-table-cell {
            display: table-cell;
        }

        .note:not(:first-child) {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="page-block">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 33%">
                    <div style="width: 100%; text-align: left">
                        <span class="black-title-h">Date:</span>
                        <span class="red-content-h">{{date('m/d/Y @ Hi')}}</span>
                    </div>
                </td>
                <td style="width: 34%">
                    <div class="page-block red-content-h"
                         style="text-align: center; font-size: 1rem; font-weight: bold; font-style: italic; margin: 1.5rem 0;">
                        {{$order->company_name}}
                    </div>
                </td>
                <td style="width: 33%">
                    <div style="text-align: right;">
                        <div style="display: inline-block;text-align: left">
                            <div class="black-title-h" style="margin-top: 5px; margin-bottom: 5px;">ORDER #</div>
                            <div class="black-title-h">TRIP #</div>
                        </div>
                        <div style="display: inline-block; text-align: right">
                            <div class="red-content-h"
                                 style="margin-top: 5px; margin-bottom: 5px;">{{$order->order_number}}</div>
                            <div class="red-content-h">{{$order->trip_number}}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-block" style="margin-top: -10px">
        <div class="div-table" style="width: 102.5%;border-spacing: 10px; margin-left: -10px;">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="width: 50%;border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px;position: relative;">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Bill To
                    </div>
                    <div>
                        <div>
                            <span class="black-title-h">CODE:</span>
                            <span class="red-content-h">
                                {{($order->bill_to_company->code ?? '') . (($order->bill_to_company->code_number ?? 0) === 0 ? '' : $order->bill_to_company->code_number)}}
                            </span>
                        </div>
                        <div>
                            <span class="black-title-h">NAME:</span>
                            <span class="red-content-h">{{($order->bill_to_company->name ?? '')}}</span>
                        </div>
                        <div>
                            <span class="black-title-h">ADDRESS 1:</span>
                            <span class="red-content-h">{{($order->bill_to_company->address1 ?? '')}}</span>
                        </div>
                        <div>
                            <span class="black-title-h">ADDRESS 2:</span>
                            <span class="red-content-h">{{($order->bill_to_company->address2 ?? '')}}</span>
                        </div>
                        <div>
                            <div class="div-table" style="border-spacing: 0">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 50%;">
                                        <div>
                                            <span class="black-title-h">CITY:</span>
                                            <span class="red-content-h">{{($order->bill_to_company->city ?? '')}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="width: 25%;">
                                        <div>
                                            <span class="black-title-h">STATE:</span>
                                            <span
                                                class="red-content-h">{{($order->bill_to_company->state ?? '')}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="width: 25%;">
                                        <div>
                                            <span class="black-title-h">ZIP:</span>
                                            <span class="red-content-h">{{($order->bill_to_company->zip ?? '')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            $bill_to_contact_name = $order->bill_to_company->contact_name ?? '';
                            $bill_to_contact_phone = $order->bill_to_company->contact_phone ?? '';
                            $bill_to_contact_phone_ext = $order->bill_to_company->ext ?? '';
                            $bill_to_contact_email = $order->bill_to_company->email ?? '';

                            foreach (($order->bill_to_company->contacts ?? []) as $contact){
                                if ($contact['is_primary'] === 1){
                                    $bill_to_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $bill_to_contact_phone = ($contact['primary_phone'] ?? 'work') === 'work'
                                        ? $contact['phone_work'] ?? ''
                                        : (($contact['primary_phone'] ?? 'work') === 'fax'
                                            ? $contact['phone_work_fax'] ?? ''
                                            : (($contact['primary_phone'] ?? 'work') === 'mobile'
                                                ? $contact['phone_mobile'] ?? ''
                                                : (($contact['primary_phone'] ?? 'work') === 'direct'
                                                    ? $contact['phone_direct'] ?? ''
                                                    : (($contact['primary_phone'] ?? 'work') === 'other'
                                                        ? $contact['phone_other'] ?? ''
                                                        : ''))));
                                    $bill_to_contact_phone_ext = ($contact['primary_phone'] ?? 'work') === 'work'
                                        ? $contact['phone_ext'] ?? ''
                                        : '';
                                    $bill_to_contact_email = ($contact['primary_email'] ?? 'work') === 'work'
                                        ? $contact['email_work'] ?? ''
                                        : (($contact['primary_email'] ?? 'work') === 'personal'
                                            ? $contact['email_personal'] ?? ''
                                            : (($contact['primary_email'] ?? 'work') === 'other'
                                                ? $contact['email_other'] ?? ''
                                                : ''));
                                    break;
                                }
                            }

                        @endphp
                        <div>
                            <span class="black-title-h">CONTACT NAME:</span>
                            <span class="red-content-h">{{$bill_to_contact_name}}</span>
                        </div>
                        <div>
                            <div class="div-table" style="border-spacing: 0">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 75%;">
                                        <div>
                                            <span class="black-title-h">CONTACT PHONE:</span>
                                            <span class="red-content-h">{{$bill_to_contact_phone}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="width: 25%" ;>
                                        <div>
                                            <span class="black-title-h">EXT:</span>
                                            <span class="red-content-h">{{$bill_to_contact_phone_ext}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="black-title-h">EMAIL:</span>
                            <span class="red-content-h">{{$bill_to_contact_email}}</span>
                        </div>
                    </div>
                </div>

                <div class="div-table-cell"
                     style="width: 50%;border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px;position: relative">
                    <div>
                        <div>
                            <span class="black-title-h">DIVISION:</span>
                            <span class="red-content-h">{{$order->division->name ?? ''}}</span>
                        </div>

                        <div style="margin: 5px 0;"></div>

                        <div>
                            <span class="black-title-h">LOAD TYPE:</span>
                            <span class="red-content-h">{{$order->load_type->name ?? ''}}</span>
                        </div>

                        <div style="margin: 5px 0;"></div>

                        <div>
                            <span class="black-title-h">TOTAL CHARGES:</span>
                            <span class="red-content-h"
                                  style="color: #4682B4;">$ {{number_format($order->total_customer_rating,2,'.',',')}}</span>
                        </div>

                        <div style="margin: 5px 0;"></div>

                        <div>
                            <span class="black-title-h">SHIPPER:</span>
                            @foreach(($order->pickups ?? []) as $pickup)
                                <span class="red-content-h"
                                      style="margin-left: 5px;">{{($pickup["special_instructions"] ?? '')}}</span>
                            @endforeach
                        </div>

                        <div style="margin: 5px 0;"></div>

                        <div>
                            <span class="black-title-h">CONSIGNEE:</span>
                            @foreach(($order->deliveries ?? []) as $delivery)
                                <span class="red-content-h"
                                      style="margin-left: 5px;">{{($delivery["special_instructions"] ?? '')}}</span>
                            @endforeach
                        </div>

                        <div style="margin-bottom: 7px;"></div>

                        @if(($order->order_invoiced ?? 0) === 1)
                            <div style="
                                        position: absolute;
                                        top: 5px;
                                        right: 5px;
                                        background-color: lightgreen;
                                        border-radius: 15px;
                                        height: 15px;
                                        width: 80px;
                                        font-size: 0.8rem;
                                        padding: 3px;
                                        line-height: 0.8rem;
                                        font-weight: bold;
                                        border: solid 1px rgba(0, 0, 0, 0.5);
                                        vertical-align: middle;
                                        text-align: center;
                                    "
                                 }}><span>Invoiced</span></div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-block">
        <div class="div-table" style="width: 100%;">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="width: 100%; border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; position: relative">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Order Information
                    </div>
                    <div>
                        <div class="div-table">
                            <div class="div-table-row">
                                <div class="div-table-cell" style="width: 40%;">
                                    <div style="text-align: center;">
                                        <span class="black-title-h">EQUIPMENT:</span>
                                        <span class="red-content-h">{{strtoupper($order->equipment->name ?? '')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="width: 30%;">
                                    <div style="text-align: center;">
                                        <span class="black-title-h">EXPEDITED:</span>
                                        <span
                                            class="red-content-h">{{($order->expedited ?? 0) === 1 ? "YES" : "NO"}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="width: 30%;">
                                    <div style="text-align: center;">
                                        <span class="black-title-h">HAZ-MAT:</span>
                                        <span
                                            class="red-content-h">{{($order->haz_mat ?? 0) === 1 ? "YES" : "NO"}}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="div-table-row">
                                <div class="div-table-cell" colspan="3">
                                    <div style="margin: 4px 0;"></div>
                                </div>
                            </div>

                            <div class="div-table-row">
                                <div class="div-table-cell" colspan="3">
                                    <div class="div-table">
                                        <div class="div-table-row">
                                            <div class="div-table-cell"
                                                 style="text-align: center;width: 12%; font-weight: bold; text-decoration: underline;">
                                                Pieces/Skids
                                            </div>
                                            <div class="div-table-cell"
                                                 style="text-align: center;width: 12%; font-weight: bold; text-decoration: underline;">
                                                Weight
                                            </div>
                                            <div class="div-table-cell"
                                                 style="text-align: left; font-weight: bold; text-decoration: underline;">
                                                Description
                                            </div>
                                            <div class="div-table-cell"
                                                 style="text-align: right;width: 15%; font-weight: bold; text-decoration: underline;">
                                                Charges
                                            </div>
                                        </div>

                                        @foreach(($order->order_customer_ratings ?? []) as $rating)
                                            <div class="div-table-row">
                                                <div class="div-table-cell" colspan="3">
                                                    <div style="margin: 2px 0;"></div>
                                                </div>
                                            </div>
                                            <div class="div-table-row">
                                                <div class="div-table-cell"
                                                     style="text-align: center;width: 12%; color: #4682B4;">
                                                    <span>{{strval($rating["pieces"] ?? '0') === '0' ? '' : number_format($rating["pieces"],(int) ($rating["pieces"] ?? '0') == ($rating["pieces"] ?? '0') ? 0 : 2,'.',',')}}</span>
                                                    <span>{{strval($rating["pieces"] ?? '0') === '0' ? '' : (strtolower($rating["pieces_unit"] ?? '') === 'sk' ? "Skids" : "Pieces")}}</span>
                                                </div>
                                                <div class="div-table-cell"
                                                     style="text-align: center;width: 12%; color: #4682B4;">
                                                    <span>{{strval($rating["weight"] ?? '0') === '0' ? '' : number_format($rating["weight"],(int) ($rating["weight"] ?? '0') == ($rating["weight"] ?? '0') ? 0 : 2,'.',',')}}</span>
                                                </div>
                                                <div class="div-table-cell" style="text-align: left; color: #4682B4;">
                                                    <span>{{$rating["description"] ?? ''}}</span>
                                                </div>
                                                <div class="div-table-cell"
                                                     style="text-align: right;width: 15%; color: #4682B4;">
                                                    <span>{{($rating["total_charges"] ?? 0) <= 0 ? '' : '$ ' . number_format(($rating["total_charges"] ?? '0'),2,'.',',')}}</span>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;margin-top: 10px;padding: 5px;">
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
                <div class="page-block div-table route-item"
                     style="width: 100%; margin-top: {{$route_index === 0 ? '0' : '0.8rem'}}">
                    <div class="div-table-row">
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="black-title-h">Pick-Up Information</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->name}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->address1}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->city}}</span>, <span
                                                class="red-content-h"
                                                style="margin: 0">{{$item->customer->state}}</span> <span
                                                class="red-content-h" style="margin: 0">{{$item->customer->zip}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%; padding: 0 5px">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Earliest Time:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div style="color: red"><span class="red-content-h"
                                                                      style="margin: 0">{{$item->pu_date1}}</span> @
                                            <span class="red-content-h" style="margin: 0">{{$item->pu_time1}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Latest Time:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div style="color: red"><span class="red-content-h"
                                                                      style="margin: 0">{{$item->pu_date2}}</span> @
                                            <span class="red-content-h" style="margin: 0">{{$item->pu_time2}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Phone:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item_contact_phone}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Contact:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><span class="black-title-h">BOL Numbers:</span>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        @foreach(explode('|', $item->bol_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;"><span class="black-title-h">PO Numbers:</span>
                                    </div>
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
                                    <div class="div-table-cell"><span
                                            class="red-content-h bol">{{$item->seal_number}}</span></div>
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
                <div class="page-block div-table route-item" style="width: 100%; margin-top: 0.8rem">
                    <div class="div-table-row">
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="black-title-h">Delivery Information</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->name}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->address1}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item->customer->city}}</span>, <span
                                                class="red-content-h"
                                                style="margin: 0">{{$item->customer->state}}</span> <span
                                                class="red-content-h" style="margin: 0">{{$item->customer->zip}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%; padding: 0 5px">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Earliest Time:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div style="color: red"><span class="red-content-h"
                                                                      style="margin: 0">{{$item->delivery_date1}}</span>
                                            @ <span class="red-content-h"
                                                    style="margin: 0">{{$item->delivery_time1}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Latest Time:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div style="color: red"><span class="red-content-h"
                                                                      style="margin: 0">{{$item->delivery_date2}}</span>
                                            @ <span class="red-content-h"
                                                    style="margin: 0">{{$item->delivery_time2}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Phone:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div><span class="red-content-h"
                                                   style="margin: 0">{{$item_contact_phone}}</span></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="width: 40%;">
                                        <div><span class="black-title-h">Contact:</span></div>
                                    </div>
                                    <div class="div-table-cell" style="width: 60%;">
                                        <div><span class="red-content-h" style="margin: 0">{{$item_contact_name}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                            <div class="div-table">
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">BOL Numbers:</span></div>
                                    <div class="div-table-cell">
                                        @foreach(explode('|', $item->bol_numbers) as $number)
                                            <span class="red-content-h bol">{{$number}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell"><span class="black-title-h">PO Numbers:</span></div>
                                    <div class="div-table-cell">
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
                                    <div class="div-table-cell"><span
                                            class="red-content-h bol">{{$item->seal_number}}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $route_index++;
            @endphp
        @endforeach
    </div>

    <div class="page-block">
        <div class="div-table" style="width: 102.5%; border-spacing: 10px; margin-left: -10px">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; width: 65%; position: relative;">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Carrier Information
                    </div>
                    <div style="min-height: 70px;">
                        <div class="div-table" style="width: 100%; border-spacing: 0">
                            <div class="div-table-row">
                                <div class="div-table-cell" style="width: 45%;">
                                    <div>
                                        <span style="color: #4682B4;">
                                            {{($order->carrier->code ?? '') . (($order->carrier->code_number ?? 0) === 0 ? '' : $order->carrier->code_number)}}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="red-content-h">{{($order->carrier->name ?? '')}}</span>
                                    </div>
                                    <div>
                                        <span class="red-content-h">{{($order->carrier->address1 ?? '')}}</span>
                                    </div>
                                    <div>
                                        <span class="red-content-h">{{($order->carrier->address2 ?? '')}}</span>
                                    </div>
                                    <div>
                                        <span
                                            class="red-content-h">{{($order->carrier->city ?? '')}}, {{($order->carrier->state ?? '')}} {{($order->carrier->zip ?? '')}}</span>
                                    </div>
                                </div>
                                @php
                                    $carrier_contact = collect($order->carrier->contacts)->first(function ($item) use ($order) {
                                        return $item["id"] === ($order->carrier_contact_id ?? 0);
                                    });

                                    $carrier_contact_name = trim(($carrier_contact["first_name"] ?? '') . ' ' . ($carrier_contact["last_name"] ?? ''));
                                    $carrier_contact_phone = ($order->carrier_contact_primary_phone ?? 'work') === 'work'
                                        ? $carrier_contact["phone_work"] ?? ''
                                        : (($order->carrier_contact_primary_phone ?? 'work') === 'fax'
                                            ? $carrier_contact["phone_work_fax"] ?? ''
                                            : (($order->carrier_contact_primary_phone ?? 'work') === 'mobile'
                                                ? $carrier_contact["phone_mobile"] ?? ''
                                                : (($order->carrier_contact_primary_phone ?? 'work') === 'direct'
                                                    ? $carrier_contact["phone_direct"] ?? ''
                                                    : (($order->carrier_contact_primary_phone ?? 'work') === 'other'
                                                        ? $carrier_contact["phone_other"] ?? ''
                                                        : ''))));
                                    $carrier_contact_phone_ext = ($order->carrier_contact_primary_phone ?? 'work') === 'work'
                                        ? $carrier_contact["phone_ext"] ?? ''
                                        : '';
                                    $carrier_contact_email = ($order->carrier_contact_primary_email ?? 'work') === 'work'
                                        ? $carrier_contact["email_work"] ?? ''
                                        : (($order->carrier_contact_primary_email ?? 'work') === 'personal'
                                            ? $carrier_contact["email_personal"] ?? ''
                                            : (($order->carrier_contact_primary_email ?? 'work') === 'other'
                                                ? $carrier_contact["email_other"] ?? ''
                                                : ''));
                                @endphp
                                <div class="div-table-cell" style="width: 55%;">
                                    <div>
                                        <span class="black-title-h">CONTACT:</span>
                                        <span class="red-content-h">{{$carrier_contact_name}}</span>
                                    </div>
                                    <div>
                                        <div class="div-table" style="width: 100%; border-spacing: 0">
                                            <div class="div-table-row">
                                                <div class="div-table-cell" style="width: 70%">
                                                    <div>
                                                        <span class="black-title-h">PHONE:</span>
                                                        <span class="red-content-h">{{$carrier_contact_phone}}</span>
                                                    </div>
                                                </div>
                                                <div class="div-table-cell" style="width: 30%">
                                                    <span class="black-title-h">EXT:</span>
                                                    <span class="red-content-h">{{$carrier_contact_phone_ext}}</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div>
                                        <span class="black-title-h">E-MAIL:</span>
                                        <span class="red-content-h">{{$carrier_contact_email}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="div-table-cell"
                     style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; width: 35%; position: relative;">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Driver Information
                    </div>
                    <div style="min-height: 70px;">
                        <div>
                            <span class="black-title-h">NAME:</span>
                            <span class="red-content-h">{{$order->driver->name ?? ''}}</span>
                        </div>
                        <div>
                            <span class="black-title-h">PHONE:</span>
                            <span class="red-content-h">{{$order->driver->contact_phone ?? ''}}</span>
                        </div>
                        <div>
                            <span class="black-title-h">UNIT:</span>
                            <span class="red-content-h">{{$order->driver->tractor->number ?? ''}}</span>
                        </div>
                        <div>
                            <span class="black-title-h">TRAILER:</span>
                            <span class="red-content-h">{{$order->driver->trailer->number ?? ''}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-block">
        <div class="div-table" style="width: 100%;">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; position: relative">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Notes for Carrier
                    </div>
                    <div style="min-height: 70px;">
                        @php
                            $note_carrier_index = 0;
                        @endphp
                        @foreach(($order->notes_for_carrier ?? []) as $carrier_note)

                            <div class="red-content-h" style="margin-top: {{$note_carrier_index === 0 ? '0' : '7px'}}">
                                @php
                                    $note_lines = preg_split ('/$\R?^/m', ($carrier_note->text ?? ''));
                                @endphp
                                @foreach($note_lines as $note)
                                    <div class="red-content-h">
                                        {{strtoupper($note)}}
                                    </div>
                                @endforeach


                            </div>
                            @php $note_carrier_index++; @endphp
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-block">
        <div class="div-table" style="width: 100%; margin-top: 10px;">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; position: relative">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Events
                    </div>
                    <div style="min-height: 70px;">
                        <div class="div-table" style="width: 100%;">
                            <div class="div-table-row">
                                <div class="div-table-cell"
                                     style="text-align: left;width: 16%; font-weight: bold; text-decoration: underline;">
                                    Date & Time
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: left;width: 25%; font-weight: bold; text-decoration: underline;">
                                    Event
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: left;width: 13%; font-weight: bold; text-decoration: underline;">
                                    Event Location
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: left;width: 36%; font-weight: bold; text-decoration: underline;">
                                    Event Notes
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: right;width: 8%; font-weight: bold; text-decoration: underline;">
                                    User
                                </div>
                            </div>
                            @foreach($order->events as $event)
                                <div class="div-table-row">
                                    <div class="div-table-cell" colspan="5">
                                        <div style="margin: 3px 0;"></div>
                                    </div>
                                </div>
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="text-align: left;width: 16%;">
                                        <div>
                                            <span
                                                class="red-content-h">{{($event->event_date ?? '') . (($event->event_date ?? '') === '' && ($event->event_time ?? '') === '' ? '' : ' @ ' . $event->event_time)}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="text-align: left;width: 25%;">
                                        <div>
                                            <span
                                                class="red-content-h">{{strtoupper($event->event_type->name ?? '')}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="text-align: left;width: 15%;">
                                        <div>
                                            <span class="red-content-h">{{$event->event_location ?? ''}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="text-align: left;width: 36%;">
                                        <div>
                                            <span class="red-content-h">{{$event->event_notes ?? ''}}</span>
                                        </div>
                                    </div>
                                    <div class="div-table-cell" style="text-align: right;width: 8%;">
                                        <div>
                                            <span class="red-content-h">{{$event->user_code->code ?? ''}}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-block">
        <div class="div-table" style="width: 100%; margin-top: 10px;">
            <div class="div-table-row">
                <div class="div-table-cell"
                     style="border: 1px solid rgba(0,0,0,0.1); border-radius: 3px;padding: 10px 5px 5px; position: relative">
                    <div style="position: absolute; background-color: white; padding: 0 5px; top: -10px; left: 10px;">
                        Totals
                    </div>
                    <div>
                        <div class="div-table" style="width: 100%;">
                            <div class="div-table-row">
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">
                                    Pieces/Skids
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">Weight
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">Charges
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">Order
                                    Cost
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">Profit
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">
                                    Percentage
                                </div>
                                <div class="div-table-cell"
                                     style="text-align: center; font-weight: bold; text-decoration: underline;">Miles
                                </div>
                            </div>

                            @php
                                $total_pieces = array_reduce($order->order_customer_ratings->toArray(), function ($pieces, $item){
                                    return $pieces + $item["pieces"];
                                });

                                $total_weight = array_reduce($order->order_customer_ratings->toArray(), function ($weight, $item){
                                    return $weight + $item["weight"];
                                });

                                $total_charges = array_reduce($order->order_customer_ratings->toArray(), function ($total_charges, $item){
                                    return $total_charges + $item["total_charges"];
                                });

                                $total_cost = array_reduce($order->order_carrier_ratings->toArray(), function ($total_charges, $item){
                                    return $total_charges + $item["total_charges"];
                                });

                                $profit = $total_charges - $total_cost;

                                $percentage = $total_charges > 0 || $total_cost > 0
                                ? $profit * 100 / ($total_charges > 0 ? $total_charges : $total_cost)
                                : 0;

                            @endphp
                            <div class="div-table-row">
                                <div class="div-table-cell" colspan="7">
                                    <div style="margin: 3px 0;"></div>
                                </div>
                            </div>

                            <div class="div-table-row">
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>{{(int)$total_pieces <= 0 ? '' : number_format($total_pieces, (int) $total_pieces == $total_pieces ? 0 : 2, '.', ',')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>{{(int)$total_weight <= 0 ? '' : number_format($total_weight, (int) $total_weight == $total_weight ? 0 : 2, '.', ',')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>$ {{number_format($total_charges, 2, '.', ',')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>$ {{number_format($total_cost, 2, '.', ',')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>$ {{number_format($profit, 2, '.', ',')}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>{{number_format($percentage, (int)$percentage == $percentage ? 0 : 2, '.', ',')}}%</span>
                                    </div>
                                </div>
                                <div class="div-table-cell" style="text-align: center; color: #4682B4;">
                                    <div>
                                        <span>{{number_format(($order->miles ?? 0) / 1609.34, 0, '.', ',')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
