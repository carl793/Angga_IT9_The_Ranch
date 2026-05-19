{{-- Add New Supplier Form --}}
<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <h3 class="font-bold text-lg mb-4 text-gray-700">Add New Supplier</h3>
    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="name" placeholder="Supplier Name *" class="border-gray-300 rounded-lg" required>
            <input type="text" name="contact_person" placeholder="Contact Person" class="border-gray-300 rounded-lg">
            <input type="text" name="phone" placeholder="Phone Number" class="border-gray-300 rounded-lg">
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-bold transition-all">
                    Save Supplier
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Suppliers Table with Analytics --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-800 text-white">
            <tr>
                <th class="p-4 text-left">Supplier Name</th>
                <th class="p-4 text-left">Contact Person</th>
                <th class="p-4 text-left">Phone</th>
                <th class="p-4 text-center">Total Batches</th>
                <th class="p-4 text-right">Inventory Value</th>
                <th class="p-4 text-center">Last Delivery</th>
                <th class="p-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4 font-bold text-gray-800">{{ $supplier->name }}</td>
                <td class="p-4 text-gray-600">{{ $supplier->contact_person ?? 'N/A' }}</td>
                <td class="p-4 text-gray-600">{{ $supplier->phone ?? 'N/A' }}</td>
                <td class="p-4 text-center">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-bold">
                        {{ $supplier->batches->count() }}
                    </span>
                </td>
                <td class="p-4 text-right font-bold text-emerald-600">
                    ${{ number_format($supplier->batches->sum(function($b) { return $b->current_quantity * $b->cost_price; }), 2) }}
                </td>
                <td class="p-4 text-center text-sm text-gray-600">
                    {{ $supplier->batches->sortByDesc('created_at')->first()?->created_at?->format('M d, Y') ?? 'Never' }}
                </td>
                <td class="p-4 text-center">
                    <div class="flex justify-center gap-3">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-blue-600 font-bold hover:underline">Edit</a>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Remove this supplier?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 font-bold hover:underline">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-8 text-center text-gray-500">No suppliers found. Add one above!</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
