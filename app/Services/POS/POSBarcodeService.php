<?php

namespace App\Services\POS;

use App\Models\Product;

class POSBarcodeService
{
    public function lookup(string $barcode): ?array
    {
        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->with(['batches' => function ($query) {
                $query->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->first();

        if (!$product) {
            return null;
        }

        $batch = $product->batches->first();

        return [
            'product_id'   => $product->id,
            'name'         => $product->name,
            'sku'          => $product->sku,
            'barcode'      => $product->barcode,
            'sale_price'   => (float) $product->sale_price,
            'purchase_price' => (float) $product->purchase_price,
            'batch_id'     => $batch?->id,
            'batch_number' => $batch?->batch_number,
            'stock'        => (float) $batch?->quantity ?? 0,
            'unit'         => $product->unit?->name ?? 'pcs',
        ];
    }

    public function search(string $query): array
    {
        return Product::where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->with(['batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->take(20)
            ->get()
            ->map(fn ($product) => [
                'product_id' => $product->id,
                'name'       => $product->name,
                'sku'        => $product->sku,
                'barcode'    => $product->barcode,
                'sale_price' => (float) $product->sale_price,
                'batch_id'   => $product->batches->first()?->id,
                'stock'      => (float) $product->batches->sum('quantity'),
                'unit'       => $product->unit?->name ?? 'pcs',
            ])
            ->toArray();
    }
}
