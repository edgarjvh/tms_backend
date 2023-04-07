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
        <td>
            Good day,
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td style="padding: 0.5rem 0;">
            Here’s your check call for the order <span style="font-weight: bold">{{$order_number ?? ''}}</span> going to
            <span style="font-weight: bold">{{$consignee_city ?? ''}}</span>, <span style="font-weight: bold">{{$consignee_state ?? ''}}</span>. The carrier
            <span style="font-weight: bold">{{$carrier_name ?? ''}}</span> is currently passing <span style="font-weight: bold">{{$event_location ?? ''}}</span>.
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
            <span style="font-weight: bold">{{$user_first_name ?? ''}}</span>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
