<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin Mobile-First Dashboard (Read-Only).
     */
    public function dashboard(Request $request): View
    {
        // 1. Determine date filter: 'today', 'month', 'custom'
        $filter = $request->input('filter', 'today');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        $now = Carbon::now();

        if ($filter === 'today') {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $filterLabel = 'Hari Ini (' . $now->translatedFormat('d M Y') . ')';
        } elseif ($filter === 'month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $filterLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
        } elseif ($filter === 'custom' && $startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
            $filterLabel = Carbon::parse($startDateInput)->format('d/m/Y') . ' - ' . Carbon::parse($endDateInput)->format('d/m/Y');
        } else {
            // default fallback to today
            $filter = 'today';
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $filterLabel = 'Hari Ini (' . $now->translatedFormat('d M Y') . ')';
        }

        // 2. Aggregate Sales KPI Metrics for the selected date range
        $salesQuery = Sale::whereBetween('created_at', [$startDate, $endDate]);

        $totalSalesCount = (clone $salesQuery)->count();
        $totalOmset = (float) (clone $salesQuery)->sum('total_amount');

        // Total Net Profit calculation from sale_details
        $totalNetProfit = (float) SaleDetail::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('profit');

        // 3. Low stock items (stock <= min_stock)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();

        $lowStockCount = $lowStockProducts->count();

        // 4. Recent sales list for selected period (Read-only)
        $sales = Sale::with(['user', 'saleDetails.product'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 5. Top 5 Best-Selling Products in this period
        $topProducts = SaleDetail::select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_subtotal'), DB::raw('SUM(profit) as total_profit'))
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'filter',
            'startDateInput',
            'endDateInput',
            'filterLabel',
            'totalSalesCount',
            'totalOmset',
            'totalNetProfit',
            'lowStockProducts',
            'lowStockCount',
            'sales',
            'topProducts'
        ));
    }

    /**
     * Dedicated Mobile View for Low Stock Alert.
     */
    public function lowStock(): View
    {
        $products = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();

        return view('admin.low_stock', compact('products'));
    }

    /**
     * Read-only Sale Transaction Detail.
     */
    public function saleDetail(int $id): View
    {
        $sale = Sale::with(['user', 'saleDetails.product'])->findOrFail($id);

        return view('admin.sale_detail', compact('sale'));
    }
}
