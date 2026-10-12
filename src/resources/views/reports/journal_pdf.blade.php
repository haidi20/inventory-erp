<!DOCTYPE html>
<html>
<head>
    <title>Laporan Jurnal Umum</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Jurnal Umum</h1>
        <p>Periode: {{ $dateFrom ?? 'Awal' }} s/d {{ $dateTo ?? 'Sekarang' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Referensi / Akun</th>
                <th>Debit</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journals as $journal)
                <tr style="background-color: #fafafa;">
                    <td><strong>{{ $journal->journal_date->format('d/m/Y') }}</strong></td>
                    <td colspan="3"><strong>{{ $journal->reference_no }}</strong> - {{ $journal->description }}</td>
                </tr>
                @foreach($journal->lines as $line)
                    <tr>
                        <td></td>
                        <td style="{{ $line->credit > 0 ? 'padding-left: 20px;' : '' }}">
                            {{ $line->account->code }} - {{ $line->account->name }}
                        </td>
                        <td class="amount">{{ number_format($line->debit, 2) }}</td>
                        <td class="amount">{{ number_format($line->credit, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
