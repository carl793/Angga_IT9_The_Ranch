<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model {
   protected $fillable = ['product_id', 'supplier_id', 'batch_code', 'initial_quantity', 'current_quantity', 'cost_price', 'expiry_date'];
   protected $casts = [
    'expiry_date' => 'date',
];

    public function product() { return $this->belongsTo(Product::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }
}
