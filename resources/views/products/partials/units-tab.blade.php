<div class="bg-white p-6 rounded-lg shadow-md">
    @can('manager-access')
    <div class="bg-gray-50 p-6 rounded-lg mb-6 border border-gray-200">
        <h4 class="font-bold text-gray-700 mb-4">Add New Unit of Measure</h4>
        <form action="{{ route('units.store') }}" method="POST" class="flex gap-4">
            @csrf
            <input type="text" name="name" placeholder="e.g., Sack, Liter, kg" 
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-2 rounded-lg transition-all shadow-md">
                Save Unit
            </button>
        </form>
    </div>
    @endcan

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-slate-800 text-white text-left">
                <th class="px-6 py-3 text-xs uppercase">ID</th>
                <th class="px-6 py-3 text-xs uppercase">Unit Name</th>
                <th class="px-6 py-3 text-xs uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $unit)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $unit->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $unit->name }}</td>
                    <td class="px-6 py-4">
                        @can('manager-access')
                            <a href="{{ route('units.edit', $unit->id) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                            <form action="{{ route('units.destroy', $unit->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this unit?\n\nNote: This will fail if any products are using it.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 font-bold hover:underline">
                                    Delete
                                </button>
                            </form>
                        @else
                            <span class="text-gray-400 italic">No actions available</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No units found. Add one above!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
