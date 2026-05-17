<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper { background: #f1f5f9; padding: 40px 16px; }

        .card {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }

        .header {
            background: #0f172a;
            padding: 24px 32px;
        }
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .brand { color: #ffffff; font-size: 12px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; }
        .brand span { color: #3b82f6; }
        .header-badge {
            background: rgba(16,185,129,0.15);
            color: #10b981;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 99px;
            border: 1px solid rgba(16,185,129,0.3);
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-summary {
            color: #94a3b8;
            font-size: 13px;
        }
        .header-summary strong { color: #ffffff; font-size: 22px; font-weight: 900; margin-right: 6px; }

        .content { padding: 32px; }

        /* TENDER CARD */
        .tender-block {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .tender-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .tender-number {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .tender-name {
            color: #0f172a;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.3;
        }

        .tender-meta {
            padding: 0 20px;
        }
        .meta-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            align-items: baseline;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-label {
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-size: 10px;
            width: 140px;
            flex-shrink: 0;
        }
        .meta-value { color: #1e293b; font-weight: 600; }
        .status-pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background: rgba(59,130,246,0.1);
            color: #2563eb;
            border: 1px solid rgba(59,130,246,0.2);
        }

        /* LOTS */
        .lots-section {
            padding: 0 20px 16px;
        }
        .lots-title {
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 12px 0 10px;
            border-top: 1px solid #f1f5f9;
        }
        .lots-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .lots-table th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px 12px;
            color: #64748b;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e2e8f0;
        }
        .lots-table tbody tr + tr td { border-top: 1px solid #f8fafc; }
        .lots-table td {
            padding: 10px 12px;
            color: #334155;
            vertical-align: top;
            line-height: 1.4;
        }
        .lots-table td.lot-name { font-weight: 600; color: #1e293b; }
        .lots-table td.lot-value {
            font-weight: 800;
            color: #0f172a;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        .no-lots { color: #94a3b8; font-size: 11px; font-style: italic; padding: 8px 0; }

        /* FOOTER */
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 32px;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="header">
            <div class="header-top">
                <div class="brand">PENNY PLUS <span>TENDERI</span></div>
                <span class="header-badge">Sync obavijest</span>
            </div>
            <div class="header-summary">
                <strong>{{ $procedures->count() }}</strong>
                {{ $procedures->count() === 1 ? 'novi tender' : ($procedures->count() < 5 ? 'nova tendera' : 'novih tendera') }}
                preuzeto {{ now()->format('d.m.Y u H:i') }}
            </div>
        </div>

        <div class="content">

            @foreach($procedures as $procedure)
            <div class="tender-block">
                <div class="tender-header">
                    @if($procedure->number)
                        <div class="tender-number">{{ $procedure->number }}</div>
                    @endif
                    <div class="tender-name">{{ $procedure->name }}</div>
                </div>

                <div class="tender-meta">
                    @if($procedure->contracting_authority_name)
                    <div class="meta-row">
                        <span class="meta-label">Ugovorni organ</span>
                        <span class="meta-value">{{ $procedure->contracting_authority_name }}</span>
                    </div>
                    @endif
                    <div class="meta-row">
                        <span class="meta-label">Status</span>
                        <span class="meta-value"><span class="status-pill">{{ $procedure->status ?? '—' }}</span></span>
                    </div>
                    @if($procedure->contract_type)
                    <div class="meta-row">
                        <span class="meta-label">Tip ugovora</span>
                        <span class="meta-value">{{ $procedure->contract_type }}</span>
                    </div>
                    @endif
                    @if($procedure->contracting_authority_city_name)
                    <div class="meta-row">
                        <span class="meta-label">Grad</span>
                        <span class="meta-value">{{ $procedure->contracting_authority_city_name }}</span>
                    </div>
                    @endif
                    @if($procedure->announced)
                    <div class="meta-row">
                        <span class="meta-label">Objavljeno</span>
                        <span class="meta-value">{{ \Carbon\Carbon::parse($procedure->announced)->format('d.m.Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                <div class="lots-section">
                    @if($procedure->lots->isNotEmpty())
                        <div class="lots-title">Lotovi ({{ $procedure->lots->count() }})</div>
                        <table class="lots-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Naziv</th>
                                    <th>Status</th>
                                    <th>Vrijednost (KM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procedure->lots as $lot)
                                <tr>
                                    <td>{{ $lot->no ?? $loop->iteration }}</td>
                                    <td class="lot-name">{{ $lot->name ?: ($lot->short_description ?: '—') }}</td>
                                    <td><span class="status-pill">{{ $lot->status ?? '—' }}</span></td>
                                    <td class="lot-value">
                                        {{ $lot->estimated_value ? number_format($lot->estimated_value, 2, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="lots-title">Lotovi</div>
                        <div class="no-lots">Nema lotova za ovu proceduru.</div>
                    @endif
                </div>
            </div>
            @endforeach

            <a href="10.20.10.51:3000/tenderi" style="display:block;background:#2563eb;color:#ffffff;text-align:center;padding:15px;border-radius:14px;text-decoration:none;font-weight:800;font-size:12px;text-transform:uppercase;letter-spacing:1.2px;margin-top:8px;">
                Otvori aplikaciju
            </a>

        </div>

        <div class="footer">
            Sistemska obavijest &bull; Penny Plus d.o.o. &bull; Povjerljivi podaci
        </div>

    </div>
</div>
</body>
</html>
