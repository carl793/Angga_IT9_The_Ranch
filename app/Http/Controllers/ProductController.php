<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'unit'])->get();
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = \App\Models\Supplier::with('batches')->get();
        return view('products.management', compact('products', 'categories', 'units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'required|string|max:100|unique:products,sku',
            'category_id'     => 'required|exists:categories,id',
            'unit_id'         => 'required|exists:units,id',
            'min_stock_level' => 'required|integer|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->uploadImage($request->file('image'));
        }

        unset($data['image']);

        Product::create($data);
        return back()->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $units = Unit::all();
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'sku'             => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id'     => 'required|exists:categories,id',
            'unit_id'         => 'required|exists:units,id',
            'min_stock_level' => 'required|integer|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = $this->uploadImage($request->file('image'));
        }

        unset($data['image']);

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $activeBatches = $product->batches()->where('current_quantity', '>', 0)->count();

        if ($activeBatches > 0) {
            $totalQty = $product->batches()->sum('current_quantity');
            return back()->withErrors([
                'error' => "Cannot archive product '{$product->name}': {$activeBatches} active batch(es) with {$totalQty} units in stock. Please stock out all inventory first."
            ]);
        }

        $this->deleteImage($product->image_path);
        $product->delete();
        return back()->with('success', 'Product archived successfully! Historical data preserved.');
    }

    public function archived()
    {
        $products = Product::onlyTrashed()->with(['category', 'unit'])->get();
        $categories = Category::all();
        $units = Unit::all();
        return view('products.archived', compact('products', 'categories', 'units'));
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return back()->with('success', 'Product restored successfully!');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->deleteImage($product->image_path);
        $product->forceDelete();
        return back()->with('success', 'Product permanently deleted!');
    }

    public function gallery()
    {
        $products = Product::with(['batches', 'category', 'unit'])->get();
        return view('products.gallery', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load(['batches' => function ($query) {
            $query->where('current_quantity', '>', 0)->orderBy('expiry_date', 'asc');
        }, 'category', 'unit']);

        return view('products.show', compact('product'));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function uploadImage($file): string
    {
        $disk = config('filesystems.default');

        if ($disk === 'cloudinary') {
            // Generate a unique public_id path for Cloudinary
            $filename = 'ranch/products/' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Write the file to Cloudinary via the Storage adapter
            Storage::disk('cloudinary')->put($filename, file_get_contents($file->getRealPath()));

            // Get the secure URL back from Cloudinary
            return Storage::disk('cloudinary')->url($filename);
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('products', $filename, 'public');
    }

    private function deleteImage(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        $disk = config('filesystems.default');

        if ($disk === 'cloudinary') {
            try {
                // Extract the path portion after /image/upload/ for the public_id
                // Cloudinary URL format: https://res.cloudinary.com/cloud/image/upload/ranch/products/filename.jpg
                if (preg_match('/\/image\/upload\/(.+)$/', $imagePath, $matches)) {
                    Storage::disk('cloudinary')->delete($matches[1]);
                }
            } catch (\Exception $e) {
                \Log::warning('Cloudinary delete failed: ' . $e->getMessage());
            }
            return;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
