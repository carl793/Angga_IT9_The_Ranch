<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;
    
    protected $fillable = ['category_id', 'unit_id', 'sku', 'name', 'min_stock_level', 'image_path'];
    protected $dates = ['deleted_at'];

    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function batches() { return $this->hasMany(Batch::class); }
    public function totalStock() { return $this->batches()->sum('current_quantity'); }

    /**
     * Returns the correct image URL regardless of storage driver.
     * Cloudinary stores full URLs; local disk stores relative paths.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // Cloudinary URLs start with https://
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Local public disk
        return asset('storage/' . $this->image_path);
    }
}

