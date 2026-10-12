@extends('reports.exports.layout')

@section('title', 'Laporan Neraca')

@section('content')
<table>
    <thead>
        <tr>
            <th>Deskripsi Akun</th>
            <th class="amount">Saldo (IDR)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="section-header bg-light">
            <td>ASET</td>
            <td class="amount">{{ number_format($assets['total'] ?? 0, 2) }}</td>
        </tr>
        @foreach($details as $row)
            @if(($row->total_assets ?? 0) != 0)
                <tr>
                    <td class="indent-1">{{ $row->account_name }}</td>
                    <td class="amount">{{ number_format($row->total_assets, 2) }}</td>
                </tr>
            @endif
        @endforeach

        <tr class="section-header bg-light">
            <td>KEWAJIBAN (LIABILITAS)</td>
            <td class="amount">{{ number_format($liabilities['total'] ?? 0, 2) }}</td>
        </tr>
        @foreach($details as $row)
            @if(($row->total_liabilities ?? 0) != 0)
                <tr>
                    <td class="indent-1">{{ $row->account_name }}</td>
                    <td class="amount">{{ number_format($row->total_liabilities, 2) }}</td>
                </tr>
            @endif
        @endforeach

        <tr class="section-header bg-light">
            <td>EKUITAS (MODAL)</td>
            <td class="amount">{{ number_format($equity['total'] ?? 0, 2) }}</td>
        </tr>
        @foreach($details as $row)
            @if(($row->total_equity ?? 0) != 0)
                <tr>
                    <td class="indent-1">{{ $row->account_name }}</td>
                    <td class="amount">{{ number_format($row->total_equity, 2) }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-bold border-top-double">
            <td>TOTAL KEWAJIBAN & EKUITAS</td>
            <td class="amount">{{ number_format($total_liabilities_and_equity ?? 0, 2) }}</td>
        </tr>
        <tr class="bg-light font-bold">
            <td>KESEIMBANGAN (BALANCE)</td>
            <td class="amount text-center {{ $is_balanced ? 'text-success' : 'text-danger' }}">
                {{ $is_balanced ? 'SEIMBANG (BALANCED)' : 'IDAK SEIMBANG (UNBALANCED)' }}
            </td>
        </tr>
    </tfoot>
</table>
@endsection
