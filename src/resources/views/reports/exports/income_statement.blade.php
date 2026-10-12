@extends('reports.exports.layout')

@section('title', 'Laporan Laba Rugi')

@section('content')
<table>
    <thead>
        <tr>
            <th>Akun Laba Rugi</th>
            <th class="amount">Saldo (IDR)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="section-header">
            <td>PENDAPATAN</td>
            <td class="amount">{{ number_format($revenue['total'] ?? 0, 2) }}</td>
        </tr>
        @foreach($details as $row)
            @if(($row->revenue ?? 0) > 0)
                <tr>
                    <td class="indent-1">{{ $row->account_name }}</td>
                    <td class="amount">{{ number_format($row->revenue, 2) }}</td>
                </tr>
            @endif
        @endforeach

        <tr class="section-header">
            <td>BEBAN</td>
            <td class="amount">{{ number_format($expense['total'] ?? 0, 2) }}</td>
        </tr>
        @foreach($details as $row)
            @if(($row->expense ?? 0) > 0)
                <tr>
                    <td class="indent-1">{{ $row->account_name }}</td>
                    <td class="amount">{{ number_format($row->expense, 2) }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
    <tfoot>
        <tr class="footer-row bg-light font-bold border-top-double">
            <td>LABA (RUGI) BERSIH</td>
            <td class="amount">{{ number_format($net_income ?? 0, 2) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
