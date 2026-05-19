@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock Operations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage inventory transactions</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('products.index') }}" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-all font-medium">
                Manage Products
            </a>
        </div>
    </div>

    {{-- Search and Filter Bar --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <input type="text" id="searchProducts" placeholder="Search products by name or SKU..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 outline-none">
            </div>
            <div>
                <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none cursor-pointer">
                    <option value="">All Categories</option>
                    @foreach($products->pluck('category')->unique() as $category)
                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Product Cards Grid --}}
    <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
        @forelse($products as $product)
        <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all" data-category="{{ $product->category->name }}" data-name="{{ strtolower($product->name) }}" data-sku="{{ strtolower($product->sku) }}">
            {{-- Product Image --}}
            <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer" onclick="window.location='{{ route('products.show', $product) }}'">
                @if($product->image_path)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="text-gray-400 text-center p-4">
                        <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-xs">No Image</p>
                    </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-800 mb-1 cursor-pointer hover:text-blue-600" onclick="window.location='{{ route('products.show', $product) }}'">{{ $product->name }}</h3>
                <p class="text-xs text-gray-500 mb-2">SKU: {{ $product->sku }}</p>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $product->category->name }}</span>
                    <span class="text-2xl font-black text-emerald-600">{{ $product->batches->sum('current_quantity') }}</span>
                </div>
                <p class="text-xs text-gray-400 mb-3">{{ $product->unit->name }}</p>

                {{-- Action Buttons --}}
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="openStockInModal({{ $product->id }}, '{{ $product->name }}', '{{ $product->sku }}')" 
                            class="bg-blue-500 text-white text-sm font-medium py-2 rounded-lg hover:bg-blue-600 transition-all">
                        Stock In
                    </button>
                    <button onclick="openStockOutModal({{ $product->id }}, '{{ $product->name }}', {{ $product->batches->sum('current_quantity') }})" 
                            class="bg-rose-500 text-white text-sm font-medium py-2 rounded-lg hover:bg-rose-600 transition-all">
                        Stock Out
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">No products found. Add products first!</p>
        </div>
        @endforelse
    </div>

    {{-- Collapsible Recent Activity Audit Trail --}}
    <div class="bg-white rounded-lg shadow-lg">
        <button onclick="toggleAuditTrail()" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-all">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h2 class="text-xl font-bold text-gray-800">Recent Stock Movements</h2>
                <span class="ml-3 text-sm text-gray-500">({{ $movements->count() }} records)</span>
            </div>
            <svg id="auditTrailIcon" class="w-6 h-6 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div id="auditTrailContent" class="hidden border-t">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2 text-left">Batch Code</th>
                                <th class="px-4 py-2 text-center">Type</th>
                                <th class="px-4 py-2 text-right">Quantity</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Reason</th>
                                <th class="px-4 py-2 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $m)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold">{{ $m->batch->product->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $m->batch->batch_code ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $m->type == 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ strtoupper($m->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold">{{ $m->type == 'in' ? '+' : '-' }}{{ $m->quantity }}</td>
                                <td class="px-4 py-3">{{ $m->user->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-gray-600 italic text-xs">{{ $m->reason ?? '-' }}</td>
                                <td class="px-4 py-3 text-right text-gray-500 text-xs">{{ $m->created_at ? $m->created_at->diffForHumans() : 'Just now' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No recent stock movements</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock In Modal --}}
<div id="stockInModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-2">Stock In</h3>
        <p class="text-sm text-gray-600 mb-4">Product: <span id="stockInProductName" class="font-bold"></span></p>
        <form action="{{ route('stock.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="in">
            <input type="hidden" name="product_id" id="stockInProductId">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full border-gray-300 rounded-lg">
                        <option value="">Select Supplier (Optional)</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Quantity *</label>
                    <input type="number" name="quantity" class="w-full border-gray-300 rounded-lg" required min="1">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Cost Price *</label>
                    <input type="number" step="0.01" name="cost_price" class="w-full border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" class="w-full border-gray-300 rounded-lg">
                </div>
                <p class="text-xs text-gray-500 italic">Batch code will be auto-generated</p>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeStockInModal()" class="flex-1 bg-gray-400 text-white font-medium py-2 rounded-lg hover:bg-gray-500">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-blue-500 text-white font-medium py-2 rounded-lg hover:bg-blue-600">
                    Confirm Stock In
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stock Out Modal --}}
<div id="stockOutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-2">Stock Out (FEFO)</h3>
        <p class="text-sm text-gray-600 mb-4">Product: <span id="stockOutProductName" class="font-bold"></span></p>
        <form action="{{ route('stock.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="out">
            <input type="hidden" name="product_id" id="stockOutProductId">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Quantity to Remove *</label>
                    <input type="number" name="quantity" class="w-full border-gray-300 rounded-lg" required min="1">
                    <p class="text-xs text-gray-500 mt-1">Available: <span id="stockOutAvailable" class="font-bold"></span></p>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Reason</label>
                    <input type="text" name="reason" placeholder="e.g., Sale, Damaged, Expired" class="w-full border-gray-300 rounded-lg">
                </div>
                <p class="text-xs text-gray-500 italic">System will automatically select batches using FEFO (First-Expired, First-Out)</p>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeStockOutModal()" class="flex-1 bg-gray-400 text-white font-medium py-2 rounded-lg hover:bg-gray-500">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-rose-500 text-white font-medium py-2 rounded-lg hover:bg-rose-600">
                    Confirm Stock Out
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Audit Trail Function
    window.toggleAuditTrail = function() {
        const content = document.getElementById('auditTrailContent');
        const icon = document.getElementById('auditTrailIcon');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    };

    // Search and Filter Products
    const searchInput = document.getElementById('searchProducts');
    const categoryFilter = document.getElementById('categoryFilter');
    const productCards = document.querySelectorAll('.product-card');

    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;

        productCards.forEach(card => {
            const name = card.getAttribute('data-name');
            const sku = card.getAttribute('data-sku');
            const category = card.getAttribute('data-category');

            const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
            const matchesCategory = selectedCategory === '' || category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('keyup', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);

    // Modal Functions
    window.openStockInModal = function(productId, productName, sku) {
        document.getElementById('stockInProductId').value = productId;
        document.getElementById('stockInProductName').textContent = productName + ' (' + sku + ')';
        document.getElementById('stockInModal').classList.remove('hidden');
    };

    window.closeStockInModal = function() {
        document.getElementById('stockInModal').classList.add('hidden');
    };

    window.openStockOutModal = function(productId, productName, available) {
        document.getElementById('stockOutProductId').value = productId;
        document.getElementById('stockOutProductName').textContent = productName;
        document.getElementById('stockOutAvailable').textContent = available;
        document.getElementById('stockOutModal').classList.remove('hidden');
    };

    window.closeStockOutModal = function() {
        document.getElementById('stockOutModal').classList.add('hidden');
    };

    // Close modals on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeStockInModal();
            window.closeStockOutModal();
        }
    });
});
</script>
@endsection
