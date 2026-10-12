<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .report-title { font-size: 14px; margin: 5px 0; color: #555; }
        .period { font-style: italic; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px; font-weight: bold; text-align: left; text-transform: uppercase; }
        td { border: 1px solid #dee2e6; padding: 6px; vertical-align: top; }
        
        .amount { text-align: right; font-family: 'Courier New', Courier, monospace; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-light { background-color: #f8f9fa; }
        .border-top-double { border-top: 3px double #000; }
        
        .section-header { background-color: #e9ecef; font-weight: bold; padding: 8px; border: 1px solid #dee2e6; }
        .indent-1 { padding-left: 20px; }
        .indent-2 { padding-left: 40px; }
        
        footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: right; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">{{ config('app.name', 'KLINIK SYSTEM') }}</p>
        <h1 class="report-title">@yield('title')</h1>
        <p class="period">Cabang: {{ $branch_info['name'] ?? 'Konsolidasi' }}</p>
        <p class="period">Periode: {{ $period_info['period_name'] ?? '-' }}</p>
    </div>

    @yield('content')

    <footer>
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Halaman <span class="page-number"></span>
    </footer>
</body>
</html>
