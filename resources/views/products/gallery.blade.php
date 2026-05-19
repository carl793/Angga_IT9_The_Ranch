@extends('layouts.farm_app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Product Gallery</h1>
        <a href="{{ route('products.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-all">
            Back to List View
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($products as $product)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all cursor-pointer" onclick="window.location='{{ route('products.show', $product) }}'">
            {{-- Product Image --}}
            <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
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
                <h3 class="font-bold text-lg text-gray-800 mb-1">{{ $product->name }}</h3>
                <p class="text-xs text-gray-500 mb-2">SKU: {{ $product->sku }}</p>
                <div class="flex justify-between items-center">
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $product->category->name }}</span>
                    <span class="text-lg font-black text-emerald-600">{{ $product->batches->sum('current_quantity') }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $product->unit->name }}</p>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">No products found. Add products first!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
