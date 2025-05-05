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
            src: local('Lato-Regular') url({{ storage_path('fonts/Lato-Regular.ttf') }}) format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-Italic') url({{ storage_path('fonts/Lato-Italic.ttf') }}) format("truetype");
            font-weight: normal;
            font-style: oblique;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-Bold') url({{ storage_path('fonts/Lato-Bold.ttf') }}) format("truetype");
            font-weight: bold;
            font-style: normal;
        }

        @font-face {
            font-family: 'Mochi';
            src: local('Lato-BoldItalic') url({{ storage_path('fonts/Lato-BoldItalic.ttf') }}) format("truetype");
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
            font-size: 0.8rem;
            line-height: 0.8rem;
            font-style: italic;
            font-family: "Mochi";
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
            <div>
                <span class="black-title-h">DATE AND TIME SENT:</span>
                <span class="red-content-h">{{ date('m/d/Y @ Hi') }}</span>
            </div>
        </div>

        <div class="page-block black-title-h"
            style="text-align: center; font-size: 1rem; font-weight: bold; margin: 1.5rem 0;">
            CUSTOMER CONFIRMATION
        </div>

        <div class="page-block" style="width: 100%;">
            <div style="font-size: 0.83rem; line-height: 0.83rem;text-align: center;">
                Thank you for allowing <span class="red-content-h"
                    style="font-size: 0.83rem; font-weight: bold; font-style: italic">{{ $order->company_name }}</span>
                to handle your transportation needs.
            </div>
            <div style="font-size: 0.83rem; line-height: 0.83rem;text-align: center;">
                Please see the information below pertaining to the order you have scheduled.
            </div>
            <div style="font-size: 0.83rem; line-height: 0.83rem;text-align: center;">
                Don’t hesitate to contact <span class="red-content-h"
                    style="font-size: 0.83rem; font-weight: bold; font-style: italic">{{ $order->user_first_name }}</span>
                <span class="red-content-h"
                    style="font-size: 0.83rem; font-weight: bold; font-style: italic">{{ $order->user_last_name }}</span>
                at
                <span class="red-content-h"
                    style="font-size: 0.83rem; font-weight: bold; font-style: italic">{{ $order->user_phone }}</span> if
                you
                have any questions.
            </div>
            <div style="margin-top: 1rem">
                <span class="black-title-h">Order Number:</span>
                <span class="red-content-h">{{ $order->order_number }}</span>
            </div>
        </div>

        <div style="margin-top: 1rem;margin-bottom: 2rem">
            @php
                $route_index = 0;
                $route_length = count($order->routing);
            @endphp
            @foreach ($order->routing as $route)
                @if ($route->type === 'pickup')
                    @php
                        $item = $order->pickups->first(function ($item) use ($route) {
                            return $item->id === $route->pickup_id;
                        });
                        $item_contact_name = $item->customer->contact_name ?? '';
                        $item_contact_phone = $item->customer->contact_phone ?? '';

                        if ($item->contact_id > 0) {
                            foreach ($item->customer->contacts as $contact) {
                                if ($contact['id'] === $item->contact_id) {
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone =
                                        ($item->contact_primary_phone ?? 'work') === 'work'
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
                        } else {
                            foreach ($item->customer->contacts as $contact) {
                                if ($contact['is_primary'] === 1) {
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone =
                                        ($item->contact_primary_phone ?? 'work') === 'work'
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
                        style="margin-right: 0.75cm; margin-bottom: {{ $route_index === $route_length - 1 ? '' : '1.5rem' }}">
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
                                                    style="margin: 0">{{ $item->customer->name }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->address1 }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->city }}</span>, <span
                                                    class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->state }}</span> <span
                                                    class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->zip }}</span>
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
                                                    style="margin: 0">{{ $item->pu_date1 }}</span> @
                                                <span class="red-content-h"
                                                    style="margin: 0">{{ $item->pu_time1 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Latest Time:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div style="color: red"><span class="red-content-h"
                                                    style="margin: 0">{{ $item->pu_date2 }}</span> @
                                                <span class="red-content-h"
                                                    style="margin: 0">{{ $item->pu_time2 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Phone:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item_contact_phone }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Contact:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item_contact_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;"><span
                                                class="black-title-h">BOL Numbers:</span>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            @foreach (explode('|', $item->bol_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;"><span
                                                class="black-title-h">PO Numbers:</span>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            @foreach (explode('|', $item->po_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">REF Numbers:</span>
                                        </div>
                                        <div class="div-table-cell">
                                            @foreach (explode('|', $item->ref_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">SEAL Number:</span>
                                        </div>
                                        <div class="div-table-cell"><span
                                                class="red-content-h bol">{{ $item->seal_number }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @php
                        $item = $order->deliveries->first(function ($item) use ($route) {
                            return $item->id === $route->delivery_id;
                        });
                        $item_contact_name = $item->customer->contact_name ?? '';
                        $item_contact_phone = $item->customer->contact_phone ?? '';

                        if ($item->contact_id > 0) {
                            foreach ($item->customer->contacts as $contact) {
                                if ($contact['id'] === $item->contact_id) {
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone =
                                        ($item->contact_primary_phone ?? 'work') === 'work'
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
                        } else {
                            foreach ($item->customer->contacts as $contact) {
                                if ($contact['is_primary'] === 1) {
                                    $item_contact_name = $contact['first_name'] . ' ' . $contact['last_name'];
                                    $item_contact_phone =
                                        ($item->contact_primary_phone ?? 'work') === 'work'
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
                        style="margin-right: 0.75cm; margin-bottom: {{ $route_index === $route_length - 1 ? '' : '1.5rem' }}">
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
                                                    style="margin: 0">{{ $item->customer->name }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->address1 }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->city }}</span>, <span
                                                    class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->state }}</span> <span
                                                    class="red-content-h"
                                                    style="margin: 0">{{ $item->customer->zip }}</span>
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
                                                    style="margin: 0">{{ $item->delivery_date1 }}</span>
                                                @ <span class="red-content-h"
                                                    style="margin: 0">{{ $item->delivery_time1 }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Latest Time:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div style="color: red"><span class="red-content-h"
                                                    style="margin: 0">{{ $item->delivery_date2 }}</span>
                                                @ <span class="red-content-h"
                                                    style="margin: 0">{{ $item->delivery_time2 }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Phone:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item_contact_phone }}</span></div>
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell" style="width: 40%;">
                                            <div><span class="black-title-h">Contact:</span></div>
                                        </div>
                                        <div class="div-table-cell" style="width: 60%;">
                                            <div><span class="red-content-h"
                                                    style="margin: 0">{{ $item_contact_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="div-table-cell" style="max-width: 33%;min-width: 33%;">
                                <div class="div-table">
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">BOL Numbers:</span>
                                        </div>
                                        <div class="div-table-cell">
                                            @foreach (explode('|', $item->bol_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">PO Numbers:</span>
                                        </div>
                                        <div class="div-table-cell">
                                            @foreach (explode('|', $item->po_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">REF Numbers:</span>
                                        </div>
                                        <div class="div-table-cell">
                                            @foreach (explode('|', $item->ref_numbers) as $number)
                                                <span class="red-content-h bol">{{ $number }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="div-table-row">
                                        <div class="div-table-cell"><span class="black-title-h">SEAL Number:</span>
                                        </div>
                                        <div class="div-table-cell"><span
                                                class="red-content-h bol">{{ $item->seal_number }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @php $route_index++; @endphp
            @endforeach
        </div>

        <div class="page-block">
            <div>
                <span class="black-title-h">Carrier Assigned:</span>
                <span class="red-content-h">{{ $order->carrier->name }}</span>
                @if (($order->carrier->mc_number ?? '') !== '')
                    <span class="red-content-h" style="font-weight: normal"> MC# {{ $order->carrier->mc_number }}</span>
                @elseif(($order->carrier->dot_number ?? '') !== '')
                    <span class="red-content-h" style="font-weight: normal"> DOT# {{ $order->carrier->dot_number }}</span>
                @endif
            </div>

            <div>
                <span class="black-title-h">Total Charges:</span>
                <span class="red-content-h" style="color: #4682b4; font-weight: bold">$
                    {{ number_format($order->total_customer_rating, 2, '.', ',') }}</span>
            </div>
        </div>
    </div>

</body>

</html>
