<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice['invoice_no'] ?? '' }}</title>
</head>
<body style="margin: 0; padding: 0; background: #fff; font-family: Arial, sans-serif; color: #1f2933;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #fff; padding: 24px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #ffffff;padding: 24px;">
                    <tr>
                        <td>
                            @if (!empty($messageHtml ?? ''))
                                <div style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #1f2933;">
                                    {!! $messageHtml !!}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if (!empty($trackingUrl ?? ''))
        <img src="{{ $trackingUrl }}" alt="" width="1" height="1" style="display:block;width:1px;height:1px;opacity:0;overflow:hidden;border:0;" />
    @endif
</body>
</html>
