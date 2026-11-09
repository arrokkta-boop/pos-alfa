@extends('layouts.admin')

@section('title', 'Detail Transaksi ' . $sale->invoice_number)

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between pb-2">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-slate-900 leading-tight">Detail Transaksi</h1>
                <p class="text-[11px] text-purple-700 font-mono font-bold">{{ $sale->invoice_number }}</p>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
            SELESAI
        </span>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-2 text-xs">
        <div class="flex justify-between py-1 border-b border-slate-100">
            <span class="text-slate-500">Waktu Transaksi</span>
            <span class="font-medium text-slate-800">{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
            <span class="text-slate-500">Kasir Bertugas</span>
            <span class="font-bold text-slate-800">{{ $sale->user->name }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
            <span class="text-slate-500">Metode Bayar</span>
            <span class="font-medium text-slate-800">Tunai / Cash</span>
        </div>
        <div class="flex justify-between py-1">
            <span class="text-slate-500">Total Keuntungan Transaksi</span>
            <span class="font-bold text-emerald-600 mono">+Rp {{ number_format($sale->total_profit, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider">
            Rincian Barang Terjual
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($sale->saleDetails as $item)
            <div class="py-2.5 space-y-1">
                <div class="flex justify-between text-xs font-semibold text-slate-900">
                    <span>{{ $item->product->product_name ?? 'Produk' }}</span>
                    <span class="mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-500">
                    <span>{{ $item->qty }} x Rp {{ number_format($item->sell_price, 0, ',', '.') }}</span>
                    <span class="text-emerald-600 font-mono text-[10px]">Laba: Rp {{ number_format($item->profit, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="pt-3 border-t border-slate-200 space-y-1.5 text-xs">
            <div class="flex justify-between font-bold text-sm text-slate-900">
                <span>Total Belanja:</span>
                <span class="mono">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Uang Diterima:</span>
                <span class="mono">Rp {{ number_format($sale->pay_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Uang Kembalian:</span>
                <span class="mono">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
