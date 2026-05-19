<div class="bg-white/90 backdrop-blur-md rounded-xl shadow-lg border border-gray-200 overflow-hidden">
    
    <div class="p-6 bg-gray-50/50 border-b border-gray-100">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">SKU *</label>
                    <input type="text" name="sku" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Stock Level *</label>
                    <input type="number" name="min_stock_level" value="10" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category *</label>
                    <select name="category_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="" disabled selected>Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Unit *</label>
                    <select name="unit_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500" required>
                        <option value="" disabled selected>Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="bg-emerald-600 text-white font-bold py-2.5 px-8 rounded-lg hover:bg-emerald-700 transition-all shadow-md">
                        Save Product
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="p-4 bg-emerald-50/30 flex flex-col md:flex-row gap-4 border-b border-gray-100">
        <div class="flex-1">
            <input type="text" id="masterSearch" placeholder="🔍 Search by name or SKU..." 
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
        </div>
        <div class="w-full md:w-64">
            <select id="categoryFilter" class="w-full px-4 py-2 border border-gray-200 rounded-lg outline-none cursor-pointer bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-800 text-white text-xs uppercase">
                <tr>
                    <th class="p-4">SKU</th>
                    <th class="p-4">Product Name</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Unit</th>
                    <th class="p-4 text-center">Min Stock</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @foreach($products as $product)
                <tr class="border-b border-gray-50 hover:bg-emerald-50/50 transition-colors">
                    <td class="p-4 font-mono text-gray-500">{{ $product->sku }}</td>
                    <td class="p-4 font-bold text-gray-800">{{ $product->name }}</td>
                    <td class="p-4"><span class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $product->category->name }}</span></td>
                    <td class="p-4 text-gray-600">{{ $product->unit->name }}</td>
                    <td class="p-4 text-center font-bold text-emerald-700">{{ $product->min_stock_level }}</td>
                    <td class="p-4 text-center flex justify-center gap-3">
                        <a href="{{ route('products.edit', $product) }}" class="text-blue-600 font-bold hover:underline">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 font-bold hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('masterSearch');
        const categorySelect = document.getElementById('categoryFilter');
        const rows = document.querySelectorAll('#productTableBody tr');

        function filterTable() {
            const search = searchInput.value.toLowerCase();
            const category = categorySelect.value.toLowerCase();

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const matchesSearch = text.includes(search);
                const matchesCategory = category === "" || text.includes(category);
                row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        categorySelect.addEventListener('change', filterTable);
    });
</script>
