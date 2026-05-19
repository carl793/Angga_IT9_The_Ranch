<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'contact_person', 'phone'];
    protected $dates = ['deleted_at'];

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}