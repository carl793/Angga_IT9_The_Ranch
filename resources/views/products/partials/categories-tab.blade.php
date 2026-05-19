<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="font-bold mb-4 text-gray-700">Add New Category</h3>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Category Name</label>
                <input type="text" name="name" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline border-gray-300" 
                       placeholder="e.g., Fertilizers" required>
            </div>
            <button type="submit" 
                    class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg transition duration-150">
                Save Category
            </button>
        </form>
    </div>

    <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="px-6 py-3 text-gray-600 font-bold uppercase text-xs">ID</th>
                    <th class="px-6 py-3 text-gray-600 font-bold uppercase text-xs">Name</th>
                    <th class="px-6 py-3 text-gray-600 font-bold uppercase text-xs text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">#{{ $category->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('categories.edit', $category->id) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this category?\n\nNote: This will fail if any products are using it.')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
