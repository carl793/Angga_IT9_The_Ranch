@extends('layouts.farm_app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow max-w-3xl">
    <h2 class="text-xl mb-4 font-bold text-gray-700">Edit Product: {{ $product->name }}</h2>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Product Name *</label>
                <input type="text" name="name" value="{{ $product->name }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">SKU *</label>
                <input type="text" name="sku" value="{{ $product->sku }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category *</label>
                <select name="category_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Unit *</label>
                <select name="unit_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Min Stock Level *</label>
                <input type="number" name="min_stock_level" value="{{ $product->min_stock_level }}" min="0" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <input type="text" name="description" value="{{ $product->description }}" class="w-full border-gray-300 rounded-md shadow-sm">
            </div>

        </div>

        {{-- Product Image Section --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
            
            @if($product->image_path)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-2">Current Image:</p>
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                </div>
            @endif

            <div class="flex items-center gap-4">
                <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" id="imageInput">
            </div>
            <p class="text-xs text-gray-500 mt-1">Accepted formats: JPEG, PNG, WebP (Max 5MB)</p>
            
            {{-- Image Preview --}}
            <div id="imagePreview" class="mt-4 hidden">
                <p class="text-xs text-gray-500 mb-2">New Image Preview:</p>
                <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 font-medium">Update Product</button>
            <a href="{{ route('products.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded-lg hover:bg-gray-500 font-medium">Cancel</a>
        </div>
    </form>
</div>

<script>
// Image preview functionality
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagePreview').classList.add('hidden');
    }
});
</script>
@endsection
