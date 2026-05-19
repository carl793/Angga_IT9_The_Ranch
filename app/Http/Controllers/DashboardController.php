<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index() {
    $products = Product::with(['batches', 'unit'])->get();

    // 1. Filter Low Stock
    $lowStock = $products->filter(function($p) {
        return $p->batches->sum('current_quantity') <= $p->min_stock_level;
    });

    // 2. Enhanced Expiry Forecasts (30, 60, 90 days)
    $expiring30 = Batch::with('product')
        ->where('expiry_date', '>', now())
        ->where('expiry_date', '<=', now()->addDays(30))
        ->where('current_quantity', '>', 0)
        ->get();

    $expiring60 = Batch::with('product')
        ->where('expiry_date', '>', now()->addDays(30))
        ->where('expiry_date', '<=', now()->addDays(60))
        ->where('current_quantity', '>', 0)
        ->get();

    $expiring90 = Batch::with('product')
        ->where('expiry_date', '>', now()->addDays(60))
        ->where('expiry_date', '<=', now()->addDays(90))
        ->where('current_quantity', '>', 0)
        ->get();

    // 3. Inventory Valuation (Total monetary value of active stock)
    $inventoryValuation = Batch::where('current_quantity', '>', 0)
        ->get()
        ->sum(function($batch) {
            return $batch->current_quantity * $batch->cost_price;
        });

    // 4. Monthly Stock In/Out Volume (Last 12 months)
    $monthlyData = $this->getMonthlyStockData();

    $stats = [
        'total_items' => $products->count(),
        'alerts' => $lowStock->count(),
        'expiring_30' => $expiring30->count(),
        'expiring_60' => $expiring60->count(),
        'expiring_90' => $expiring90->count(),
        'inventory_value' => $inventoryValuation
    ];

    return view('dashboard', compact('lowStock', 'expiring30', 'expiring60', 'expiring90', 'stats', 'monthlyData'));
}

/**
 * Get monthly stock in/out data for the last 12 months
 */
private function getMonthlyStockData()
{
    $months = [];
    $stockIn = [];
    $stockOut = [];

    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $monthKey = $date->format('Y-m');
        $monthLabel = $date->format('M Y');

        $months[] = $monthLabel;

        // Stock In volume for this month
        $stockIn[] = StockMovement::where('type', 'in')
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('quantity');

        // Stock Out volume for this month
        $stockOut[] = StockMovement::where('type', 'out')
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('quantity');
    }

    return [
        'months' => $months,
        'stock_in' => $stockIn,
        'stock_out' => $stockOut
    ];
}
}
