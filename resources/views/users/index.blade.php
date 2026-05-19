@extends('layouts.farm_app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl mb-4 font-bold text-gray-700">User Account Management</h2>

    {{-- Success and Error Messages --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" class="mb-8 p-6 bg-gray-50 border rounded">
        @csrf
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Create New Account</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                <input type="text" name="name" class="w-full border-gray-300 rounded-md shadow-sm" required>
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address *</label>
                <input type="email" name="email" class="w-full border-gray-300 rounded-md shadow-sm" required>
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Role *</label>
                <select name="role" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="" disabled selected>Assign a Role</option>
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
                @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password *</label>
                <input type="password" name="password" class="w-full border-gray-300 rounded-md shadow-sm" required>
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

        </div>
        <button type="submit" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Register User</button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Role</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $user->name }}</td>
                    <td class="p-3 text-gray-600">{{ $user->email }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : ($user->role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="p-3 text-center">
                        @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this user account?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Revoke Access</button>
                            </form>
                        @else
                            <span class="text-gray-400 italic">Current User</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection