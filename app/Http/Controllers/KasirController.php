<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Display the POS Kasir Desktop Interface.
     */
    public function index(): View
    {
        $today = Carbon::today();

        // Cashier stats for today
        $todaySalesCount = Sale::where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->count();

        $todaySalesTotal = Sale::where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // Recent sales today
        $recentSales = Sale::with(['saleDetails.product'])
            ->where('user_id', Auth::id())
            ->whereDate('created_at', $today)
            ->latest()
            ->take(10)
            ->get();

        // Products catalogue for quick pick or local lookup
        $products = Product::where('stock', '>', 0)
            ->orderBy('product_name')
            ->get(['id', 'barcode_code', 'product_name', 'sell_price', 'stock']);

        return view('kasir.index', compact('todaySalesCount', 'todaySalesTotal', 'recentSales', 'products'));
    }

    /**
     * Find a product by exact barcode or search keyword (for Barcode Scanner & Search input).
     */
    public function findProduct(Request $request): JsonResponse
    {
        $query = trim($request->input('barcode', ''));

        if ($query === '') {
            return response()->json(['success' => false, 'message' => 'Barcode tidak boleh kosong.'], 400);
        }

        // Exact match by barcode first
        $product = Product::where('barcode_code', $query)->first();

        // If not found, try search by name
        if (! $product) {
            $product = Product::where('product_name', 'like', "%{$query}%")->first();
        }

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => "Barang dengan barcode/kode '{$query}' tidak ditemukan.",
            ], 404);
        }

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => "Stok produk '{$product->product_name}' sedang habis (0).",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'barcode_code' => $product->barcode_code,
                'product_name' => $product->product_name,
                'sell_price' => (float) $product->sell_price,
                'stock' => $product->stock,
            ],
        ]);
    }

    /**
     * Process checkout transaction with DB::transaction() and stock decrement.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'pay_amount' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Keranjang belanja masih kosong.',
            'items.min' => 'Pilih minimal 1 barang untuk checkout.',
            'pay_amount.required' => 'Jumlah bayar wajib diisi.',
            'pay_amount.numeric' => 'Jumlah bayar harus berupa angka valid.',
        ]);

        try {
            $sale = DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                $detailsToCreate = [];

                // 1. Lock and validate all products & stock
                foreach ($validated['items'] as $item) {
                    /** @var Product $product */
                    $product = Product::where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $qty = (int) $item['qty'];

                    if ($product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'stock' => "Stok untuk produk '{$product->product_name}' tidak mencukupi. Sisa stok: {$product->stock}, diminta: {$qty}.",
                        ]);
                    }

                    $subtotal = $qty * (float) $product->sell_price;
                    $profit = ($product->sell_price - $product->buy_price) * $qty;
                    $totalAmount += $subtotal;

                    $detailsToCreate[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'buy_price' => $product->buy_price,
                        'sell_price' => $product->sell_price,
                        'subtotal' => $subtotal,
                        'profit' => $profit,
                    ];
                }

                $payAmount = (float) $validated['pay_amount'];

                if ($payAmount < $totalAmount) {
                    $kurang = number_format($totalAmount - $payAmount, 0, ',', '.');
                    throw ValidationException::withMessages([
                        'pay_amount' => "Jumlah pembayaran kurang Rp {$kurang}. Total tagihan: Rp " . number_format($totalAmount, 0, ',', '.'),
                    ]);
                }

                $changeAmount = $payAmount - $totalAmount;

                // 2. Generate unique invoice number: INV-YYYYMMDD-XXXX
                $datePrefix = Carbon::now()->format('Ymd');
                $randomSuffix = strtoupper(Str::random(4));
                $invoiceNumber = "INV-{$datePrefix}-{$randomSuffix}";

                while (Sale::where('invoice_number', $invoiceNumber)->exists()) {
                    $randomSuffix = strtoupper(Str::random(4));
                    $invoiceNumber = "INV-{$datePrefix}-{$randomSuffix}";
                }

                // 3. Create Sale record
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => Auth::id(),
                    'total_amount' => $totalAmount,
                    'pay_amount' => $payAmount,
                    'change_amount' => $changeAmount,
                ]);

                // 4. Create SaleDetails, decrement stock & create StockLogs
                foreach ($detailsToCreate as $itemData) {
                    $product = $itemData['product'];
                    $qty = $itemData['qty'];

                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'buy_price' => $itemData['buy_price'],
                        'sell_price' => $itemData['sell_price'],
                        'subtotal' => $itemData['subtotal'],
                        'profit' => $itemData['profit'],
                    ]);

                    // Automatically decrement stock
                    $product->decrement('stock', $qty);

                    // Create stock log entry
                    StockLog::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'out',
                        'qty' => $qty,
                        'notes' => "Penjualan POS No. {$invoiceNumber}",
                    ]);
                }

                return $sale;
            });

            // Load relations for response/receipt
            $sale->load(['saleDetails.product', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'created_at' => $sale->created_at->format('d/m/Y H:i:s'),
                    'cashier_name' => $sale->user->name,
                    'total_amount' => (float) $sale->total_amount,
                    'pay_amount' => (float) $sale->pay_amount,
                    'change_amount' => (float) $sale->change_amount,
                    'items' => $sale->saleDetails->map(function ($d) {
                        return [
                            'name' => $d->product->product_name,
                            'qty' => $d->qty,
                            'price' => (float) $d->sell_price,
                            'subtotal' => (float) $d->subtotal,
                        ];
                    }),
                ],
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => collect($ve->errors())->flatten()->first(),
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Thermal Receipt Preview / Print.
     */
    public function printReceipt(int $id): View
    {
        $sale = Sale::with(['saleDetails.product', 'user'])->findOrFail($id);

        return view('kasir.receipt', compact('sale'));
    }

    /**
     * Transaction history for the cashier.
     */
    public function history(): View
    {
        $sales = Sale::with(['saleDetails.product', 'user'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('kasir.history', compact('sales'));
    }
}
