<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saime</title>
</head>
<body>
    <div style="margin-bottom: 10px">
        <span style="font-weight: bold; margin-right: 10px">URL:</span>
        <span>{{$url ?? 'no url'}}</span>
    </div>

    <div style="margin-bottom: 10px">
        <span style="font-weight: bold; margin-right: 10px">STATUS:</span>
        <span>{{$last_status ?? 'none'}}</span>
    </div>

    <div style="margin-bottom: 10px">
        <span style="font-weight: bold; margin-right: 10px">DATE:</span>
        <span>{{$date_time ?? ''}}</span>
    </div>

    <div style="margin-bottom: 10px">
        <span style="font-weight: bold; margin-right: 10px">BODY:</span>
        <span>{{$body ?? ''}}</span>
    </div>
</body>
</html>
