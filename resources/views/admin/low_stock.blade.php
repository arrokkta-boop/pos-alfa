@extends('layouts.admin')

@section('title', 'Notifikasi Stok Menipis')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between pb-2">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-slate-900 leading-tight">Barang Stok Kritis</h1>
                <p class="text-[11px] text-slate-500">Stok fisik saat ini &le; Stok minimal produk</p>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 mono">
            {{ $products->count() }} Item
        </span>
    </div>

    <div class="space-y-2.5">
        @forelse ($products as $p)
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div class="space-y-1 pr-3">
                <div class="font-bold text-xs text-slate-900 leading-snug">{{ $p->product_name }}</div>
                <div class="text-[10px] text-slate-400 font-mono">Barcode: {{ $p->barcode_code }}</div>
                <div class="text-[11px] text-slate-500">
                    Harga Jual: <span class="font-bold text-slate-700 mono">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="text-right flex-shrink-0">
                <div class="inline-flex flex-col items-end">
                    <span class="text-[10px] uppercase font-semibold text-slate-400">Sisa Stok</span>
                    <span class="px-2.5 py-0.5 rounded-lg text-sm font-black mono {{ $p->stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $p->stock }} pcs
                    </span>
                    <span class="text-[10px] text-slate-400 mt-0.5">Min: {{ $p->min_stock }} pcs</span>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl p-8 text-center text-slate-400 text-xs border border-slate-200">
            <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2"></i>
            <div class="font-bold text-slate-700">Semua Stok Aman</div>
            <p class="text-slate-400 mt-1">Tidak ada produk yang berada di bawah stok minimal saat ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
