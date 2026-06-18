<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expiry_date' => 'date',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
