<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
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
        )->withPivot('cost', 'is_preferred')
        ->withTimestamps();
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
