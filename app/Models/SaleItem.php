<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function returnItems()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function returnedQuantity(): int
    {
        return (int) $this->returnItems()
            ->whereHas('saleReturn', fn ($query) => $query->where('status', 'completed'))
            ->sum('quantity');
    }

    public function remainingQuantity(): int
    {
        return max(0, $this->quantity - $this->returnedQuantity());
    }
}
