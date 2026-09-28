<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Campus Coin</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-card { width: 100% !important; }
            .email-content { padding: 24px 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eaf3ff;color:#0b1b33;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eaf3ff;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" class="email-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background-color:#ffffff;border:1px solid #d7e5f8;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 32px;background-color:#071426;border-bottom:4px solid #2563eb;">
                            <div style="font-size:22px;font-weight:700;letter-spacing:.2px;color:#ffffff;">Campus <span style="color:#60a5fa;">Coin</span></div>
                            <div style="margin-top:5px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#cbdcf5;">Student finance, made clear</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-content" style="padding:32px;color:#0b1b33;font-size:15px;line-height:1.65;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px;background-color:#f4f8ff;border-top:1px solid #d7e5f8;color:#526782;font-size:12px;line-height:1.5;">
                            This email was sent by Campus Coin. Keep your account details and financial information private.
                        </td>
                    </tr>
                </table>
                <div style="padding:16px 8px 0;color:#526782;font-size:11px;">&copy; {{ date('Y') }} Campus Coin</div>
            </td>
        </tr>
    </table>
</body>
</html>
