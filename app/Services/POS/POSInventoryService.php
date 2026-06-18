<?php

namespace App\Services\POS;

use App\Models\ProductBatch;
use Illuminate\Support\Collection;

class POSInventoryService
{
    public function validateStock(Collection $items): void
    {
        foreach ($items as $item) {
            if (!isset($item['batch_id'])) {
                $available = (float) ProductBatch::where('product_id', $item['product_id'])->sum('quantity');
                if ($item['quantity'] > $available) {
                    throw new \RuntimeException(
                        "Insufficient stock for product ID {$item['product_id']}. Available: $available"
                    );
                }
                continue;
            }

            $batch = ProductBatch::findOrFail($item['batch_id']);
            if ($item['quantity'] > $batch->quantity) {
                throw new \RuntimeException(
                    "Insufficient stock for batch #{$batch->batch_number}. " .
                    "Available: {$batch->quantity}, requested: {$item['quantity']}"
                );
            }
        }
    }

    public function deductStock(Collection $items): void
    {
        foreach ($items as $item) {
            $qty = (float) $item['quantity'];

            if (!empty($item['batch_id'])) {
                ProductBatch::where('id', $item['batch_id'])
                    ->decrement('quantity', $qty);
                continue;
            }

            // Deduct from batches FIFO (oldest first)
            $batches = ProductBatch::where('product_id', $item['product_id'])
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->get();

            foreach ($batches as $batch) {
                if ($qty <= 0) break;
                $deduct = min($qty, (float) $batch->quantity);
                $batch->decrement('quantity', $deduct);
                $qty -= $deduct;
            }
        }
    }

    public function restoreStock(Collection $items): void
    {
        foreach ($items as $item) {
            $qty = (float) $item['quantity'];

            if (!empty($item['batch_id'])) {
                ProductBatch::where('id', $item['batch_id'])
                    ->increment('quantity', $qty);
            }
        }
    }

    public function getItemCost(array $item): float
    {
        if (!empty($item['batch_id'])) {
            $batch = ProductBatch::find($item['batch_id']);
            return $batch ? (float) $batch->cost * (float) $item['quantity'] : 0;
        }

        return ProductBatch::where('product_id', $item['product_id'])
            ->where('quantity', '>', 0)
            ->orderBy('id')
            ->value('cost') * (float) $item['quantity'] ?? 0;
    }
}
