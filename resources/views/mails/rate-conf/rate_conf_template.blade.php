<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;1,400;1,700&display=swap');
    </style>
</head>
<body style="font-family: 'Lato', sans-serif;">
<table class="table" style="width: 100%; font-style: italic;">
    <tbody>
    <tr>
        <td style="padding: 0.5rem 0;">
{{--            Please see the attached order confirmation for your shipment from {{$origin_city}}, {{$origin_state}} to {{$destination_city}}, {{$destination_state}}. Please sign and return back.--}}
            Please see the attached order confirmation for your shipment from <span style="font-weight: bold">{{$origin_city ?? ''}}</span>,
            <span style="font-weight: bold">{{$origin_state ?? ''}}</span> to <span style="font-weight: bold">{{$destination_city ?? ''}}</span>, <span style="font-weight: bold">{{$destination_state ?? ''}}</span>.
            Please sign and return back.
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>Regards,</td>
    </tr>
    <tr>
        <td style="padding: 0.5rem 0"></td>
    </tr>
    <tr>
        <td>
{{--            {{$user_first_name}}--}}
            <span style="font-weight: bold">{{$user_first_name ?? ''}}</span>
        </td>
    </tr>
    <tr>
        <td>
{{--            {{$user_email_address}}--}}
            <span style="font-weight: bold">{{$user_email_address ?? ''}}</span>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
