@extends('layouts.gudang')

@section('title', 'Riwayat Log Stok')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Riwayat Mutasi & Log Stok Barang</h1>
            <p class="text-xs text-slate-500">Pencatatan otomatis setiap stok masuk, penjualan POS, dan penyesuaian opname</p>
        </div>
        <a href="{{ route('gudang.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Master Barang</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('gudang.logs') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama barang atau barcode..."
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <select name="type" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe Mutasi</option>
                <option value="in" {{ $type === 'in' ? 'selected' : '' }}>Stok Masuk (In)</option>
                <option value="out" {{ $type === 'out' ? 'selected' : '' }}>Stok Keluar / Terjual (Out)</option>
                <option value="adjustment" {{ $type === 'adjustment' ? 'selected' : '' }}>Penyesuaian (Adjustment)</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition">
                Filter
            </button>

            @if($search || $type)
                <a href="{{ route('gudang.logs') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Waktu / Tanggal</th>
                        <th class="py-3 px-4">Barang</th>
                        <th class="py-3 px-4 text-center">Tipe</th>
                        <th class="py-3 px-4 text-center">Qty Mutasi</th>
                        <th class="py-3 px-4">Keterangan / Alasan</th>
                        <th class="py-3 px-4">Petugas / User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($logs as $idx => $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 text-center text-slate-400 mono">{{ $logs->firstItem() + $idx }}</td>
                        <td class="py-3.5 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $log->product->product_name ?? 'Produk Dihapus' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $log->product->barcode_code ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if ($log->type === 'in')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fa-solid fa-arrow-down"></i> MASUK
                                </span>
                            @elseif ($log->type === 'out')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <i class="fa-solid fa-arrow-up"></i> KELUAR
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-sliders"></i> PENYESUAIAN
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center mono font-bold text-xs">
                            @if ($log->type === 'in')
                                <span class="text-emerald-600">+{{ $log->qty }}</span>
                            @elseif ($log->type === 'out')
                                <span class="text-rose-600">-{{ $log->qty }}</span>
                            @else
                                <span class="text-amber-600">{{ $log->qty >= 0 ? '+' . $log->qty : $log->qty }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs max-w-xs">
                            {{ $log->notes ?: '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs whitespace-nowrap">
                            <i class="fa-solid fa-user-circle text-slate-400 mr-1"></i>
                            {{ $log->user->name ?? 'Sistem' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Belum ada riwayat mutasi stok tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
