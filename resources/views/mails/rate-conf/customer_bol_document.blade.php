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
            font-family: 'Mochi Bold Oblique';
            src: local('Lato-BoldItalic') url({{storage_path('fonts/Lato-BoldItalic.ttf')}}) format("truetype");
            font-weight: normal;
            font-style: normal;
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
            margin: 0;
            padding: 0;
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

        label {
            display: block;
            font-size: 0.7rem;
            /*padding-right: 15px;*/
            /*text-indent: 15px;*/
        }

        input[type=checkbox] {
            width: 13px;
            height: 13px;
            padding: 0;
            margin:0;
            vertical-align: middle;
            position: relative;
            top: -1px;
            *overflow: hidden;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div style="width: calc(100% - 2px); border: 1px solid rgba(0,0,0,1); border-radius: 5px;">
        <div class="div-table" style="width: 100%;">
            <div class="div-table-row">
                <div class="div-table-cell" style="width: 20%">
                    <div style="text-align: center">
                        <div style="font-family: 'Mochi Bold Oblique'; margin-bottom: -5px">PICK-UP DATE</div>
                        <div>{{($order->pickup->pu_date1 ?? '') === '' ? '' : date('F d, Y',strtotime($order->pickup->pu_date1))}}</div>
                    </div>
                </div>
                <div class="div-table-cell" style="width: 60%; vertical-align: middle">
                    <div style="font-family: 'Mochi Bold Oblique'; text-align: center">BILL OF LADING – SHORT FORM – NOT NEGOTIABLE</div>
                </div>
                <div class="div-table-cell" style="width: 20%">
                    <div style="text-align: center">
                        <div style="font-family: 'Mochi Bold Oblique'; margin-bottom: -5px">DELIVERY DATE</div>
                        <div>{{($order->delivery->delivery_date2 ?? '') === '' ? '' : date('F d, Y',strtotime($order->delivery->delivery_date2))}}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="div-table" style="width: 100%;margin-top: 10px;">
            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-left: 0;border-bottom: 0;">
                    <div style="font-family: 'Mochi Bold Oblique'; background-color: rgba(0,0,0,0.1); text-align: center; padding: 3px;">SHIP FROM</div>
                </div>
                <div class="div-table-cell" rowspan="2" style="border-top: 1px solid black;">

                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-bottom: 0; border-left: 0;">
                    <div style="padding: 5px 10px; min-height: 50px;font-family: 'Mochi Med Oblique'">
                        <div style="margin-bottom: -5px">{{$order->pickup->customer->name ?? ''}}</div>
                        <div style="margin-bottom: -5px">{{$order->pickup->customer->address1 ?? ''}}</div>
                        <div style="margin-bottom: -5px">{{$order->pickup->customer->address2 ?? ''}}</div>
                        <div>
                            <span>{{$order->pickup->customer->city ?? ''}}</span>, <span>{{$order->pickup->customer->state ?? ''}}</span> <span>{{$order->pickup->customer->zip ?? ''}}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-bottom: 0; border-left: 0">
                    <div style="font-family: 'Mochi Bold Oblique'; background-color: rgba(0,0,0,0.1); text-align: center; padding: 3px;">SHIP TO</div>
                </div>
                <div class="div-table-cell" style="border-top: 1px solid black;">
                    <div style="text-align: left; padding: 3px 10px;">
                        <span style="font-family: 'Mochi Bold Oblique'">Shipping Order Number: </span> <span>{{$order->order_number ?? ''}}</span>
                    </div>
                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-bottom: 0; border-left: 0;">
                    <div style="padding: 5px 10px; min-height: 50px;font-family: 'Mochi Med Oblique'">
                        <div style="margin-bottom: -5px">{{$order->delivery->customer->name ?? ''}}</div>
                        <div style="margin-bottom: -5px">{{$order->delivery->customer->address1 ?? ''}}</div>
                        <div style="margin-bottom: -5px">{{$order->delivery->customer->address2 ?? ''}}</div>
                        <div>
                            <span>{{$order->delivery->customer->city ?? ''}}</span>, <span>{{$order->delivery->customer->state ?? ''}}</span> <span>{{$order->delivery->customer->zip ?? ''}}</span>
                        </div>
                    </div>
                </div>
                <div class="div-table-cell" style="border-top: 1px solid black; vertical-align: middle">
                    <div style="min-height: 50px;">
                        <div class="div-table" style="width: 100%;">
                            <div class="div-table-row">
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">BOL Numbers: </span>
                                        @php
                                        $bol_numbers = [];
                                        foreach (explode('|', $order->pickup->bol_numbers) as $number){
                                            if ($number !== ''){
                                                $bol_numbers[] = $number;
                                            }
                                        }
                                        @endphp
                                        @foreach($bol_numbers as $bol)
                                            <span style="color: {{$loop->index % 2 === 0 ? 'black' : '#535252'}}">{{$bol}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">PO Numbers: </span>
                                        @php
                                            $po_numbers = [];
                                            foreach (explode('|', $order->pickup->po_numbers) as $number){
                                                if ($number !== ''){
                                                    $po_numbers[] = $number;
                                                }
                                            }
                                        @endphp
                                        @foreach($po_numbers as $po)
                                            <span style="color: {{$loop->index % 2 === 0 ? 'black' : '#535252'}}">{{$po}}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="div-table-row">
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">REF Numbers: </span>
                                        @php
                                            $ref_numbers = [];
                                            foreach (explode('|', $order->pickup->ref_numbers) as $number){
                                                if ($number !== ''){
                                                    $ref_numbers[] = $number;
                                                }
                                            }
                                        @endphp
                                        @foreach($ref_numbers as $ref)
                                            <span style="color: {{$loop->index % 2 === 0 ? 'black' : '#535252'}}">{{$ref}}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">SEAL Number: </span>
                                        <span style="color: black">{{$order->pickup->seal_number}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-bottom: 0; border-left: 0">
                    <div style="font-family: 'Mochi Bold Oblique'; background-color: rgba(0,0,0,0.1); text-align: center; padding: 3px;">THIRD PARTY FREIGHT CHARGES BILL TO</div>
                </div>
                <div class="div-table-cell" style="border-top: 1px solid black;">
                    <div style="text-align: left; padding: 3px 10px;">
                        <span style="font-family: 'Mochi Bold Oblique'">Carrier Name: </span> <span>{{$order->carrier->name ?? ''}}</span>
                    </div>
                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-bottom: 0; border-left: 0">
                    <div style="padding: 5px 10px; min-height: 50px;font-family: 'Mochi Med Oblique'">
                        @if($order->freight_charge_terms_3rd_party === true)
                            <div style="margin-bottom: -5px">{{$order->company->mailing_address->name ?? ''}}</div>
                            <div style="margin-bottom: -5px">{{$order->company->mailing_address->address1 ?? ''}}</div>
                            <div style="margin-bottom: -5px">{{$order->company->mailing_address->address2 ?? ''}}</div>
                            <div>
                                <span>{{$order->company->mailing_address->city ?? ''}}</span>, <span>{{$order->company->mailing_address->state ?? ''}}</span> <span>{{$order->company->mailing_address->zip ?? ''}}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="div-table-cell" style="border-top: 1px solid black;vertical-align: middle">
                    <div style="min-height: 50px;">
                        <div class="div-table" style="width: 100%;">
                            <div class="div-table-row">
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">Truck Number: </span>
                                        <span style="color: black">{{$order->driver->tractor->number ?? ''}}</span>
                                    </div>
                                </div>
                                <div class="div-table-cell">
                                    <div style="padding: 0 10px">
                                        <span style="font-family: 'Mochi Bold Oblique'; font-style: italic">Trailer Number: </span>
                                        <span style="color: black">{{$order->driver->trailer->number ?? ''}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="div-table-row">
                <div class="div-table-cell" rowspan="2" style="border: 1px solid black; border-bottom: 0; border-left: 0">
                    <div style="padding: 5px 10px">
                        <div style="font-family: 'Mochi Bold Oblique'">Special Instructions</div>
                        <div>{{$order->pickup->special_instructions ?? ''}}</div>
                    </div>

                </div>

                <div class="div-table-cell" style="border-top: 1px solid black;">
                    <div style="padding: 5px 10px">
                        <div style="font-family: 'Mochi Bold Oblique'">Freight Charge Terms:</div>
                        <div style="font-size: 0.5rem">(Freight charges are prepaid unless marked otherwise)</div>
                        <div>
                            <div class="div-table" style="width: 100%">
                                <div class="div-table-row">
                                    <div class="div-table-cell" style="text-align: center;">
                                        <label>Prepaid<input style="margin-left: 5px" type="checkbox" {{$order->freight_charge_terms_prepaid === true ? 'checked' : ''}}></label>

                                    </div>
                                    <div class="div-table-cell" style="text-align: center;">
                                        <label>Collect<input style="margin-left: 5px" type="checkbox" {{$order->freight_charge_terms_collect === true ? 'checked' : ''}}></label>

                                    </div>
                                    <div class="div-table-cell" style="text-align: center;">
                                        <label>3rd Party<input style="margin-left: 5px" type="checkbox" {{$order->freight_charge_terms_3rd_party === true ? 'checked' : ''}}></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-cell" style="border-top: 1px solid black;">
                    <div style="text-align: center; padding: 3px;">
                        <label><input style="margin-right: 5px" type="checkbox" {{$order->freight_charge_terms_master === true ? 'checked' : ''}}>Master bill of lading with attached underlying bills of lading.</label>
                    </div>
                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" colspan="2" style="border-top: 1px solid black;">
                    <div style="font-family: 'Mochi Bold Oblique'; background-color: rgba(0,0,0,0.1); text-align: center; padding: 3px;">CUSTOMER ORDER INFORMATION</div>
                </div>
            </div>

            <div class="div-table-row">
                <div class="div-table-cell" colspan="2">
                    <div class="div-table" style="width: 100%">
                        <div class="div-table-row">
                            <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;padding: 3px;width: 9%">
                                <div style="font-family: 'Mochi Bold Oblique'; line-height: 0.8rem">Quantity or Piece Count</div>
                            </div>
                            <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;padding: 3px;width: 9%">
                                <div style="font-family: 'Mochi Bold Oblique'; line-height: 0.8rem">Type (Skids, Bundles)</div>
                            </div>
                            <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;padding: 3px;width: 9%">
                                <div style="font-family: 'Mochi Bold Oblique'; line-height: 0.8rem">Weight</div>
                            </div>
                            <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;padding: 3px;width: 9%">
                                <div style="font-family: 'Mochi Bold Oblique'; line-height: 0.8rem">HazMat (X)</div>
                            </div>
                            <div class="div-table-cell" colspan="3" style="text-align: left;vertical-align: middle;border-top: 1px solid black;padding: 5px;width: 64%">
                                <div style="font-family: 'Mochi Bold Oblique'">Commodity Description</div>
                                <div style="font-family: 'Mochi Bold Oblique'; font-size: 0.5rem; line-height: 0.5rem">
                                    Commodities requiring special or additional care or attention in handling or stowing must be so marked and packaged as to ensure safe transportation with ordinary care. See Section 2(e) of NMFC item 360
                                </div>
                            </div>
                        </div>

                        @foreach($order->rating_items as $item)
                            <div class="div-table-row">
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 9%">
                                    <div style="padding: 3px;min-height: 18px">{{$item["pieces"] ?? ''}}</div>
                                </div>
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 9%">
                                    <div style="padding: 3px;min-height: 18px">{{$item["pieces_name"] ?? ''}}</div>
                                </div>
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 9%">
                                    <div style="padding: 3px;min-height: 18px">{{$item["weight"] ?? ''}}</div>
                                </div>
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 9%"></div>
                                <div class="div-table-cell" style="text-align: left;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 46%">
                                    <div style="padding: 3px;min-height: 18px">{{$item["description"] ?? ''}}</div>
                                </div>
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border: 1px solid black; border-bottom: 0; border-left: 0;width: 9%"></div>
                                <div class="div-table-cell" style="text-align: center;vertical-align: middle;border-top: 1px solid black; ;width: 9%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="width: calc(100% - 2px); border: 1px solid rgba(0,0,0,1); border-radius: 5px;margin-top: 10px">
        <div class="div-table" style="width: 100%">
            <div class="div-table-row">
                <div class="div-table-cell" colspan="2" style="vertical-align: middle;border-right: 1px solid black">
                    <div style="font-size: 0.55rem; line-height: 0.55rem; padding: 5px">
                        Where the rate is dependent on value, shippers are required to state specifically in writing the agreed or declared value of the property as follows: “The agreed or declared value of the property is specifically stated by the shipper to be not exceeding _______________ per _______________.
                    </div>
                </div>
                <div class="div-table-cell" colspan="2">
                    <div style="padding: 5px">
                        <div class="div-table" style="width: 100%">
                            <div class="div-table-row">
                                <div class="div-table-cell">
                                    <span>COD Amount: $</span>
                                </div>
                                <div class="div-table-cell" colspan="3" style="border-bottom: 1px solid rgba(0,0,0,0.7)"></div>
                            </div>
                            <div class="div-table-row">
                                <div class="div-table-cell" style="width: 24%">
                                    <span style="font-weight: bold; font-style: italic; font-size: 0.8rem">Fee Terms:</span>
                                </div>
                                <div class="div-table-cell" style="width: 18%">
                                    <label>Collect<input style="margin-left: 5px" type="checkbox" {{$order->fee_terms_collect === true ? 'checked' : ''}}></label>
                                </div>
                                <div class="div-table-cell" style="width: 18%">
                                    <label>Prepaid<input style="margin-left: 5px" type="checkbox" {{$order->fee_terms_prepaid === true ? 'checked' : ''}}></label>
                                </div>
                                <div class="div-table-cell" style="width: 40%">
                                    <label>Customer check acceptable<input style="margin-left: 5px" type="checkbox" {{$order->fee_terms_check === true ? 'checked' : ''}}></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-cell" colspan="4" style="border-top: 1px solid black">
                    <div style="font-family: 'Mochi Bold Oblique'; text-align: center; padding: 5px;">Note: Liability limitation for loss or damage in this shipment may be applicable. See 49 USC § 14706(c)(1)(A) and (B).</div>
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-cell" colspan="2" style="vertical-align: middle;border: 1px solid black; border-left: 0;border-bottom: 0">
                    <div style="font-size: 0.55rem; line-height: 0.55rem; padding: 5px">
                        Received, subject to individually determined rates or contracts that have been agreed upon in writing between the carrier and shipper, if applicable, otherwise to the rates, classifications, and rules that have been established by the carrier and are available to the shipper, on request, and to all applicable state and federal regulations.
                    </div>
                </div>
                <div class="div-table-cell" colspan="2" style="border-top: 1px solid black">
                    <div style="padding: 5px">
                        <div class="div-table" style="width: 100%">
                            <div class="div-table-row">
                                <div class="div-table-cell" colspan="2">
                                    <div style="font-size: 0.55rem; line-height: 0.55rem">The carrier shall not make delivery of this shipment without payment of charges and all other lawful fees.</div>
                                </div>
                            </div>
                            <div class="div-table-row">
                                <div class="div-table-cell" style="width: 30%">
                                    <span style="font-weight: bold; font-style: italic; font-size: 0.8rem">Shipper Signature:</span>
                                </div>
                                <div class="div-table-cell" style="width: 70%;border-bottom: 1px solid rgba(0,0,0,0.7)"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="div-table-row">
                <div class="div-table-cell" style="border: 1px solid black; border-left: 0; border-bottom: 0">
                    <div style="padding: 0 5px 5px">
                        <div style="font-family: 'Mochi Bold Oblique';text-align: left;">Shipper Signature/Date</div>
                        <div style="border-bottom: 1px solid rgba(0,0,0,0.7); margin-top: 20px"></div>
                        <div style="font-size: 0.55rem; line-height: 0.55rem">
                            This is to certify that the above named materials are properly classified, packaged, marked, and labeled, and are in proper condition for transportation according to the applicable regulations of the DOT.
                        </div>
                    </div>
                </div>
                <div class="div-table-cell" style="border: 1px solid black; border-left: 0; border-bottom: 0">
                    <div style="padding: 0 5px 5px">
                        <div style="font-family: 'Mochi Bold Oblique';text-align: left;">Trailer Loaded</div>
                        <div style="margin-top: 10px">
                            <label style="font-size: 0.75rem"><input style="margin-right: 5px" type="checkbox" {{$order->trailer_loaded_by_shipper === true ? 'checked' : ''}}>By shipper</label>
                        </div>
                        <div style="margin-top: 5px">
                            <label style="font-size: 0.75rem"><input style="margin-right: 5px" type="checkbox" {{$order->trailer_loaded_by_driver === true ? 'checked' : ''}}>By driver</label>
                        </div>
                    </div>
                </div>
                <div class="div-table-cell" style="border: 1px solid black; border-left: 0; border-bottom: 0">
                    <div style="padding: 0 5px 5px">
                        <div style="font-family: 'Mochi Bold Oblique';text-align: left;">Freight Counted</div>
                        <div style="margin-top: 10px">
                            <label style="font-size: 0.75rem"><input style="margin-right: 5px" type="checkbox" {{$order->freight_counted_by_shipper === true ? 'checked' : ''}}>By shipper</label>
                        </div>
                        <div style="margin-top: 5px">
                            <label style="font-size: 0.75rem"><input style="margin-right: 5px" type="checkbox" {{$order->freight_counted_by_driver_pallets === true ? 'checked' : ''}}>By driver/pallets said to contain</label>
                        </div>
                        <div style="margin-top: 5px">
                            <label style="font-size: 0.75rem"><input style="margin-right: 5px" type="checkbox" {{$order->freight_counted_by_driver_pieces === true ? 'checked' : ''}}>By driver/pieces</label>
                        </div>
                    </div>
                </div>
                <div class="div-table-cell" style="border-top: 1px solid black;">
                    <div style="padding: 0 5px 5px">
                        <div style="font-family: 'Mochi Bold Oblique';text-align: left;">Carrier Signature/PickupDate</div>
                        <div style="border-bottom: 1px solid rgba(0,0,0,0.7); margin-top: 20px"></div>
                        <div style="font-size: 0.55rem; line-height: 0.55rem">
                            Carrier acknowledges receipt of packages and required placards. Carrier certifies emergency response information was made available and/or carrier has the DOT emergency response guidebook or equivalent documentation in the vehicle. Property described above is received in good order, except as noted.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="width: calc(100% - 2px); border: 1px solid rgba(0,0,0,1); border-radius: 5px;margin-top: 10px;margin-bottom: auto">
        <div style="font-family: 'Mochi Bold Oblique'; text-align: center">Consignee/Receiver Signature</div>
        <div style="text-align: center;padding-bottom: 5px;margin-top: 20px;">Date:________________________ Print Name:___________________________________ Signature:____________________________________</div>
    </div>
</div>

</body>
</html>
