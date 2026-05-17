<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { background: #f1f5f9; padding: 40px 16px; }

        .card { max-width: 680px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }

        /* HEADER */
        .header { background: #0f172a; padding: 28px 36px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .brand { color: #fff; font-size: 12px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; }
        .brand span { color: #3b82f6; }
        .header-badge { background: rgba(139,92,246,0.15); color: #a78bfa; font-size: 10px; font-weight: 700; padding: 5px 12px; border-radius: 99px; border: 1px solid rgba(139,92,246,0.3); letter-spacing: 1px; text-transform: uppercase; }
        .header-title { color: #fff; font-size: 18px; font-weight: 800; margin-bottom: 6px; }
        .header-period { color: #64748b; font-size: 12px; }
        .header-period strong { color: #94a3b8; }

        /* STATS ROW */
        .stats { display: flex; border-bottom: 1px solid #e2e8f0; }
        .stat { flex: 1; padding: 20px; text-align: center; border-right: 1px solid #e2e8f0; }
        .stat:last-child { border-right: none; }
        .stat-number { font-size: 28px; font-weight: 900; display: block; }
        .stat-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px; }
        .stat.accepted .stat-number { color: #10b981; }
        .stat.rejected .stat-number { color: #ef4444; }
        .stat.pending .stat-number { color: #f59e0b; }

        /* CONTENT */
        .content { padding: 32px 36px; }

        /* SECTION */
        .section { margin-bottom: 32px; }
        .section:last-child { margin-bottom: 0; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; }
        .section-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .section-dot.green { background: #10b981; }
        .section-dot.red { background: #ef4444; }
        .section-dot.yellow { background: #f59e0b; }
        .section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: #475569; }
        .section-count { margin-left: auto; background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        thead tr { background: #f8fafc; }
        th { text-align: left; padding: 9px 12px; color: #64748b; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid #e2e8f0; }
        .btn-open { display: inline-block; background: #0f172a; color: #fff !important; font-size: 10px; font-weight: 800; text-decoration: none; padding: 5px 12px; border-radius: 8px; letter-spacing: 0.5px; white-space: nowrap; }
        tbody tr + tr td { border-top: 1px solid #f8fafc; }
        td { padding: 11px 12px; color: #334155; vertical-align: top; line-height: 1.4; }
        td.tender-name { font-weight: 600; color: #1e293b; max-width: 220px; }
        td.user-name { color: #2563eb; font-weight: 600; white-space: nowrap; }
        td.reason { color: #64748b; font-style: italic; }
        td.date { color: #94a3b8; white-space: nowrap; font-size: 11px; }

        .status-pill { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        .pill-green { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.2); }
        .pill-red { background: rgba(239,68,68,0.1); color: #dc2626; border: 1px solid rgba(239,68,68,0.2); }
        .pill-yellow { background: rgba(245,158,11,0.1); color: #d97706; border: 1px solid rgba(245,158,11,0.2); }
        .pill-blue { background: rgba(59,130,246,0.1); color: #2563eb; border: 1px solid rgba(59,130,246,0.2); }

        .empty-row td { color: #94a3b8; font-style: italic; text-align: center; padding: 16px; }

        /* FOOTER */
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 36px; text-align: center; color: #94a3b8; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    <div class="header">
        <div class="header-top">
            <div class="brand">PENNY PLUS <span>TENDERI</span></div>
            <span class="header-badge">Sedmični izvještaj</span>
        </div>
        <div class="header-title">Pregled tendera — Uprava</div>
        <div class="header-period">Period: <strong>{{ $weekFrom }}</strong> — <strong>{{ $weekTo }}</strong></div>
    </div>

    <div class="stats">
        <div class="stat accepted">
            <span class="stat-number">{{ $accepted->count() }}</span>
            <span class="stat-label">Prihvaćeni</span>
        </div>
        <div class="stat rejected">
            <span class="stat-number">{{ $rejected->count() }}</span>
            <span class="stat-label">Odbijeni</span>
        </div>
        <div class="stat pending">
            <span class="stat-number">{{ $pending->count() }}</span>
            <span class="stat-label">Na čekanju</span>
        </div>
    </div>

    <div class="content">

        {{-- PRIHVAĆENI --}}
        <div class="section">
            <div class="section-header">
                <div class="section-dot green"></div>
                <span class="section-title">Prihvaćeni tenderi</span>
                <span class="section-count">{{ $accepted->count() }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tender</th>
                        <th>Ugovorni organ</th>
                        <th>Lotovi / Vrijednost</th>
                        <th>Status</th>
                        <th>Korisnik</th>
                        <th>Datum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($accepted as $w)
                    <tr>
                        <td class="tender-name">{{ $w->procedure_name }}</td>
                        <td>{{ $w->contracting_authority ?? '—' }}</td>
                        <td>
                            @if(!empty($w->has_lots) && $w->has_lots)
                                <span style="font-size:11px;color:#334155;">{{ $w->lot_names }}</span><br>
                                <span style="font-size:12px;font-weight:800;color:#059669;font-family:monospace;">{{ number_format($w->lot_value, 2, ',', '.') }} KM</span>
                            @else
                                <span style="color:#94a3b8;font-size:11px;font-style:italic;">Cijeli tender</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pillMap = ['accepted'=>'pill-green','offer_submitted'=>'pill-blue','documentation_uploaded'=>'pill-blue','won'=>'pill-green','completed'=>'pill-green'];
                                $labelMap = ['accepted'=>'Prihvaćen','offer_submitted'=>'Ponuda','documentation_uploaded'=>'Dok. učitana','won'=>'Dobijen','completed'=>'Završen'];
                            @endphp
                            <span class="status-pill {{ $pillMap[$w->status] ?? 'pill-green' }}">{{ $labelMap[$w->status] ?? $w->status }}</span>
                        </td>
                        <td class="user-name">{{ $w->user_name }}</td>
                        <td class="date">{{ \Carbon\Carbon::parse($w->updated_at)->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="7">Nema prihvaćenih tendera u ovom periodu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ODBIJENI --}}
        <div class="section">
            <div class="section-header">
                <div class="section-dot red"></div>
                <span class="section-title">Odbijeni tenderi</span>
                <span class="section-count">{{ $rejected->count() }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tender</th>
                        <th>Ugovorni organ</th>
                        <th>Razlog odbijanja</th>
                        <th>Korisnik</th>
                        <th>Datum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rejected as $w)
                    <tr>
                        <td class="tender-name">{{ $w->procedure_name }}</td>
                        <td>{{ $w->contracting_authority ?? '—' }}</td>
                        <td class="reason">{{ $w->reason ?: '—' }}</td>
                        <td class="user-name">{{ $w->user_name }}</td>
                        <td class="date">{{ \Carbon\Carbon::parse($w->updated_at)->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="6">Nema odbijenih tendera u ovom periodu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- NA ČEKANJU --}}
        <div class="section">
            <div class="section-header">
                <div class="section-dot yellow"></div>
                <span class="section-title">Na čekanju — bez odluke</span>
                <span class="section-count">{{ $pending->count() }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tender</th>
                        <th>Ugovorni organ</th>
                        <th>Korisnik</th>
                        <th>Dodijeljeno</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($pending as $w)
                    <tr>
                        <td class="tender-name">{{ $w->procedure_name }}</td>
                        <td>{{ $w->contracting_authority ?? '—' }}</td>
                        <td class="user-name">{{ $w->user_name ?? '—' }}</td>
                        <td class="date">{{ \Carbon\Carbon::parse($w->created_at)->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5">Nema tendera na čekanju.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <div class="footer">
        Sedmični izvještaj &bull; Penny Plus d.o.o. &bull; {{ now()->format('d.m.Y') }}
    </div>

</div>
</div>
</body>
</html>
