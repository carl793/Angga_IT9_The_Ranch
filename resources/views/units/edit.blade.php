@extends('layouts.farm_app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow max-w-xl mx-auto">
    <h2 class="text-2xl mb-6 font-bold text-gray-800">Edit Unit</h2>

    <form action="{{ route('units.update', $unit) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Name *</label>
            <input type="text" name="name" value="{{ old('name', $unit->name) }}" 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                   required maxlength="20">
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            
            @if($unit->products()->count() > 0)
                <p class="text-amber-600 text-sm mt-2 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    This unit is used by {{ $unit->products()->count() }} product(s). Updating will affect all of them.
                </p>
            @endif
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 font-medium">
                Update Unit
            </button>
            <a href="{{ route('products.index') }}#units" class="bg-gray-400 text-white px-6 py-2 rounded-lg hover:bg-gray-500 font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
