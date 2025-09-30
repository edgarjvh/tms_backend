<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status Update on Trucker Tools</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; background: #f9f9f9; }
        .container { background: #fff; max-width: 500px; margin: 40px auto; padding: 32px 24px; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h1 { color: #2563eb; font-size: 1.7em; margin-bottom: 24px; }
        .info { margin-bottom: 16px; }
        .label { font-weight: bold; color: #555; }
        .value { color: #222; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Status Update on Trucker Tools</h1>
        <div class="info">
            <span class="label">Load Number:</span>
            <span class="value">{{ $orderNumber }}</span>
        </div>
        <div class="info">
            <span class="label">Status:</span>
            <span class="value">{{ $status }}</span>
        </div>
    </div>
</body>
</html>
