<!DOCTYPE html>
<html>
<head>
    <title>Laporan Neraca Saldo</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .amount { text-align: right; }
        .footer-row { font-weight: bold; background-color: #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Neraca Saldo</h1>
        <p>Periode: {{ $periodName ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Kode</th>
                <th rowspan="2">Nama Akun</th>
                <th rowspan="2">Saldo Awal</th>
                <th colspan="2">Mutasi</th>
                <th rowspan="2">Saldo Akhir</th>
            </tr>
            <tr>
                <th class="amount">Debit</th>
                <th class="amount">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sumOpening = 0; $sumDebit = 0; $sumCredit = 0; $sumClosing = 0;
            @endphp
            @foreach($accounts as $acc)
                <tr>
                    <td>{{ $acc->account_code }}</td>
                    <td>{{ $acc->account_name }}</td>
                    <td class="amount">{{ number_format($acc->opening_balance, 2) }}</td>
                    <td class="amount">{{ number_format($acc->total_debit, 2) }}</td>
                    <td class="amount">{{ number_format($acc->total_credit, 2) }}</td>
                    <td class="amount">{{ number_format($acc->closing_balance, 2) }}</td>
                </tr>
                @php
                    $sumOpening += $acc->opening_balance;
                    $sumDebit += $acc->total_debit;
                    $sumCredit += $acc->total_credit;
                    $sumClosing += $acc->closing_balance;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="2" style="text-align: center;">TOTAL</td>
                <td class="amount">{{ number_format($sumOpening, 2) }}</td>
                <td class="amount">{{ number_format($sumDebit, 2) }}</td>
                <td class="amount">{{ number_format($sumCredit, 2) }}</td>
                <td class="amount">{{ number_format($sumClosing, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
