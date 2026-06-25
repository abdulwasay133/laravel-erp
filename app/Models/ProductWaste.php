<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWaste extends Model
{
    protected $guarded = [];

    protected $casts = [
        'waste_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
