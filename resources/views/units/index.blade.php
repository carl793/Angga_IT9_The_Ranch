@extends('layouts.farm_app')

@section('content')
<div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0; color: #2c3e50;"> Unit Management</h2>
        
        {{-- Display Success/Error Messages --}}
        @if(session('success'))
            <div style="color: #27ae60; font-weight: bold;">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div style="color: #e74c3c; font-weight: bold;">{{ session('error') }}</div>
        @endif
    </div>

    {{-- 1. ADD UNIT FORM (Managers/Admins Only) --}}
    @can('manager-access')
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #e9ecef;">
        <h4 style="margin-top: 0; margin-bottom: 1rem;">Add New Unit of measure</h4>
        <form action="{{ route('units.store') }}" method="POST" style="display: flex; gap: 10px;">
            @csrf
            <input type="text" name="name" placeholder="e.g., Sack, Liter, kg" 
                   style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
            <button type="submit" 
                    style="background: #2ecc71; color: white; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                Save Unit
            </button>
        </form>
    </div>
    @endcan

    {{-- 2. UNITS TABLE --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background: #2c3e50; color: white; text-align: left;">
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6;">ID</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6;">Unit Name</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $unit)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">{{ $unit->id }}</td>
                    <td style="padding: 12px; font-weight: 500;">{{ $unit->name }}</td>
                    <td style="padding: 12px;">
                        @can('manager-access')
                            <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone if the unit is not in use.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: #e74c3c; background: none; border: none; cursor: pointer; font-weight: bold;">
                                    Delete
                                </button>
                            </form>
                        @else
                            <span style="color: #95a5a6; font-style: italic;">No actions available</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="padding: 20px; text-align: center; color: #7f8c8d;">No units found. Add one above!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection