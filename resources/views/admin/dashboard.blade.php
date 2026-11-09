@extends('layouts.admin')

@section('title', 'Executive Dashboard')

@section('content')
<div class="bg-indigo-50/80 border border-indigo-200/80 rounded-2xl p-3 flex items-center justify-between text-xs text-indigo-900">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-shield-halved text-indigo-600"></i>
        <span>Hak Akses: <strong>Read-Only</strong> (Pemilik Toko & Monitoring)</span>
    </div>
    <span class="text-[10px] text-indigo-600 font-semibold">Toko ALFA</span>
</div>

<div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-sm space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-regular fa-calendar-check text-purple-600"></i>
            <span>Filter Periode Laporan</span>
        </span>
        <span class="text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">
            {{ $filterLabel }}
        </span>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <a href="{{ route('admin.dashboard', ['filter' => 'today']) }}"
            class="py-2 text-center text-xs font-semibold rounded-xl transition {{ $filter === 'today' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Hari Ini
        </a>
        <a href="{{ route('admin.dashboard', ['filter' => 'month']) }}"
            class="py-2 text-center text-xs font-semibold rounded-xl transition {{ $filter === 'month' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Bulan Ini
        </a>
        <button type="button" onclick="toggleCustomDate()"
            class="py-2 text-center text-xs font-semibold rounded-xl transition {{ $filter === 'custom' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Custom <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
        </button>
    </div>

    <div id="custom-date-box" class="{{ $filter === 'custom' ? '' : 'hidden' }} pt-2 border-t border-slate-100">
        <form action="{{ route('admin.dashboard') }}" method="GET" class="space-y-2">
            <input type="hidden" name="filter" value="custom">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDateInput ?: date('Y-m-d') }}" required
                        class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDateInput ?: date('Y-m-d') }}" required
                        class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono focus:outline-none focus:ring-1 focus:ring-purple-500">
                </div>
            </div>
            <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">
                Terapkan Rentang Tanggal
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div class="bg-gradient-to-br from-indigo-900 to-indigo-800 text-white rounded-2xl p-4 shadow-md shadow-indigo-900/10">
        <div class="flex items-center justify-between text-indigo-200 text-xs font-semibold">
            <span>Total Omset</span>
            <i class="fa-solid fa-coins"></i>
        </div>
        <div class="text-xl sm:text-2xl font-black mono mt-2 tracking-tight">
            Rp {{ number_format($totalOmset, 0, ',', '.') }}
        </div>
        <div class="text-[10px] text-indigo-300 mt-1">Total Nilai Penjualan</div>
    </div>

    <div class="bg-gradient-to-br from-emerald-800 to-emerald-700 text-white rounded-2xl p-4 shadow-md shadow-emerald-900/10">
        <div class="flex items-center justify-between text-emerald-200 text-xs font-semibold">
            <span>Laba Bersih</span>
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
        <div class="text-xl sm:text-2xl font-black mono mt-2 tracking-tight">
            Rp {{ number_format($totalNetProfit, 0, ',', '.') }}
        </div>
        <div class="text-[10px] text-emerald-200 mt-1">Margin: Sell &minus; Buy Price</div>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase">Total Transaksi</div>
            <div class="text-lg font-bold text-slate-800 mono mt-0.5">{{ $totalSalesCount }} Struk</div>
        </div>
        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </div>

    <a href="{{ route('admin.low-stock') }}" class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-sm flex items-center justify-between hover:border-amber-400 transition group">
        <div>
            <div class="text-[10px] font-semibold text-slate-400 uppercase">Stok Kritis (&le; Min)</div>
            <div class="text-lg font-bold text-amber-600 mono mt-0.5">{{ $lowStockCount }} SKU</div>
        </div>
        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-100 flex items-center justify-center text-sm transition">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
    </a>
</div>

@if ($lowStockCount > 0)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5">
    <div class="flex items-center justify-between pb-2 border-b border-amber-200/60 mb-2">
        <span class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
            <i class="fa-solid fa-bell text-amber-600"></i>
            <span>Peringatan Stok Menipis</span>
        </span>
        <a href="{{ route('admin.low-stock') }}" class="text-[11px] font-semibold text-amber-700 hover:underline">
            Lihat Semua ({{ $lowStockCount }}) &rarr;
        </a>
    </div>
    <div class="space-y-1.5">
        @foreach ($lowStockProducts->take(3) as $lp)
        <div class="flex items-center justify-between text-xs bg-white/70 p-2 rounded-xl">
            <div class="truncate mr-2">
                <div class="font-bold text-slate-800 truncate">{{ $lp->product_name }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ $lp->barcode_code }}</div>
            </div>
            <div class="text-right whitespace-nowrap">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 mono">
                    Sisa {{ $lp->stock }} pcs
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if ($topProducts->isNotEmpty())
<div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
    <div class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center justify-between">
        <span class="flex items-center gap-1.5">
            <i class="fa-solid fa-fire text-orange-500"></i>
            <span>Produk Terlaris Periode Ini</span>
        </span>
        <span class="text-[10px] text-slate-400 font-normal">By Qty Terjual</span>
    </div>
    <div class="space-y-2">
        @foreach ($topProducts as $idx => $tp)
        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 last:border-0">
            <div class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold flex items-center justify-center">
                    {{ $idx + 1 }}
                </span>
                <span class="font-semibold text-slate-800 truncate max-w-[170px] sm:max-w-xs">{{ $tp->product->product_name ?? 'Item' }}</span>
            </div>
            <div class="text-right">
                <div class="font-bold text-slate-900 mono">{{ $tp->total_qty }} pcs</div>
                <div class="text-[10px] text-emerald-600 font-semibold mono">+Rp {{ number_format($tp->total_profit, 0, ',', '.') }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-list-check text-purple-600"></i>
            <span>Daftar Transaksi Penjualan</span>
        </span>
        <span class="text-[11px] text-slate-400 font-mono">{{ $sales->total() }} Data</span>
    </div>

    <div class="space-y-2.5">
        @forelse ($sales as $sale)
        <a href="{{ route('admin.sale.detail', $sale->id) }}" class="block p-3 rounded-xl border border-slate-100 hover:border-purple-300 hover:bg-purple-50/20 transition">
            <div class="flex items-center justify-between text-xs">
                <span class="font-mono font-bold text-purple-700">{{ $sale->invoice_number }}</span>
                <span class="font-bold text-slate-900 mono">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                <span>{{ $sale->created_at->format('d/m/Y H:i') }} &bull; Kasir: {{ $sale->user->name }}</span>
                <span class="text-emerald-600 font-semibold mono">Laba: Rp {{ number_format($sale->total_profit, 0, ',', '.') }}</span>
            </div>
        </a>
        @empty
        <div class="text-center py-8 text-slate-400 text-xs">
            Belum ada data penjualan pada rentang periode ini.
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCustomDate() {
        const box = document.getElementById('custom-date-box');
        box.classList.toggle('hidden');
    }
</script>
@endpush
