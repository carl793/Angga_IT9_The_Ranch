<?php 
namespace App\Http\Controllers;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index() {
        $units = Unit::all();
        return view('units.index', compact('units'));
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|unique:units|max:20']);
        Unit::create($request->all());
        return back()->with('success', 'Unit created!');
    }

    public function edit(Unit $unit) {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit) {
        $request->validate(['name' => 'required|max:20|unique:units,name,'.$unit->id]);
        
        $oldName = $unit->name;
        $unit->update($request->all());
        
        // Count affected products
        $productCount = $unit->products()->count();
        
        if ($productCount > 0) {
            return redirect()->route('products.index')->with('cascade_update', [
                'type' => 'unit',
                'old_name' => $oldName,
                'new_name' => $unit->name,
                'count' => $productCount
            ]);
        }
        
        return redirect()->route('products.index')->with('success', 'Unit updated!');
    }

    public function destroy(Unit $unit) {
        // Check if unit is in use by any products
        $productCount = $unit->products()->count();
        
        if ($productCount > 0) {
            return back()->withErrors([
                'error' => "Cannot delete unit '{$unit->name}': {$productCount} product(s) are using this unit. Please reassign or archive the products first."
            ]);
        }
        
        $unit->delete(); // Soft delete
        return back()->with('success', 'Unit archived successfully!');
    }

    /**
     * Show archived units
     */
    public function archived() {
        $units = Unit::onlyTrashed()->get();
        return view('units.archived', compact('units'));
    }

    /**
     * Restore archived unit
     */
    public function restore($id) {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        $unit->restore();
        return back()->with('success', 'Unit restored successfully!');
    }

    /**
     * Permanently delete unit
     */
    public function forceDelete($id) {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        
        // Double-check no products are using it
        if ($unit->products()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot permanently delete: Unit still has products.']);
        }
        
        $unit->forceDelete();
        return back()->with('success', 'Unit permanently deleted!');
    }
}
