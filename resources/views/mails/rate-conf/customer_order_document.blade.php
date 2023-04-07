<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
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
            font-family: "Lato", sans-serif;
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
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 33%">
                    <div style="width: 100%; text-align: left">
                        <span class="black-title-h">Date:</span>
                        <span class="red-content-h">{{date('m/d/Y @ Hi')}}</span>
                    </div>
                </td>
                <td style="width: 34%">
                    <div class="page-block red-content-h" style="text-align: center; font-size: 1rem; font-weight: bold; margin: 1.5rem 0;">
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
                            <div class="red-content-h" style="margin-top: 5px; margin-bottom: 5px;">{{$order->order_number}}</div>
                            <div class="red-content-h">{{$order->trip_number}}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-block">
        <table style="width: 102%;border-spacing: 10px;margin-left: -8px;">
            <tr>
                <td style="width: 50%;border: 1px solid rgba(0,0,0,0.5); border-radius: 5px;">
                    <div style="background-color: rgba(0,0,0,0.1); padding: 0 10px;">Bill To</div>
                </td>
                <td style="width: 50%;border: 1px solid rgba(0,0,0,0.5);border-radius: 5px;">
                    <div>

                    </div>
                </td>
            </tr>
        </table>
    </div>


</div>

</body>
</html>
