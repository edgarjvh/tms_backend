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
                    We have received a request to reset your password. Please click the button below to start the
                    password recovery process:
                </td>
            </tr>
            <tr>
                <td style="padding: 1rem 0; text-align: center;">
                    <a href="{{ $recovery_link ?? '#' }}"
                        style="background-color: #007bff; color: #fff; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">
                        Reset Password
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 0;">
                    If you did not request this change, you can safely ignore this email.
                </td>
            </tr>
            <tr>
                <td>Regards,</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
