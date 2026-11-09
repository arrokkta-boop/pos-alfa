@extends('layouts.gudang')

@section('title', 'Master Data Barang & Stok')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Item Produk</div>
                <div class="text-2xl font-bold text-slate-900 mt-1 mono">{{ number_format($totalProducts) }} <span class="text-xs font-normal text-slate-400">SKU</span></div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-archive"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Stok Fisik</div>
                <div class="text-2xl font-bold text-slate-900 mt-1 mono">{{ number_format($totalStockUnits) }} <span class="text-xs font-normal text-slate-400">Pcs</span></div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-cubes"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Menipis (&le; Min)</div>
                <div class="text-2xl font-bold text-amber-600 mt-1 mono">{{ number_format($lowStockCount) }} <span class="text-xs font-normal text-slate-400">Barang</span></div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('gudang.index') }}" method="GET" class="w-full md:w-auto flex-1 flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari barcode atau nama barang..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition font-mono">
            </div>

            <select name="stock_status" onchange="this.form.submit()" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all" {{ $filterStock === 'all' ? 'selected' : '' }}>Semua Status Stok</option>
                <option value="low" {{ $filterStock === 'low' ? 'selected' : '' }}>Stok Menipis (&le; Min)</option>
                <option value="out" {{ $filterStock === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition">
                Filter
            </button>

            @if($search || $filterStock !== 'all')
                <a href="{{ route('gudang.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 transition">
                    Reset
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 w-full md:w-auto justify-end">
            <button type="button" onclick="openStockInModal()"
                class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/20 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Stok Masuk</span>
            </button>

            <button type="button" onclick="openAdjustmentModal()"
                class="px-3.5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-amber-600/20 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-sliders"></i>
                <span>Penyesuaian Stok</span>
            </button>

            <button type="button" onclick="openCreateProductModal()"
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-blue-600/20 transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Barang Baru</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Barcode / SKU</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-4 text-right">Harga Beli</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                        <th class="py-3 px-4 text-center">Min. Stok</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($products as $idx => $p)
                    <tr class="hover:bg-slate-50/80 transition {{ $p->is_low_stock ? 'bg-amber-50/40' : '' }}">
                        <td class="py-3.5 px-4 text-center text-slate-400 mono">{{ $products->firstItem() + $idx }}</td>
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                {{ $p->barcode_code }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-xs">{{ $p->product_name }}</div>
                            @if ($p->is_low_stock)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Stok Menipis
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right mono text-slate-600">Rp {{ number_format($p->buy_price, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right mono font-bold text-slate-900">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-xs font-bold mono {{ $p->stock <= 0 ? 'bg-rose-100 text-rose-800' : ($p->is_low_stock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $p->stock }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center mono text-slate-500">{{ $p->min_stock }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick='quickStockIn({{ json_encode($p) }})' title="Stok Masuk" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                    <i class="fa-solid fa-truck-ramp-box"></i>
                                </button>
                                <button type="button" onclick='openEditProductModal({{ json_encode($p) }})' title="Edit Barang" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('gudang.product.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Barang" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            Tidak ada barang ditemukan. Silakan tambah data barang baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- MODAL: TAMBAH BARANG -->
<div id="create-product-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-blue-600"></i>
                <span>Tambah Master Data Barang</span>
            </h3>
            <button onclick="closeModal('create-product-modal')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="{{ route('gudang.product.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Kode Barcode / QR Code *</label>
                <div class="relative">
                    <input type="text" name="barcode_code" required autofocus placeholder="Contoh: 8992388111018"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Produk / Barang *</label>
                <input type="text" name="product_name" required placeholder="Contoh: Indomie Goreng Spesial 85g"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Harga Beli (Rp) *</label>
                    <input type="number" name="buy_price" min="0" step="100" required placeholder="2600"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Harga Jual (Rp) *</label>
                    <input type="number" name="sell_price" min="0" step="100" required placeholder="3200"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Stok Awal Fisik *</label>
                    <input type="number" name="stock" min="0" required placeholder="50"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Batas Minimal Stok *</label>
                    <input type="number" name="min_stock" min="0" required value="5" placeholder="5"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('create-product-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                    Simpan Barang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT BARANG -->
<div id="edit-product-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen text-blue-600"></i>
                <span>Edit Data Barang</span>
            </h3>
            <button onclick="closeModal('edit-product-modal')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form id="edit-product-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Kode Barcode / QR Code *</label>
                <input type="text" id="edit-barcode" name="barcode_code" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Produk / Barang *</label>
                <input type="text" id="edit-name" name="product_name" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Harga Beli (Rp) *</label>
                    <input type="number" id="edit-buy-price" name="buy_price" min="0" step="100" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Harga Jual (Rp) *</label>
                    <input type="number" id="edit-sell-price" name="sell_price" min="0" step="100" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Batas Minimal Stok *</label>
                <input type="number" id="edit-min-stock" name="min_stock" min="0" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('edit-product-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: STOK MASUK -->
<div id="stock-in-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-emerald-900 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                <span>Input Stok Masuk (Stock In dari Supplier)</span>
            </h3>
            <button onclick="closeModal('stock-in-modal')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="{{ route('gudang.stock.in') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Pilih Barang *</label>
                <select name="product_id" id="stock-in-product-id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                    <option value="">-- Pilih Barang --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->product_name }} (Stok saat ini: {{ $p->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Jumlah Stok Masuk (Pcs) *</label>
                <input type="number" name="qty" min="1" required placeholder="Contoh: 24"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Catatan / Supplier</label>
                <input type="text" name="notes" placeholder="Contoh: Supplier PT Indofood / PO-2026-001"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('stock-in-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                    Simpan Stok Masuk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: PENYESUAIAN STOK -->
<div id="stock-adjustment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-amber-50 border-b border-amber-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-amber-600"></i>
                <span>Penyesuaian Stok (Stock Opname / Koreksi)</span>
            </h3>
            <button onclick="closeModal('stock-adjustment-modal')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="{{ route('gudang.stock.adjustment') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Pilih Barang yang Disesuaikan *</label>
                <select name="product_id" id="adjustment-product-id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-medium">
                    <option value="">-- Pilih Barang --</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->product_name }} (Stok sistem: {{ $p->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Stok Aktual Hasil Fisik (Pcs) *</label>
                <input type="number" name="actual_stock" min="0" required placeholder="0"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-amber-500">
                <p class="text-[11px] text-slate-400 mt-1">Masukkan jumlah stok riil fisik di rak/gudang.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Alasan Penyesuaian *</label>
                <input type="text" name="notes" required placeholder="Contoh: 2 pcs kemasan bocor / selisih opname fisik bulanan"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('stock-adjustment-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                    Simpan Penyesuaian
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function openCreateProductModal() {
        document.getElementById('create-product-modal').classList.remove('hidden');
    }

    function openStockInModal() {
        document.getElementById('stock-in-modal').classList.remove('hidden');
    }

    function openAdjustmentModal() {
        document.getElementById('stock-adjustment-modal').classList.remove('hidden');
    }

    function quickStockIn(product) {
        const select = document.getElementById('stock-in-product-id');
        select.value = product.id;
        openStockInModal();
    }

    function openEditProductModal(product) {
        const form = document.getElementById('edit-product-form');
        form.action = `/gudang/product/${product.id}`;
        document.getElementById('edit-barcode').value = product.barcode_code;
        document.getElementById('edit-name').value = product.product_name;
        document.getElementById('edit-buy-price').value = parseInt(product.buy_price);
        document.getElementById('edit-sell-price').value = parseInt(product.sell_price);
        document.getElementById('edit-min-stock').value = product.min_stock;

        document.getElementById('edit-product-modal').classList.remove('hidden');
    }
</script>
@endpush
