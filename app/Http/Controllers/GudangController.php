<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GudangController extends Controller
{
    /**
     * Display listing of products in warehouse with search and stock status.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $filterStock = $request->input('stock_status', 'all');

        $query = Product::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($filterStock === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock');
        } elseif ($filterStock === 'out') {
            $query->where('stock', '<=', 0);
        }

        $products = $query->orderBy('product_name')->paginate(10)->withQueryString();

        // Warehouse stats
        $totalProducts = Product::count();
        $totalStockUnits = Product::sum('stock');
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        return view('gudang.index', compact('products', 'search', 'filterStock', 'totalProducts', 'totalStockUnits', 'lowStockCount'));
    }

    /**
     * Store a newly created product in database.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'barcode_code' => ['required', 'string', 'max:50', 'unique:products,barcode_code'],
            'product_name' => ['required', 'string', 'max:150'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0', 'gte:buy_price'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ], [
            'barcode_code.required' => 'Kode Barcode wajib diisi.',
            'barcode_code.unique' => 'Kode Barcode ini sudah terdaftar di sistem.',
            'product_name.required' => 'Nama Produk wajib diisi.',
            'buy_price.required' => 'Harga Beli wajib diisi.',
            'sell_price.required' => 'Harga Jual wajib diisi.',
            'sell_price.gte' => 'Harga Jual harus lebih besar atau sama dengan Harga Beli.',
            'stock.required' => 'Stok Awal wajib diisi.',
            'min_stock.required' => 'Stok Minimal wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            if ($product->stock > 0) {
                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'qty' => $product->stock,
                    'notes' => 'Stok awal saat pendaftaran barang baru',
                ]);
            }
        });

        return redirect()->route('gudang.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    /**
     * Update the specified product.
     */
    public function updateProduct(Request $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'barcode_code' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'product_name' => ['required', 'string', 'max:150'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0', 'gte:buy_price'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ], [
            'barcode_code.required' => 'Kode Barcode wajib diisi.',
            'barcode_code.unique' => 'Kode Barcode ini sudah digunakan produk lain.',
            'product_name.required' => 'Nama Produk wajib diisi.',
            'buy_price.required' => 'Harga Beli wajib diisi.',
            'sell_price.required' => 'Harga Jual wajib diisi.',
            'sell_price.gte' => 'Harga Jual harus lebih besar atau sama dengan Harga Beli.',
            'min_stock.required' => 'Stok Minimal wajib diisi.',
        ]);

        $product->update($validated);

        return redirect()->route('gudang.index')->with('success', "Data barang '{$product->product_name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroyProduct(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        if ($product->saleDetails()->exists()) {
            return back()->with('error', "Barang '{$product->product_name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan. Anda dapat mengubah stoknya menjadi 0.");
        }

        DB::transaction(function () use ($product) {
            $product->stockLogs()->delete();
            $product->delete();
        });

        return redirect()->route('gudang.index')->with('success', "Barang '{$product->product_name}' berhasil dihapus.");
    }

    /**
     * Process Stock In (Stok Masuk dari Supplier).
     */
    public function stockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'product_id.required' => 'Pilih barang yang ingin ditambah stoknya.',
            'qty.required' => 'Jumlah stok masuk wajib diisi.',
            'qty.min' => 'Jumlah stok masuk minimal 1.',
        ]);

        DB::transaction(function () use ($validated) {
            /** @var Product $product */
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->firstOrFail();
            $qty = (int) $validated['qty'];

            $product->increment('stock', $qty);

            StockLog::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'qty' => $qty,
                'notes' => $validated['notes'] ?: 'Stok Masuk dari Supplier / Pembelian',
            ]);
        });

        return redirect()->route('gudang.index')->with('success', 'Stok masuk berhasil dicatat dan stok barang bertambah.');
    }

    /**
     * Process Stock Adjustment (Penyesuaian Stok / Opname).
     */
    public function stockAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'actual_stock' => ['required', 'integer', 'min:0'],
            'notes' => ['required', 'string', 'max:255'],
        ], [
            'product_id.required' => 'Pilih barang yang disesuaikan stoknya.',
            'actual_stock.required' => 'Stok aktual fisik hasil opname wajib diisi.',
            'notes.required' => 'Keterangan/alasan penyesuaian (misal: barang rusak, kadaluarsa, selisih opname) wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            /** @var Product $product */
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->firstOrFail();
            $oldStock = $product->stock;
            $newStock = (int) $validated['actual_stock'];
            $diff = $newStock - $oldStock;

            $product->update(['stock' => $newStock]);

            $diffText = $diff >= 0 ? "+{$diff}" : "{$diff}";

            StockLog::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'adjustment',
                'qty' => $diff,
                'notes' => "Penyesuaian Stok (Lama: {$oldStock}, Baru: {$newStock}, Selisih: {$diffText}). Alasan: {$validated['notes']}",
            ]);
        });

        return redirect()->route('gudang.index')->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    /**
     * Display complete stock logs history.
     */
    public function stockLogs(Request $request): View
    {
        $type = $request->input('type');
        $search = trim($request->input('search', ''));

        $query = StockLog::with(['product', 'user']);

        if ($type && in_array($type, ['in', 'out', 'adjustment'])) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('barcode_code', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $products = Product::orderBy('product_name')->get(['id', 'barcode_code', 'product_name', 'stock', 'min_stock']);

        return view('gudang.logs', compact('logs', 'products', 'type', 'search'));
    }
}
