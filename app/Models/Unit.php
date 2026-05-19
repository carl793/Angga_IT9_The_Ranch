<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model {
    use SoftDeletes;
    
    protected $fillable = ['name'];
    protected $dates = ['deleted_at'];

    public function products() {
        return $this->hasMany(Product::class);
    }
}