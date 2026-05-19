<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model {
    // Note: your table uses created_at but not updated_at
    public $timestamps = false; 
    
    protected $casts = [
    'created_at' => 'datetime',
];
    
    protected $fillable = ['batch_id', 'user_id', 'quantity', 'type', 'remarks'];

    public function batch() { return $this->belongsTo(Batch::class); }
    public function user() { return $this->belongsTo(User::class); }
}
