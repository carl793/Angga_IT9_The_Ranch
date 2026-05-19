@extends('layouts.farm_app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow max-w-md">
    <h2 class="text-xl mb-4 font-bold text-gray-700">Edit Supplier info</h2>

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-600">Supplier Name</label>
            <input type="text" name="name" value="{{ $supplier->name }}" class="border p-2 w-full rounded focus:ring-blue-500" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-600">Phone Number</label>
            <input type="text" name="phone" value="{{ $supplier->phone }}" class="border p-2 w-full rounded focus:ring-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
            <a href="{{ route('suppliers.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
        </div>
    </form>
</div>
@endsection