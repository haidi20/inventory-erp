@extends('reports.exports.layout')

@section('title', 'Laporan Arus Kas')

@section('content')
<table>
    <thead>
        <tr>
            <th>Deskripsi Arus Kas</th>
            <th class="amount">Saldo (IDR)</th>
        </tr>
    </thead>
    <tbody>
        <!-- OPERATING ACTIVITIES -->
        <tr class="section-header bg-light">
            <td colspan="2">AKTIVITAS OPERASI</td>
        </tr>
        @foreach($operating_activities as $activity)
            <tr>
                <td class="indent-1">{{ $activity['name'] }}</td>
                <td class="amount">{{ number_format($activity['amount'], 2) }}</td>
            </tr>
        @endforeach
        <tr class="font-bold">
            <td class="indent-2">Kas Bersih dari Aktivitas Operasi</td>
            <td class="amount">{{ number_format($operating_total, 2) }}</td>
        </tr>

        <!-- INVESTING ACTIVITIES -->
        <tr class="section-header bg-light">
            <td colspan="2">AKTIVITAS INVESTASI</td>
        </tr>
        @foreach($investing_activities as $activity)
            <tr>
                <td class="indent-1">{{ $activity['name'] }}</td>
                <td class="amount">{{ number_format($activity['amount'], 2) }}</td>
            </tr>
        @endforeach
        <tr class="font-bold">
            <td class="indent-2">Kas Bersih dari Aktivitas Investasi</td>
            <td class="amount">{{ number_format($investing_total, 2) }}</td>
        </tr>

        <!-- FINANCING ACTIVITIES -->
        <tr class="section-header bg-light">
            <td colspan="2">AKTIVITAS PENDANAAN</td>
        </tr>
        @foreach($financing_activities as $activity)
            <tr>
                <td class="indent-1">{{ $activity['name'] }}</td>
                <td class="amount">{{ number_format($activity['amount'], 2) }}</td>
            </tr>
        @endforeach
        <tr class="font-bold">
            <td class="indent-2">Kas Bersih dari Aktivitas Pendanaan</td>
            <td class="amount">{{ number_format($financing_total, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="bg-light font-bold border-top-double">
            <td>KENAIKAN (PENURUNAN) BERSIH KAS & SETARA KAS</td>
            <td class="amount">{{ number_format($net_cash_flow, 2) }}</td>
        </tr>
        <tr>
            <td>SALDO KAS & SETARA KAS - AWAL PERIODE</td>
            <td class="amount">{{ number_format($beginning_cash, 2) }}</td>
        </tr>
        <tr class="bg-light font-bold">
            <td>SALDO KAS & SETARA KAS - AKHIR PERIODE</td>
            <td class="amount">{{ number_format($ending_cash, 2) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
