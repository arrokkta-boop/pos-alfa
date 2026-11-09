@extends('layouts.pos')

@section('title', 'Riwayat Transaksi Kasir')

@section('content')
<div class="flex-1 flex flex-col w-full h-full bg-slate-900 p-6 overflow-y-auto">
    <div class="max-w-6xl mx-auto w-full">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('kasir.pos') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-white">Riwayat Transaksi Kasir</h1>
                    <p class="text-xs text-slate-400">Daftar penjualan yang telah Anda proses</p>
                </div>
            </div>
            <a href="{{ route('kasir.pos') }}" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2">
                <i class="fa-solid fa-cash-register"></i>
                <span>Kembali ke POS Kasir</span>
            </a>
        </div>

        <!-- Sales Table -->
        <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-slate-400 uppercase text-[11px] font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">No. Invoice</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Jumlah Item</th>
                        <th class="py-3 px-4 text-right">Total Transaksi</th>
                        <th class="py-3 px-4 text-right">Bayar</th>
                        <th class="py-3 px-4 text-right">Kembalian</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium text-slate-200">
                    @forelse ($sales as $sale)
                    <tr class="hover:bg-slate-900/60 transition">
                        <td class="py-3 px-4 font-mono font-bold text-red-400">{{ $sale->invoice_number }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-slate-300">{{ $sale->saleDetails->sum('qty') }} pcs</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-white">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono text-slate-300">Rp {{ number_format($sale->pay_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-mono text-amber-400">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('kasir.receipt', $sale->id) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-[11px] font-semibold transition">
                                <i class="fa-solid fa-print text-red-400"></i>
                                <span>Cetak Ulang</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            Belum ada riwayat transaksi penjualan hari ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection
