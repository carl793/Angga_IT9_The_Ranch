<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index() {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|unique:categories|max:50']);
        Category::create($request->all());
        return back()->with('success', 'Category added!');
    }

    // Show the Edit Page
    public function edit(Category $category) {
        return view('categories.edit', compact('category'));
    }

    // Process the Update
    public function update(Request $request, Category $category) {
        $request->validate(['name' => 'required|max:50|unique:categories,name,'.$category->id]);
        
        $oldName = $category->name;
        $category->update($request->all());
        
        // Count affected products
        $productCount = $category->products()->count();
        
        if ($productCount > 0) {
            return redirect()->route('products.index')->with('cascade_update', [
                'type' => 'category',
                'old_name' => $oldName,
                'new_name' => $category->name,
                'count' => $productCount
            ]);
        }
        
        return redirect()->route('products.index')->with('success', 'Category updated!');
    }

    public function destroy(Category $category) {
        // Check if category is in use by any products
        $productCount = $category->products()->count();
        
        if ($productCount > 0) {
            return back()->withErrors([
                'error' => "Cannot delete category '{$category->name}': {$productCount} product(s) are using this category. Please reassign or archive the products first."
            ]);
        }
        
        $category->delete(); // Soft delete
        return back()->with('success', 'Category archived successfully!');
    }

    /**
     * Show archived categories
     */
    public function archived() {
        $categories = Category::onlyTrashed()->get();
        return view('categories.archived', compact('categories'));
    }

    /**
     * Restore archived category
     */
    public function restore($id) {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return back()->with('success', 'Category restored successfully!');
    }

    /**
     * Permanently delete category
     */
    public function forceDelete($id) {
        $category = Category::onlyTrashed()->findOrFail($id);
        
        // Double-check no products are using it
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot permanently delete: Category still has products.']);
        }
        
        $category->forceDelete();
        return back()->with('success', 'Category permanently deleted!');
    }
}