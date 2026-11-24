<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Informasi Layanan Konseling' }}</title>
</head>
<body style="margin:0;background:#f3f7f3;font-family:Arial,Helvetica,sans-serif;color:#23332a;line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f7f3;padding:28px 12px;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border:1px solid #dfe9e0;border-radius:18px;overflow:hidden;">
            <tr><td style="background:#527b5a;padding:24px 30px;color:#fff;">
                <div style="font-size:18px;font-weight:700;letter-spacing:.2px;">Layanan Konseling</div>
                <div style="font-size:12px;opacity:.9;margin-top:2px;">Pesan informasi untuk perjalanan Anda</div>
            </td></tr>
            <tr><td style="padding:30px;">
                <p style="font-size:16px;margin:0 0 14px;font-weight:700;">{{ $greeting }}</p>
                @if($status)
                    <div style="display:inline-block;background:#e7f4e9;color:#2f6d42;border:1px solid #c9e6cf;border-radius:999px;padding:6px 13px;font-size:12px;font-weight:700;margin-bottom:16px;">{{ $status }}</div>
                @endif
                <div style="font-size:14px;color:#435248;white-space:pre-line;">{!! nl2br(e($bodyMessage ?? '')) !!}</div>
                @if(count($details))
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0;background:#f6faf6;border:1px solid #dcebdd;border-radius:12px;">
                        @foreach($details as $label => $value)
                            <tr><td style="padding:9px 14px;color:#718078;font-size:12px;width:38%;">{{ $label }}</td><td style="padding:9px 14px;color:#23332a;font-size:13px;font-weight:700;">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                @endif
                @if($actionUrl)
                    <p style="margin:24px 0 10px;"><a href="{{ $actionUrl }}" style="display:inline-block;background:#527b5a;color:#fff;text-decoration:none;border-radius:10px;padding:12px 20px;font-size:13px;font-weight:700;">{{ $actionLabel ?: 'Buka Informasi' }}</a></p>
                @endif
                @if($secondaryUrl)
                    <p style="margin:10px 0 0;"><a href="{{ $secondaryUrl }}" style="display:inline-block;color:#527b5a;text-decoration:none;font-size:13px;font-weight:700;">{{ $secondaryLabel ?: 'Buka tautan lainnya' }} &rarr;</a></p>
                @endif
                @if($quote)<p style="border-left:3px solid #91b497;margin:24px 0 0;padding:8px 14px;color:#65756b;font-size:13px;font-style:italic;">{{ $quote }}</p>@endif
            </td></tr>
            <tr><td style="background:#f8fbf8;border-top:1px solid #edf2ed;padding:18px 30px;color:#849188;font-size:11px;">Pesan ini dikirim otomatis. Jika membutuhkan bantuan segera, hubungi layanan konseling melalui kontak yang tersedia.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
