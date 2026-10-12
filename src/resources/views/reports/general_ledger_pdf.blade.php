<!DOCTYPE html>
<html>
<head>
    <title>Laporan Buku Besar</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .amount { text-align: right; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Buku Besar</h1>
        @if($account)
            <h2>Akun: {{ $account->code }} - {{ $account->name }}</h2>
        @endif
        <p>Periode: {{ $dateFrom ?? 'Awal' }} s/d {{ $dateTo ?? 'Sekarang' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Referensi</th>
                <th>Keterangan</th>
                <th class="amount">Debit</th>
                <th class="amount">Kredit</th>
                <th class="amount">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $currentBalance = 0; @endphp
            @foreach($ledgers as $ledger)
                <tr>
                    <td>{{ $ledger->journal_date->format('d/m/Y') }}</td>
                    <td>{{ $ledger->reference_no }}</td>
                    <td>{{ $ledger->description }}</td>
                    <td class="amount">{{ number_format($ledger->debit, 2) }}</td>
                    <td class="amount">{{ number_format($ledger->credit, 2) }}</td>
                    <td class="amount">{{ number_format($ledger->balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
