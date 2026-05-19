@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Product Profile</h1>
        <a href="{{ route('stock.operations') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-all">
            Back to Stock Operations
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Product Details Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                {{-- Product Image --}}
                <div class="h-64 bg-gray-100 flex items-center justify-center overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-gray-400 text-center p-4">
                            <svg class="w-24 h-24 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">No Image</p>
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">{{ $product->name }}</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">SKU</p>
                            <p class="text-lg font-mono">{{ $product->sku }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Category</p>
                            <p class="text-lg">{{ $product->category->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Unit</p>
                            <p class="text-lg">{{ $product->unit->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Total Stock</p>
                            <p class="text-3xl font-black text-emerald-600">{{ $product->batches->sum('current_quantity') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Min Stock Level</p>
                            <p class="text-lg">{{ $product->min_stock_level }}</p>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="mt-6 space-y-2">
                        <button onclick="showStockInModal()" class="w-full bg-blue-500 text-white font-medium py-3 rounded-lg hover:bg-blue-600 transition-all">
                            Quick Stock In
                        </button>
                        <button onclick="showStockOutModal()" class="w-full bg-rose-500 text-white font-medium py-3 rounded-lg hover:bg-rose-600 transition-all">
                            Quick Stock Out
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Batches --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Active Batches</h3>
                
                @if($product->batches->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Batch Code</th>
                                <th class="px-4 py-2 text-right">Initial Qty</th>
                                <th class="px-4 py-2 text-right">Current Qty</th>
                                <th class="px-4 py-2 text-right">Cost Price</th>
                                <th class="px-4 py-2 text-right">Expiry Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->batches as $batch)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono">{{ $batch->batch_code }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $batch->initial_quantity }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ $batch->current_quantity }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($batch->cost_price, 2) }}</td>
                                <td class="px-4 py-3 text-right {{ $batch->expiry_date && $batch->expiry_date->isPast() ? 'text-red-600' : 'text-gray-600' }}">
                                    {{ $batch->expiry_date ? $batch->expiry_date->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-gray-500 py-8">No active batches for this product</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stock In Modal --}}
<div id="stockInModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Quick Stock In</h3>
        <form action="{{ route('stock.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="in">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Batch Code *</label>
                    <input type="text" name="batch_code" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Quantity *</label>
                    <input type="number" name="quantity" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Cost Price *</label>
                    <input type="number" step="0.01" name="cost_price" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" class="w-full border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeStockInModal()" class="flex-1 bg-gray-400 text-white font-medium py-2 rounded-lg hover:bg-gray-500">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-blue-500 text-white font-medium py-2 rounded-lg hover:bg-blue-600">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stock Out Modal --}}
<div id="stockOutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Quick Stock Out (FEFO)</h3>
        <form action="{{ route('stock.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="out">
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Quantity to Remove *</label>
                    <input type="number" name="quantity" class="w-full border-gray-300 rounded-lg" required>
                    <p class="text-xs text-gray-500 mt-1">Available: {{ $product->batches->sum('current_quantity') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Reason</label>
                    <input type="text" name="reason" placeholder="e.g., Sale, Damaged" class="w-full border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeStockOutModal()" class="flex-1 bg-gray-400 text-white font-medium py-2 rounded-lg hover:bg-gray-500">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-rose-500 text-white font-medium py-2 rounded-lg hover:bg-rose-600">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showStockInModal() {
    document.getElementById('stockInModal').classList.remove('hidden');
}

function closeStockInModal() {
    document.getElementById('stockInModal').classList.add('hidden');
}

function showStockOutModal() {
    document.getElementById('stockOutModal').classList.remove('hidden');
}

function closeStockOutModal() {
    document.getElementById('stockOutModal').classList.add('hidden');
}
</script>
@endsection
