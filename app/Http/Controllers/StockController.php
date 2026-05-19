<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Batch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    
    public function index()
    {
        $products = Product::with('batches')->get();
        
        $activeBatches = Batch::with('product')
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $movements = StockMovement::with(['batch.product', 'user'])->latest()->take(10)->get();
        
        return view('stock.index', compact('products', 'activeBatches', 'movements'));
    }

    /**
     * Display the new Stock Operations view (replaces old stock management)
     */
    public function operations()
    {
        $products = Product::with(['batches', 'category', 'unit'])->get();
        $suppliers = \App\Models\Supplier::all();
        $movements = StockMovement::with(['batch.product', 'user'])->latest()->take(20)->get();
        
        return view('stock.operations', compact('products', 'suppliers', 'movements'));
    }

 public function store(Request $request)
{
    // 1. Handle STOCK IN
    if ($request->type === 'in') {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric',
            'expiry_date' => 'nullable|date',
        ]);

        // Auto-generate batch code: {SKU}-{YYYYMMDD}-{SEQUENCE}
        $product = \App\Models\Product::findOrFail($request->product_id);
        $date = now()->format('Ymd');
        $sequence = \App\Models\Batch::where('batch_code', 'like', "{$product->sku}-{$date}-%")->count() + 1;
        $batchCode = "{$product->sku}-{$date}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        $batch = Batch::create([
            'product_id' => $request->product_id,
            'supplier_id' => $request->supplier_id,
            'batch_code' => $batchCode,
            'initial_quantity' => $request->quantity,
            'current_quantity' => $request->quantity,
            'cost_price' => $request->cost_price,
            'expiry_date' => $request->expiry_date,
        ]);

        StockMovement::create([
            'batch_id' => $batch->id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'quantity' => $request->quantity,
        ]);

        return redirect()->back()->with('success', "Stock In successful! Batch Code: {$batchCode}");
    } 
    
    // 2. Handle STOCK OUT with FEFO Logic
    elseif ($request->type === 'out') {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return $this->processStockOutFEFO($request);
    }

    return redirect()->back()->with('success', 'Inventory updated successfully!');
}

/**
 * Process Stock Out using FEFO (First-Expired, First-Out) Logic
 */
private function processStockOutFEFO(Request $request)
{
    $productId = $request->product_id;
    $requestedQty = $request->quantity;
    $reason = $request->reason;

    // Get all active batches for this product, ordered by expiry date (FEFO)
    $batches = Batch::where('product_id', $productId)
        ->where('current_quantity', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->orderBy('id', 'asc') // Tie-breaker for same expiry dates
        ->lockForUpdate() // Prevent race conditions
        ->get();

    // Check if we have enough total stock
    $totalAvailable = $batches->sum('current_quantity');
    if ($totalAvailable < $requestedQty) {
        return back()->withErrors([
            'quantity' => "Insufficient stock! Requested: {$requestedQty}, Available: {$totalAvailable}"
        ]);
    }

    // Start database transaction
    \DB::beginTransaction();
    try {
        $remainingQty = $requestedQty;
        $affectedBatches = [];

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $deductQty = min($remainingQty, $batch->current_quantity);

            // Deduct from batch
            $batch->decrement('current_quantity', $deductQty);

            // Log stock movement
            StockMovement::create([
                'batch_id' => $batch->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $deductQty,
                'reason' => $reason,
            ]);

            $affectedBatches[] = [
                'batch_code' => $batch->batch_code,
                'quantity' => $deductQty
            ];

            $remainingQty -= $deductQty;
        }

        \DB::commit();

        // Build success message
        $batchDetails = collect($affectedBatches)->map(function($b) {
            return "{$b['batch_code']} (-{$b['quantity']})";
        })->implode(', ');

        return redirect()->back()->with('success', 
            "Stock out successful! Deducted from batches: {$batchDetails}");

    } catch (\Exception $e) {
        \DB::rollBack();
        return back()->withErrors(['error' => 'Stock out failed: ' . $e->getMessage()]);
    }
}
}