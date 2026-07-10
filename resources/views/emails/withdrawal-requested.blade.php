<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Nouvelle demande de retrait</title></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
<tr><td style="background:linear-gradient(135deg,#7B3FA0,#9c4db8);padding:24px 32px;text-align:center;">
<h1 style="margin:0;color:#fff;font-size:20px;font-weight:700;">\ud83d\udcb0 Nouvelle demande de retrait</h1>
</td></tr>
<tr><td style="padding:32px;">
<p style="margin:0 0 16px;font-size:15px;color:#333;">Bonjour,</p>
<p style="margin:0 0 16px;font-size:15px;color:#333;">
<strong>{{ $withdrawal->user->nom }}</strong> ({{ $withdrawal->user->email }}) a effectu\u00e9 une demande de retrait.
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f6f9;border-radius:10px;padding:16px;margin-bottom:20px;">
<tr><td style="padding:6px 0;font-size:14px;color:#555;">Montant</td>
<td style="padding:6px 0;font-size:14px;font-weight:700;color:#1a1a2e;text-align:right;">{{ number_format($withdrawal->montant, 0, ',', ' ') }} FCFA</td></tr>
<tr><td style="padding:6px 0;font-size:14px;color:#555;">Op\u00e9rateur</td>
<td style="padding:6px 0;font-size:14px;text-align:right;">
@if($withdrawal->operateur && isset(\App\Http\Controllers\RetraitController::getOperateurs()[$withdrawal->operateur]))
{{ \App\Http\Controllers\RetraitController::getOperateurs()[$withdrawal->operateur] }}
@else
Non sp\u00e9cifi\u00e9
@endif
</td></tr>
<tr><td style="padding:6px 0;font-size:14px;color:#555;">B\u00e9n\u00e9ficiaire</td>
<td style="padding:6px 0;font-size:14px;text-align:right;">{{ $withdrawal->nom }}</td></tr>
<tr><td style="padding:6px 0;font-size:14px;color:#555;">Mobile Money</td>
<td style="padding:6px 0;font-size:14px;text-align:right;">{{ $withdrawal->mobile }}</td></tr>
<tr><td style="padding:6px 0;font-size:14px;color:#555;">Date</td>
<td style="padding:6px 0;font-size:14px;text-align:right;">{{ $withdrawal->created_at->isoFormat('D MMM YYYY HH:mm') }}</td></tr>
</table>
<p style="margin:0 0 8px;font-size:14px;color:#555;">
<a href="{{ route('superadmin.retraits') }}" style="display:inline-block;background:linear-gradient(135deg,#7B3FA0,#9c4db8);color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;">
<i class="bi bi-arrow-right"></i> Voir la demande
</a>
</p>
<p style="margin:16px 0 0;font-size:13px;color:#999;">\u00c9quipe PaxEvent</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
