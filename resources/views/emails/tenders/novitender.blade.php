<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { background-color: #f1f5f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { background-color: #f1f5f9; padding: 40px 10px; }
        .card { 
            max-width: 550px; 
            margin: 0 auto; 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            border-radius: 32px; 
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .header { 
            background: #e2e8f0; 
            padding: 25px 35px; 
            border-bottom: 1px solid #cbd5e1;
        }
        .brand { 
            color: #1e293b; 
            font-size: 13px; 
            font-weight: 900; 
            letter-spacing: 3px; 
            text-transform: uppercase; 
        }
        .brand span { color: #3b82f6; }
        .content { padding: 40px 35px; }
        .badge { 
            display: inline-block;
            background: rgba(16, 185, 129, 0.1); 
            color: #10b981; 
            font-size: 9px; 
            font-weight: 900; 
            padding: 6px 12px; 
            border-radius: 8px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            margin-bottom: 25px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .tender-title { 
            color: #1e293b; 
            font-size: 22px; 
            font-weight: 800; 
            line-height: 1.3; 
            margin: 0 0 30px 0;
            text-transform: uppercase;
        }
        .info-grid { 
            background: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 20px; 
            padding: 25px; 
        }
        .label { color: #64748b; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; }
        .value { color: #1e293b; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .cpv-box { 
            background: #ffffff; 
            padding: 12px 15px; 
            border-radius: 12px; 
            border-left: 3px solid #3b82f6;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        /* POPRAVKA OVDJE: Promijenjena boja u tamno plavu (#1e293b) */
        .price-tag { 
            color: #1e293b; 
            font-size: 32px; 
            font-weight: 900; 
            font-family: 'Courier New', monospace; 
            display: block;
            margin-top: 5px;
        }
        
        .notification-reason {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
            border: 1px dashed #cbd5e1;
        }
        .btn-link { 
            display: block; 
            background: #2563eb; 
            color: #ffffff !important; 
            text-align: center; 
            padding: 18px; 
            border-radius: 16px; 
            text-decoration: none; 
            font-weight: 800; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            margin-top: 30px;
        }
        .footer-text { text-align: center; color: #94a3b8; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; padding: 30px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="brand">PENNY PLUS <span>TENDERI</span></div>
            </div>
            <div class="content">
                <span class="badge">Novi Tender Detektovan</span>
                <h1 class="tender-title">Nabavka HTZ opreme, radnih odijela i zaštitne obuće</h1>
                
                <div class="info-grid">
                    <div class="label">Ugovorni organ</div>
                    <div class="value">JP ELEKTROPRIVREDA BIH D.D. SARAJEVO</div>

                    <div class="label">CPV Kategorija</div>
                    <div class="cpv_box">
                        <div class="value" style="margin-bottom: 0; color: #3b82f6;">18141000-9 - Radna odjeća</div>
                    </div>

                    <div class="label">Procijenjena vrijednost</div>
                    
                    {{-- Cifra je sada tamna i čitljiva --}}
                    <div class="price-tag">85.400,00 KM</div>
                </div>

                <div class="notification-reason">
                    🔔 <strong>Zašto ste dobili ovaj mail?</strong><br>
                    Ovaj postupak je detektovan jer odgovara CPV sektoru <strong>(181)</strong> koji je dodijeljen Vašem profilu u Penny Plus administraciji.
                </div>

                <a href="{{ $url ?? '#' }}" class="btn-link">Otvori u Aplikaciji</a>
            </div>
            <div class="footer-text">
                Povjerljivi podaci • Sistemska obavijest • Penny Plus d.o.o.
            </div>
        </div>
    </div>
</body>
</html>