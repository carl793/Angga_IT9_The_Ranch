@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Stock Management</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6"> <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-indigo-500">
                <h2 class="font-bold text-lg mb-4 text-indigo-700"> Stock In (Receive New Batch)</h2>
                <form action="{{ route('stock.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="in">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold">Select Product</label>
                            <select name="product_id" class="w-full border-gray-300 rounded mt-1" required>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" name="batch_code" placeholder="Batch Number" class="rounded border-gray-300" required>
                        <input type="number" name="quantity" placeholder="Quantity" class="rounded border-gray-300" required>
                        <input type="number" step="0.01" name="cost_price" placeholder="Cost Price" class="rounded border-gray-300" required>
                        <input type="date" name="expiry_date" class="rounded border-gray-300">
                    </div>
                    <button type="submit" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded font-bold hover:bg-indigo-700">Create Batch</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                <h2 class="font-bold text-lg mb-4 text-red-700">🤖 Smart Stock Out (FEFO Auto-Routing)</h2>
                <p class="text-sm text-gray-600 mb-4">System automatically selects batches based on expiry dates (First-Expired, First-Out)</p>
                <form action="{{ route('stock.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="out">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-3">
                            <label class="text-sm font-semibold">Select Product</label>
                            <select name="product_id" class="w-full border-gray-300 rounded mt-1" required>
                                <option value="" disabled selected>Choose a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} ({{ $product->sku }}) - Available: {{ $product->batches->sum('current_quantity') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="number" name="quantity" placeholder="Qty to Remove" class="rounded border-gray-300" required min="1">
                        <div class="md:col-span-2">
                            <input type="text" name="reason" placeholder="Reason (e.g. Sale, Damaged)" class="w-full rounded border-gray-300">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 bg-red-600 text-white px-6 py-2 rounded font-bold hover:bg-red-700">Confirm Stock Out</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1">
    <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-gray-500">
        <h2 class="font-bold text-lg mb-4 text-gray-700 flex items-center">
            <span class="mr-2"></span> Recent Audit Trail
        </h2>
        


        
        <div class="space-y-4">
            @forelse($movements as $m)
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 shadow-sm">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-bold text-gray-900">{{ $m->batch->product->name ?? 'Unknown Product' }}</p>
                        <p class="text-xs text-gray-500">Batch: {{ $m->batch->batch_code ?? 'N/A' }}</p>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-black {{ $m->type == 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ strtoupper($m->type) }} {{ $m->type == 'in' ? '+' : '-' }}{{ $m->quantity }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center text-xs text-gray-500 border-t pt-2 mt-2">
                    <span> {{ $m->user->name ?? 'System' }}</span>
                    <span> {{ $m->created_at ? $m->created_at->diffForHumans() : 'Just now' }}</span>
                </div>

                @if($m->reason)
                <p class="text-[10px] italic text-gray-400 mt-1">Reason: {{ $m->reason }}</p>
                @endif
            </div>
            @empty
            <p class="text-center text-gray-500 py-4 italic text-sm">No recent activities found.</p>
            @endforelse
        </div>
    </div>
</div>

    </div>
</div>
@endsection