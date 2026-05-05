<!DOCTYPE html>
<html lang="bs">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Penny Plus - Zvanična Ponuda</title>
    <style>
        /* FONT PODEŠAVANJA */
        * { font-family: 'DejaVu Sans', sans-serif; box-sizing: border-box; }
        
        /* DOMPDF MAGIJA: Fiksne margine na cijelom papiru! */
        @page { 
            margin: 100px 40px 50px 40px; 
        }
        
        body { 
            margin: 0; 
            padding: 0; 
            background-color: #f8fafc;
            color: #334155;
            font-size: 11px; /* Blago smanjen bazni font */
        }

        /* --- NASLOVNA STRANICA --- */
        .cover-page {
            background-color: #0f172a;
            position: absolute; 
            top: -100px; 
            bottom: -50px; 
            left: -40px; 
            right: -40px;
            color: white;
            z-index: 1000;
        }
        .cover-accent {
            background-color: #10b981; 
            height: 15px;
            width: 100%;
        }
        .cover-content {
            padding: 140px 80px;
        }
        .cover-logo {
            font-size: 56px;
            font-weight: bold;
            color: white;
            margin-bottom: 60px;
        }
        .cover-logo span { color: #3b82f6; }
        .cover-title {
            font-size: 34px;
            font-weight: bold;
            color: #10b981;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .cover-subtitle {
            font-size: 16px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .cover-footer {
            position: absolute;
            bottom: 50px;
            left: 80px;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #1e293b;
            padding-top: 15px;
            width: calc(100% - 160px);
        }
        
        .page-break { page-break-after: always; }

        /* --- HEADER I FOOTER (UNUTRAŠNJE STRANICE) --- */
        .header {
            background-color: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            position: fixed;
            top: -100px; 
            left: 0; 
            right: 0;
            height: 80px;
        }
        .header-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 35px; 
        }
        .h-logo { font-size: 20px; font-weight: bold; color: #0f172a; }
        .h-logo span { color: #3b82f6; }
        .h-info { text-align: right; font-size: 10px; color: #64748b; font-weight: bold; text-transform: uppercase; }

        .footer {
            position: fixed;
            bottom: -50px; 
            left: 0; 
            right: 0;
            height: 30px;
            background-color: #0f172a;
            color: #94a3b8;
            font-size: 10px;
        }
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 8px; padding: 0 10px; }
        .page-num:before { content: counter(page); }

        /* --- SADRŽAJ --- */
        .content {
            padding-top: 10px; 
        }

        /* --- KARTICA ARTIKLA --- */
        .item-card {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px; /* SMANJENO: sa 25px na 12px */
            background-color: #ffffff;
            page-break-inside: avoid;
            border: 1px solid #cbd5e1;
        }
        
        .item-header-row td {
            background-color: #f1f5f9;
            padding: 5px 12px; /* SMANJENO */
            border-bottom: 1px solid #cbd5e1;
            font-size: 10px;
            font-weight: bold;
        }
        .item-index { color: #0f172a; }
        .item-sku { text-align: right; color: #475569; }

        .item-body-row td {
            padding: 8px 12px; 
            vertical-align: middle;
        }
        
        .col-image {
            width: 110px;
            text-align: center;
            border-right: 1px dashed #cbd5e1;
            vertical-align: middle !important;
        }
        .col-image img {
            max-width: 90px; 
            max-height: 90px;
            object-fit: contain;
        }

        .col-details {
            padding-left: 15px !important; 
        }
        
        .item-title {
            font-size: 14px; 
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px; 
            display: block;
        }

        .tender-req-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px; 
        }
        .tender-req-table td {
            padding: 5px 8px; 
            font-size: 10.5px;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }
        .req-label {
            width: 25%; 
            background-color: #f8fafc;
            font-weight: bold;
            color: #64748b;
            border-left: 3px solid #3b82f6;
        }
        .req-value {
            width: 75%;
            color: #334155;
            border-right: 1px solid #f1f5f9;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .metrics-table td {
            padding: 6px;
            text-align: center;
        }
        .metric-spacer {
            width: 2%; 
        }
        .metric-box-qty {
            width: 49%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .metric-box-price {
            width: 49%;
            background-color: #eff6ff; 
            border: 1px solid #bfdbfe;
        }
        .metric-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .metric-label-price {
            color: #3b82f6;
        }
        .metric-value {
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            display: block;
        }
        .metric-value-price {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="cover-page">
        <div class="cover-accent"></div>
        <div class="cover-content">
            <div class="cover-logo">PENNY<span>PLUS</span></div>
            <div class="cover-title">Zvanična Ponuda</div>
            <div class="cover-subtitle">
                Odgovor na Tendersku Dokumentaciju<br>
                <strong>Postupak #{{ $tender_id }}</strong><br><br>
                Datum kreiranja: {{ date('d.m.Y') }}
            </div>
        </div>
        <div class="cover-footer">
            PENNY PLUS d.o.o. | Igmanska bb, Vogošća, Sarajevo | OIB: 4200162210002
        </div>
    </div>

    <div class="page-break"></div>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="h-logo">PENNY<span>PLUS</span></td>
                <td class="h-info">Tender #{{ $tender_id }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>Zvanična dokumentacija generisana iz Penny Plus sistema</td>
                <td style="text-align: right;">Stranica <span class="page-num"></span></td>
            </tr>
        </table>
    </div>

    <div class="content">
        @foreach($artikli as $index => $artikal)
            
            <table class="item-card">
                <tr class="item-header-row">
                    <td class="item-index">STAVKA {{ $index + 1 }}</td>
                    <td class="item-sku">ŠIFRA: {{ $artikal['ident'] }}</td>
                </tr>
                
                <tr class="item-body-row">
                    <td class="col-image">
                        <img src="{{ $artikal['slika_base64'] }}" alt="Slika">
                    </td>
                    <td class="col-details">
                        <div class="item-title">
                            {{ !empty($artikal['naziv']) ? $artikal['naziv'] : 'Naziv artikla nedostaje' }}
                        </div>
                        
                        <table class="tender-req-table">
                            <tr>
                                <td class="req-label">Zahtjev tendera</td>
                                <td class="req-value">{{ $artikal['tender_opis'] }}</td>
                            </tr>
                        </table>

                        <table class="metrics-table">
                            <tr>
                                <td class="metric-box-qty">
                                    <span class="metric-label">Količina</span>
                                    <span class="metric-value">{{ $artikal['kolicina'] }}</span>
                                </td>
                                <td class="metric-spacer"></td>
                                
                                <td class="metric-box-price">
                                    <span class="metric-label metric-label-price">Pojedinačna cijena</span>
                                    <span class="metric-value metric-value-price">{{ $artikal['cijena'] ?? '0,00 KM' }}</span>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

        @endforeach
    </div>

</body>
</html>