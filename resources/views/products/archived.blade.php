@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Archived Products</h1>
            <p class="text-sm text-gray-500 mt-1">Manage archived products - restore or permanently delete</p>
        </div>
        <a href="{{ route('products.index') }}" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-all font-medium">
            Back to Products
        </a>
    </div>

    {{-- Success/Error Notifications --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p class="text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Archived Products Table --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Unit</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Archived Date</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($product->image_path)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded object-cover mr-3">
                            @else
                                <div class="w-10 h-10 bg-gray-200 rounded mr-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <span class="font-semibold text-gray-900">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 font-mono text-sm">{{ $product->sku }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $product->category->name }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $product->unit->name }}</td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $product->deleted_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="inline mr-2">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 font-medium" onclick="return confirm('Restore this product?')">
                                Restore
                            </button>
                        </form>
                        <form action="{{ route('products.forceDelete', $product->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('⚠️ PERMANENT DELETE\n\nThis will permanently delete this product and cannot be undone.\n\nAre you absolutely sure?')">
                                Delete Forever
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">No archived products</p>
                        <p class="text-gray-400 text-sm mt-1">Archived products will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
