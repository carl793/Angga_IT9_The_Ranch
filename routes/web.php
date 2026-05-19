<?php

use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth', 'verified'])->group(function () {
    
    // 1. General Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Profile Routes (Breeze Defaults)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 3. Product Management (Consolidated View)
    
    // Product Archive Management (MUST be before resource route)
    Route::middleware(['auth', 'can:manager-access'])->group(function () {
        Route::get('/products/archived', [ProductController::class, 'archived'])->name('products.archived');
        Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.forceDelete');
    });
    
    Route::resource('products', ProductController::class);
    Route::get('/products-gallery', [ProductController::class, 'gallery'])->name('products.gallery');
    
    // Redirect old routes to Product Management with hash
    Route::get('/units', function() {
        return redirect()->route('products.index') . '#units';
    })->name('units.index');
    
    Route::get('/categories', function() {
        return redirect()->route('products.index') . '#categories';
    })->name('categories.index');

    Route::get('/suppliers', function() {
        return redirect()->route('products.index') . '#suppliers';
    })->name('suppliers.index');
    
    // Unit Management Actions
    Route::middleware('can:manager-access')->group(function () {
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::get('/units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
        Route::patch('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
        
        // Archive management
        Route::get('/units/archived', [UnitController::class, 'archived'])->name('units.archived');
        Route::post('/units/{id}/restore', [UnitController::class, 'restore'])->name('units.restore');
        Route::delete('/units/{id}/force-delete', [UnitController::class, 'forceDelete'])->name('units.forceDelete');
    });

    // Category Management Actions
    Route::middleware(['auth', 'can:manager-access'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        
        // Archive management
        Route::get('/categories/archived', [CategoryController::class, 'archived'])->name('categories.archived');
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
        Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');
    });

    // Supplier Management Actions
    Route::middleware(['auth', 'can:manager-access'])->group(function () {
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        
        // Archive management
        Route::get('/suppliers/archived', [SupplierController::class, 'archived'])->name('suppliers.archived');
        Route::post('/suppliers/{id}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
        Route::delete('/suppliers/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('suppliers.forceDelete');
    });

    // 4. User Management
    Route::resource('users', UserController::class)->only(['index', 'store', 'destroy']);

    // 5. Stock Management (New Operations View)
    Route::get('/stock-operations', [StockController::class, 'operations'])->name('stock.operations');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    
    // Keep old stock route for backward compatibility (optional)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
});
require __DIR__.'/auth.php';
