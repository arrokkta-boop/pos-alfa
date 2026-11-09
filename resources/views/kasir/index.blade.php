@extends('layouts.pos')

@section('title', 'POS Kasir Terminal')

@section('content')
<div class="flex-1 flex w-full h-full bg-slate-900">
    <!-- LEFT PANEL: Barcode Input & Cart (65% width) -->
    <div class="flex-1 flex flex-col border-r border-slate-800 bg-slate-900">
        <!-- BARCODE SCANNER BAR -->
        <div class="p-3 bg-slate-950/80 border-b border-slate-800 flex items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-red-500">
                    <i class="fa-solid fa-barcode text-xl"></i>
                </div>
                <input type="text" id="barcode-input" autofocus autocomplete="off"
                    placeholder="Scan Barcode atau ketik nama barang lalu tekan ENTER... [F2]"
                    class="w-full pl-12 pr-28 py-3 bg-slate-900 border-2 border-red-500/60 focus:border-red-500 rounded-xl text-white font-mono text-base placeholder-slate-500 focus:outline-none focus:ring-4 focus:ring-red-500/20 transition">
                <div class="absolute inset-y-0 right-1.5 flex items-center gap-1">
                    <button type="button" onclick="submitBarcode()" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah</span>
                    </button>
                </div>
            </div>

            <!-- Quick Catalogue Modal Trigger -->
            <button type="button" onclick="openCatalogueModal()" class="px-3.5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-boxes-stacked text-amber-400"></i>
                <span>Cari Barang</span>
            </button>
        </div>

        <!-- Notification Banner / Alert -->
        <div id="pos-alert" class="hidden px-4 py-2 text-xs font-medium flex items-center justify-between">
            <span id="pos-alert-message"></span>
            <button onclick="hideAlert()" class="text-xs opacity-75 hover:opacity-100">&times;</button>
        </div>

        <!-- CART TABLE -->
        <div class="flex-1 overflow-y-auto p-3">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px] pb-2">
                        <th class="py-2.5 px-3 w-12 text-center">No</th>
                        <th class="py-2.5 px-3">Kode / Nama Barang</th>
                        <th class="py-2.5 px-3 text-right w-28">Harga</th>
                        <th class="py-2.5 px-3 text-center w-36">Qty</th>
                        <th class="py-2.5 px-3 text-right w-32">Subtotal</th>
                        <th class="py-2.5 px-3 text-center w-12">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cart-items" class="divide-y divide-slate-800/60 font-medium">
                    <!-- Dynamic Cart Rows rendered here -->
                </tbody>
            </table>

            <!-- Empty Cart Placeholder -->
            <div id="cart-empty" class="h-64 flex flex-col items-center justify-center text-slate-500">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/50 flex items-center justify-center text-slate-600 text-3xl mb-3">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <p class="font-semibold text-slate-400">Keranjang Belanja Masih Kosong</p>
                <p class="text-xs text-slate-500 mt-1">Scan barcode barang atau klik tombol "Cari Barang" untuk memulai</p>
            </div>
        </div>

        <!-- BOTTOM STATUS BAR -->
        <div class="p-3 bg-slate-950 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <div class="flex items-center gap-4">
                <span>Total Item: <strong id="total-qty" class="text-white mono">0</strong></span>
                <span>Jenis Barang: <strong id="unique-items" class="text-white mono">0</strong></span>
            </div>
            <button type="button" onclick="clearCart()" class="text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1.5 transition">
                <i class="fa-solid fa-trash-can"></i>
                <span>Kosongkan Keranjang [F8]</span>
            </button>
        </div>
    </div>

    <!-- RIGHT PANEL: Total Display & Checkout Actions (35% width) -->
    <div class="w-96 flex flex-col bg-slate-950 p-4 justify-between border-l border-slate-800">
        <div>
            <!-- SHIFT / DAILY STATS -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-xl p-3 mb-4">
                <div class="flex items-center justify-between text-[11px] text-slate-400 pb-2 border-b border-slate-800">
                    <span>Transaksi Anda Hari Ini</span>
                    <a href="{{ route('kasir.history') }}" class="text-sky-400 hover:underline">Riwayat <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase">Struk Terbit</div>
                        <div class="text-base font-bold text-white mono">{{ $todaySalesCount }} Transaksi</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-500 uppercase">Omset Kasir</div>
                        <div class="text-base font-bold text-emerald-400 mono">Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <!-- TOTAL BILL DISPLAY -->
            <div class="bg-gradient-to-br from-red-950/40 to-slate-900 border-2 border-red-500/40 rounded-2xl p-5 mb-4 shadow-xl">
                <div class="text-xs uppercase tracking-wider text-red-400 font-semibold mb-1 flex items-center justify-between">
                    <span>Total Pembayaran</span>
                    <i class="fa-solid fa-receipt text-red-400"></i>
                </div>
                <div class="text-4xl font-black text-white mono tracking-tight" id="display-total">
                    Rp 0
                </div>
                <div class="text-[11px] text-slate-400 mt-2 flex items-center justify-between pt-2 border-t border-slate-800">
                    <span>Jumlah yang harus dibayar</span>
                    <span id="display-total-raw" class="text-slate-300 mono text-xs font-semibold">0 items</span>
                </div>
            </div>

            <!-- QUICK SHORTCUT INSTRUCTIONS -->
            <div class="space-y-2 text-xs text-slate-400 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
                <div class="font-semibold text-slate-300 text-[11px] uppercase tracking-wider mb-1">Panduan Kasir:</div>
                <div class="flex items-center justify-between">
                    <span>Scan Barcode</span>
                    <kbd class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-white font-mono text-[10px]">F2</kbd>
                </div>
                <div class="flex items-center justify-between">
                    <span>Buka Pembayaran</span>
                    <kbd class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-white font-mono text-[10px]">F4</kbd>
                </div>
                <div class="flex items-center justify-between">
                    <span>Reset Transaksi</span>
                    <kbd class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-white font-mono text-[10px]">F8</kbd>
                </div>
                <div class="flex items-center justify-between">
                    <span>Tutup Pop-up</span>
                    <kbd class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-white font-mono text-[10px]">ESC</kbd>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTON: BAYAR / CHECKOUT -->
        <div class="pt-4">
            <button type="button" id="btn-pay" onclick="openPaymentModal()"
                class="w-full py-4 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/30 text-lg flex items-center justify-center gap-3 transition">
                <i class="fa-solid fa-money-bill-wave text-xl"></i>
                <span>BAYAR (F4)</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL 1: PAYMENT -->
<div id="payment-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <h3 class="text-base font-bold text-white">Pembayaran Transaksi</h3>
            </div>
            <button onclick="closePaymentModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <div class="p-6 space-y-5">
            <div class="bg-slate-950 rounded-xl p-4 border border-slate-800 text-center">
                <div class="text-xs text-slate-400 uppercase font-semibold">Total Tagihan</div>
                <div id="modal-bill-total" class="text-3xl font-black text-white mono mt-1">Rp 0</div>
            </div>

            <div>
                <label for="pay-amount-input" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Jumlah Uang Diterima (Rp)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-slate-400 text-lg">Rp</span>
                    <input type="number" id="pay-amount-input" min="0" step="500"
                        placeholder="0"
                        oninput="calculateChange()"
                        onkeydown="handlePaymentKey(event)"
                        class="w-full pl-14 pr-4 py-3 bg-slate-950 border-2 border-emerald-500/60 focus:border-emerald-500 rounded-xl text-white font-mono text-2xl font-bold focus:outline-none focus:ring-4 focus:ring-emerald-500/20 transition">
                </div>
            </div>

            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilihan Cepat Nominal:</div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setExactPay()" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
                        Uang Pas
                    </button>
                    <button type="button" onclick="addPay(10000)" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono rounded-lg border border-slate-700 transition">
                        Rp 10.000
                    </button>
                    <button type="button" onclick="addPay(20000)" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono rounded-lg border border-slate-700 transition">
                        Rp 20.000
                    </button>
                    <button type="button" onclick="addPay(50000)" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono rounded-lg border border-slate-700 transition">
                        Rp 50.000
                    </button>
                    <button type="button" onclick="addPay(100000)" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono rounded-lg border border-slate-700 transition">
                        Rp 100.000
                    </button>
                    <button type="button" onclick="addPay(200000)" class="py-2 px-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono rounded-lg border border-slate-700 transition">
                        Rp 200.000
                    </button>
                </div>
            </div>

            <div id="change-card" class="rounded-xl p-4 border border-slate-800 bg-slate-950 flex items-center justify-between">
                <div>
                    <div class="text-xs text-slate-400 uppercase font-semibold">Kembalian</div>
                    <div id="modal-change-text" class="text-xs text-slate-500">Masukkan nominal pembayaran</div>
                </div>
                <div id="modal-change-total" class="text-2xl font-black text-amber-400 mono">
                    Rp 0
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-950 border-t border-slate-800 flex items-center justify-end gap-3">
            <button type="button" onclick="closePaymentModal()" class="px-4 py-2.5 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-xs font-semibold transition">
                Batal [ESC]
            </button>
            <button type="button" id="btn-process-checkout" onclick="processCheckout()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                <i class="fa-solid fa-check"></i>
                <span>Selesaikan Transaksi (ENTER)</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL 2: RECEIPT -->
<div id="receipt-modal" class="fixed inset-0 bg-black/85 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white text-slate-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-slate-100 p-3 border-b border-slate-200 flex items-center justify-between no-print">
            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <i class="fa-solid fa-receipt text-red-600"></i> Preview Struk Thermal
            </span>
            <button onclick="closeReceiptModal()" class="text-slate-400 hover:text-slate-700 text-base font-bold">&times;</button>
        </div>

        <div id="printable-receipt" class="p-6 overflow-y-auto font-mono text-[11px] leading-tight text-slate-900 bg-white">
            <div class="text-center pb-3 border-b border-dashed border-slate-300">
                <div class="text-base font-black tracking-wider">TOKO RETAIL ALFA</div>
                <div class="text-[10px] text-slate-600">Jl. Jendral Sudirman No. 88, Jakarta</div>
                <div class="text-[10px] text-slate-600">Telp: (021) 555-8899</div>
            </div>

            <div class="py-2.5 border-b border-dashed border-slate-300 space-y-0.5 text-[10px]">
                <div class="flex justify-between">
                    <span>No. Struk:</span>
                    <strong id="receipt-invoice">-</strong>
                </div>
                <div class="flex justify-between">
                    <span>Waktu:</span>
                    <span id="receipt-date">-</span>
                </div>
                <div class="flex justify-between">
                    <span>Kasir:</span>
                    <span id="receipt-cashier">-</span>
                </div>
            </div>

            <div class="py-2.5 border-b border-dashed border-slate-300">
                <table class="w-full text-left">
                    <tbody id="receipt-items-body" class="divide-y divide-slate-100">
                    </tbody>
                </table>
            </div>

            <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1">
                <div class="flex justify-between font-bold text-xs">
                    <span>TOTAL:</span>
                    <span id="receipt-total">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>TUNAI:</span>
                    <span id="receipt-pay">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span>KEMBALI:</span>
                    <span id="receipt-change">Rp 0</span>
                </div>
            </div>

            <div class="text-center pt-3 text-[10px] space-y-1">
                <div class="font-bold">*** TERIMA KASIH ***</div>
                <div class="text-slate-500">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</div>
                <div class="text-[9px] text-slate-400 mt-2">ALFA POS - Layanan Konsumen: 1500-888</div>
            </div>
        </div>

        <div class="p-3 bg-slate-100 border-t border-slate-200 flex items-center gap-2 no-print">
            <button onclick="printReceiptDirect()" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </button>
            <button onclick="closeReceiptModal()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-xs transition">
                Selesai
            </button>
        </div>
    </div>
</div>

<!-- MODAL 3: CATALOGUE -->
<div id="catalogue-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[85vh] flex flex-col">
        <div class="px-5 py-3.5 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-amber-400"></i>
                <h3 class="text-sm font-bold text-white">Katalog Barang Cepat</h3>
            </div>
            <button onclick="closeCatalogueModal()" class="text-slate-400 hover:text-white text-base">&times;</button>
        </div>
        <div class="p-3 bg-slate-950 border-b border-slate-800">
            <input type="text" id="catalogue-search-box" placeholder="Cari barcode atau nama produk..."
                oninput="filterCatalogue()"
                class="w-full px-3.5 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-red-500 font-mono">
        </div>
        <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="catalogue-list">
                @foreach ($products as $p)
                <button type="button" onclick="addProductFromCatalogue({{ json_encode($p) }})"
                    class="p-2.5 bg-slate-800/70 hover:bg-slate-800 border border-slate-700/60 hover:border-red-500 rounded-xl text-left transition flex items-center justify-between group catalogue-item"
                    data-search="{{ strtolower($p->barcode_code . ' ' . $p->product_name) }}">
                    <div>
                        <div class="text-xs font-semibold text-white group-hover:text-red-400 transition">{{ $p->product_name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                            {{ $p->barcode_code }} &bull; Stok: <span class="font-bold {{ $p->stock <= 5 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $p->stock }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-amber-400 mono">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</div>
                        <span class="text-[10px] text-slate-500 group-hover:text-red-300">Pilih +</span>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cart = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const barcodeInput = document.getElementById('barcode-input');
    const payAmountInput = document.getElementById('pay-amount-input');

    window.addEventListener('DOMContentLoaded', () => {
        focusBarcode();
    });

    function focusBarcode() {
        if (document.getElementById('payment-modal').classList.contains('hidden') &&
            document.getElementById('receipt-modal').classList.contains('hidden') &&
            document.getElementById('catalogue-modal').classList.contains('hidden')) {
            barcodeInput.focus();
        }
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'F2') {
            e.preventDefault();
            focusBarcode();
        } else if (e.key === 'F4') {
            e.preventDefault();
            if (document.getElementById('payment-modal').classList.contains('hidden')) {
                openPaymentModal();
            }
        } else if (e.key === 'F8') {
            e.preventDefault();
            clearCart();
        } else if (e.key === 'Escape') {
            closePaymentModal();
            closeReceiptModal();
            closeCatalogueModal();
            focusBarcode();
        }
    });

    barcodeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitBarcode();
        }
    });

    async function submitBarcode() {
        const query = barcodeInput.value.trim();
        if (!query) return;

        barcodeInput.disabled = true;

        try {
            const res = await fetch("{{ route('kasir.find-product') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ barcode: query })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                addToCart(data.product);
                barcodeInput.value = '';
                hideAlert();
            } else {
                showAlert(data.message || 'Barang tidak ditemukan', 'error');
            }
        } catch (err) {
            showAlert('Gagal mencari barang: ' + err.message, 'error');
        } finally {
            barcodeInput.disabled = false;
            focusBarcode();
        }
    }

    function addToCart(product) {
        const existing = cart.find(i => i.id === product.id);

        if (existing) {
            if (existing.qty + 1 > product.stock) {
                showAlert(`Stok "${product.product_name}" tersisa ${product.stock}. Tidak bisa menambah lagi.`, 'error');
                return;
            }
            existing.qty += 1;
        } else {
            if (product.stock < 1) {
                showAlert(`Stok "${product.product_name}" habis.`, 'error');
                return;
            }
            cart.push({
                id: product.id,
                barcode_code: product.barcode_code,
                product_name: product.product_name,
                sell_price: parseFloat(product.sell_price),
                stock: product.stock,
                qty: 1
            });
        }

        renderCart();
    }

    function updateQty(index, newQty) {
        const item = cart[index];
        const val = parseInt(newQty);

        if (isNaN(val) || val < 1) {
            removeFromCart(index);
            return;
        }

        if (val > item.stock) {
            showAlert(`Maksimal stok tersedia untuk "${item.product_name}" adalah ${item.stock}.`, 'error');
            item.qty = item.stock;
        } else {
            item.qty = val;
        }

        renderCart();
    }

    function changeQty(index, delta) {
        const item = cart[index];
        updateQty(index, item.qty + delta);
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function clearCart() {
        if (cart.length === 0) return;
        if (confirm('Kosongkan semua barang di keranjang?')) {
            cart = [];
            renderCart();
            hideAlert();
            focusBarcode();
        }
    }

    function renderCart() {
        const tbody = document.getElementById('cart-items');
        const emptyState = document.getElementById('cart-empty');
        tbody.innerHTML = '';

        if (cart.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }

        let grandTotal = 0;
        let totalQty = 0;

        cart.forEach((item, idx) => {
            const subtotal = item.qty * item.sell_price;
            grandTotal += subtotal;
            totalQty += item.qty;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-800/40 transition border-b border-slate-800/60';
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-slate-400 mono">${idx + 1}</td>
                <td class="py-2.5 px-3">
                    <div class="font-bold text-white text-xs">${escapeHtml(item.product_name)}</div>
                    <div class="text-[10px] text-slate-400 font-mono">${escapeHtml(item.barcode_code)} (Sisa: ${item.stock})</div>
                </td>
                <td class="py-2.5 px-3 text-right mono text-slate-200">Rp ${formatRupiah(item.sell_price)}</td>
                <td class="py-2.5 px-3 text-center">
                    <div class="inline-flex items-center rounded-lg bg-slate-950 border border-slate-700 overflow-hidden">
                        <button type="button" onclick="changeQty(${idx}, -1)" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">&minus;</button>
                        <input type="number" min="1" max="${item.stock}" value="${item.qty}"
                            onchange="updateQty(${idx}, this.value)"
                            class="w-12 py-1 text-center bg-transparent text-white font-mono text-xs focus:outline-none">
                        <button type="button" onclick="changeQty(${idx}, 1)" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">&plus;</button>
                    </div>
                </td>
                <td class="py-2.5 px-3 text-right font-bold text-amber-400 mono">Rp ${formatRupiah(subtotal)}</td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="removeFromCart(${idx})" class="p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-md transition" title="Hapus">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('display-total').innerText = 'Rp ' + formatRupiah(grandTotal);
        document.getElementById('display-total-raw').innerText = `${totalQty} items (${cart.length} jenis)`;
        document.getElementById('total-qty').innerText = totalQty;
        document.getElementById('unique-items').innerText = cart.length;
    }

    function openPaymentModal() {
        if (cart.length === 0) {
            showAlert('Keranjang masih kosong. Scan barang terlebih dahulu.', 'error');
            focusBarcode();
            return;
        }

        const grandTotal = getGrandTotal();
        document.getElementById('modal-bill-total').innerText = 'Rp ' + formatRupiah(grandTotal);
        payAmountInput.value = '';
        calculateChange();

        document.getElementById('payment-modal').classList.remove('hidden');
        setTimeout(() => {
            payAmountInput.focus();
        }, 100);
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        focusBarcode();
    }

    function getGrandTotal() {
        return cart.reduce((sum, i) => sum + (i.qty * i.sell_price), 0);
    }

    function calculateChange() {
        const grandTotal = getGrandTotal();
        const pay = parseFloat(payAmountInput.value) || 0;
        const change = pay - grandTotal;

        const changeEl = document.getElementById('modal-change-total');
        const textEl = document.getElementById('modal-change-text');
        const cardEl = document.getElementById('change-card');

        if (pay === 0) {
            changeEl.innerText = 'Rp 0';
            changeEl.className = 'text-2xl font-black text-slate-500 mono';
            textEl.innerText = 'Masukkan nominal pembayaran';
            cardEl.className = 'rounded-xl p-4 border border-slate-800 bg-slate-950 flex items-center justify-between';
        } else if (change < 0) {
            changeEl.innerText = '- Rp ' + formatRupiah(Math.abs(change));
            changeEl.className = 'text-2xl font-black text-rose-500 mono';
            textEl.innerText = 'Uang pembayaran masih kurang';
            cardEl.className = 'rounded-xl p-4 border border-rose-500/40 bg-rose-950/20 flex items-center justify-between';
        } else {
            changeEl.innerText = 'Rp ' + formatRupiah(change);
            changeEl.className = 'text-2xl font-black text-emerald-400 mono';
            textEl.innerText = change === 0 ? 'Uang Pas' : 'Kembalian Pelanggan';
            cardEl.className = 'rounded-xl p-4 border border-emerald-500/40 bg-emerald-950/20 flex items-center justify-between';
        }
    }

    function setExactPay() {
        payAmountInput.value = getGrandTotal();
        calculateChange();
    }

    function addPay(amount) {
        const current = parseFloat(payAmountInput.value) || 0;
        payAmountInput.value = current + amount;
        calculateChange();
    }

    function handlePaymentKey(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            processCheckout();
        }
    }

    async function processCheckout() {
        const grandTotal = getGrandTotal();
        const payAmount = parseFloat(payAmountInput.value);

        if (isNaN(payAmount) || payAmount < grandTotal) {
            alert('Jumlah pembayaran kurang dari total tagihan!');
            payAmountInput.focus();
            return;
        }

        const btn = document.getElementById('btn-process-checkout');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        const payload = {
            items: cart.map(i => ({ product_id: i.id, qty: i.qty })),
            pay_amount: payAmount
        };

        try {
            const res = await fetch("{{ route('kasir.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                closePaymentModal();
                showReceiptModal(data.sale);
                cart = [];
                renderCart();
            } else {
                alert(data.message || 'Gagal memproses transaksi.');
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Selesaikan Transaksi (ENTER)</span>';
        }
    }

    function showReceiptModal(sale) {
        document.getElementById('receipt-invoice').innerText = sale.invoice_number;
        document.getElementById('receipt-date').innerText = sale.created_at;
        document.getElementById('receipt-cashier').innerText = sale.cashier_name;

        const body = document.getElementById('receipt-items-body');
        body.innerHTML = '';

        sale.items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-1">
                    <div class="font-bold">${escapeHtml(item.name)}</div>
                    <div class="text-[10px] text-slate-500">${item.qty} x ${formatRupiah(item.price)}</div>
                </td>
                <td class="py-1 text-right font-bold align-top">
                    Rp ${formatRupiah(item.subtotal)}
                </td>
            `;
            body.appendChild(tr);
        });

        document.getElementById('receipt-total').innerText = 'Rp ' + formatRupiah(sale.total_amount);
        document.getElementById('receipt-pay').innerText = 'Rp ' + formatRupiah(sale.pay_amount);
        document.getElementById('receipt-change').innerText = 'Rp ' + formatRupiah(sale.change_amount);

        document.getElementById('receipt-modal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
        focusBarcode();
    }

    function printReceiptDirect() {
        window.print();
    }

    function openCatalogueModal() {
        document.getElementById('catalogue-modal').classList.remove('hidden');
        document.getElementById('catalogue-search-box').focus();
    }

    function closeCatalogueModal() {
        document.getElementById('catalogue-modal').classList.add('hidden');
        focusBarcode();
    }

    function filterCatalogue() {
        const query = document.getElementById('catalogue-search-box').value.toLowerCase().trim();
        const items = document.querySelectorAll('.catalogue-item');

        items.forEach(el => {
            const search = el.getAttribute('data-search');
            if (search.includes(query)) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    function addProductFromCatalogue(product) {
        addToCart(product);
        closeCatalogueModal();
    }

    function formatRupiah(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    function showAlert(msg, type = 'error') {
        const alertEl = document.getElementById('pos-alert');
        const msgEl = document.getElementById('pos-alert-message');
        msgEl.innerText = msg;
        if (type === 'error') {
            alertEl.className = 'px-4 py-2 text-xs font-semibold flex items-center justify-between bg-rose-950/80 border-b border-rose-500/50 text-rose-300';
        } else {
            alertEl.className = 'px-4 py-2 text-xs font-semibold flex items-center justify-between bg-emerald-950/80 border-b border-emerald-500/50 text-emerald-300';
        }
        alertEl.classList.remove('hidden');
    }

    function hideAlert() {
        document.getElementById('pos-alert').classList.add('hidden');
    }
</script>
@endpush
