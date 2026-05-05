<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { background-color: #020617; margin: 0; padding: 0; }
        .wrapper { background-color: #020617; padding: 40px 15px; font-family: 'Inter', Helvetica, Arial, sans-serif; }
        .card { max-width: 750px; margin: 0 auto; background: #0f172a; border: 1px solid #1e293b; border-radius: 32px; overflow: hidden; }
        .header { padding: 30px; background: #1e293b; border-bottom: 1px solid #334155; }
        .brand { color: #f8fafc; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 3px; }
        .brand span { color: #3b82f6; }
        .content { padding: 40px; }
        
        /* KPI Box */
        .kpi-box { background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 20px; padding: 30px; text-align: center; margin-bottom: 35px; }
        .kpi-label { color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; }
        .kpi-value { color: #ffffff; font-size: 42px; font-weight: 900; font-family: 'Courier New', monospace; }
        
        /* Tabela */
        .table-wrap { border-radius: 20px; overflow: hidden; border: 1px solid #1e293b; background: rgba(30, 41, 59, 0.2); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e293b; padding: 15px; color: #64748b; font-size: 10px; text-transform: uppercase; text-align: left; letter-spacing: 1px; }
        td { padding: 18px 15px; border-bottom: 1px solid #1e293b; color: #e2e8f0; font-size: 13px; vertical-align: top; }
        
        .ref { color: #3b82f6; font-weight: 800; font-size: 14px; }
        .buyer { color: #f8fafc; font-weight: 600; display: block; margin-top: 2px; }
        .reason { color: #ef4444; font-size: 11px; font-style: italic; margin-top: 5px; display: block; font-weight: 500; }
        
        /* Statusi */
        .status-pill { display: inline-block; padding: 5px 10px; border-radius: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase; border: 1px solid; }
        .win { color: #10b981; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); }
        .progress { color: #3b82f6; background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.2); }
        .lost { color: #ef4444; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }
        
        .price { font-family: 'Courier New', monospace; font-weight: 800; font-size: 15px; color: #ffffff; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff !important; padding: 18px 35px; border-radius: 16px; text-decoration: none; font-weight: 800; margin-top: 40px; text-transform: uppercase; letter-spacing: 1px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header"><div class="brand">PENNY PLUS <span>TENDERI</span></div></div>
            <div class="content">
                <div class="kpi-box">
                    <div class="kpi-label">UKUPNA VRIJEDNOST SEDMICE</div>
                    <div class="kpi-value">205.400,00 <span style="font-size: 18px; color: #3b82f6;">KM</span></div>
                </div>
                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Referent / Kupac</th>
                                <th>Status</th>
                                <th align="right">Iznos</th>

                                RADIO M RACUN ZA TELEMAH
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="ref">Anvar A.</span>
                                    <span class="buyer">Elektroprivreda BiH</span>
                                </td>
                                <td><span class="status-pill win">Dobijen</span></td>
                                <td align="right" class="price">85.400,00</td>
                            </tr>
                            
                            <tr>
                                <td>
                                    <span class="ref">Kenan Z.</span>
                                    <span class="buyer">KCUS Sarajevo</span>
                                    <span class="reason">⚠ Razlog: Nepotpuna dokumentacija (Član 45.)</span>
                                </td>
                                <td><span class="status-pill lost">Odbijen</span></td>
                                <td align="right" class="price">120.000,00</td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="ref">Sarajčić E.</span>
                                    <span class="buyer">Vodovod i Kanalizacija</span>
                                </td>
                                <td><span class="status-pill progress">U radu</span></td>
                                <td align="right" class="price">45.200,00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <center>
                    <a href="http://127.0.0.1:8000/tender-progress" class="btn">DETALJNI DASHBOARD</a>
                </center>
            </div>
        </div>
    </div>
</body>
</html>