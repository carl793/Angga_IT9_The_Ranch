<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index() {
        $suppliers = Supplier::with('batches')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        Supplier::create($data);
        return back()->with('success', 'Supplier added successfully!');
    }

    public function edit(Supplier $supplier) {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated!');
    }

    public function destroy(Supplier $supplier) {
        // Check if supplier has batches
        $batchCount = $supplier->batches()->count();
        
        if ($batchCount > 0) {
            return back()->withErrors([
                'error' => "Cannot delete supplier '{$supplier->name}': {$batchCount} batch(es) are associated with this supplier. Deletion would destroy audit trail. Use 'Archive' instead."
            ]);
        }
        
        $supplier->delete(); // Soft delete
        return back()->with('success', 'Supplier archived successfully!');
    }

    /**
     * Show archived suppliers
     */
    public function archived() {
        $suppliers = Supplier::onlyTrashed()->withCount('batches')->get();
        return view('suppliers.archived', compact('suppliers'));
    }

    /**
     * Restore archived supplier
     */
    public function restore($id) {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();
        return back()->with('success', 'Supplier restored successfully!');
    }

    /**
     * Permanently delete supplier
     */
    public function forceDelete($id) {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        
        // Double-check no batches exist
        if ($supplier->batches()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot permanently delete: Supplier still has batches.']);
        }
        
        $supplier->forceDelete();
        return back()->with('success', 'Supplier permanently deleted!');
    }
}