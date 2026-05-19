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

        // Remove 'image' key from validated data before creating (not a DB column)
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
            // Delete old image from storage
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

        $product->delete(); // Soft delete
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
    // Private helpers: abstract storage so local and cloud work identically
    // -------------------------------------------------------------------------

    /**
     * Upload an image and return the stored path / URL.
     * Uses Cloudinary in production (FILESYSTEM_DISK=cloudinary),
     * falls back to local public disk in development.
     */
    private function uploadImage($file): string
    {
        $disk = config('filesystems.default');

        if ($disk === 'cloudinary') {
            // Upload to Cloudinary and return the secure URL
            $result = cloudinary()->upload($file->getRealPath(), [
                'folder' => 'ranch/products',
            ]);
            return $result->getSecurePath();
        }

        // Local development: store in public disk
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('products', $filename, 'public');
    }

    /**
     * Delete an image from whichever disk is active.
     */
    private function deleteImage(?string $imagePath): void
    {
        if (!$imagePath) {
            return;
        }

        $disk = config('filesystems.default');

        if ($disk === 'cloudinary') {
            // Cloudinary stores the full URL; extract the public_id to delete
            // public_id format: ranch/products/<filename_without_extension>
            try {
                $segments   = explode('/', parse_url($imagePath, PHP_URL_PATH));
                $filename   = pathinfo(end($segments), PATHINFO_FILENAME);
                $publicId   = 'ranch/products/' . $filename;
                cloudinary()->destroy($publicId);
            } catch (\Exception $e) {
                // Log but don't crash if deletion fails
                \Log::warning('Cloudinary delete failed: ' . $e->getMessage());
            }
            return;
        }

        // Local disk
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
