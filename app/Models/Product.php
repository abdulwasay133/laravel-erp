<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }
    
    public function suppliers()
    {
        return $this->belongsToMany(
            Supplier::class,
            'product_suppliers'
        )->withPivot('cost')
        ->withTimestamps();
    }

}
