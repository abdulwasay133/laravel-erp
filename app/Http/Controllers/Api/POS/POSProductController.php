<?php

namespace App\Http\Controllers\Api\POS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\POS\POSBarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POSProductController extends Controller
{
    public function __construct(protected POSBarcodeService $barcodeService) {}

    public function index(Request $request): JsonResponse
    {
        $products = Product::with(['unit', 'batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->search, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('name')
            ->paginate(50);

        $products->getCollection()->transform(fn ($p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'sku'          => $p->sku,
            'barcode'      => $p->barcode,
            'sale_price'   => (float) $p->sale_price,
            'stock'        => (float) $p->batches->sum('quantity'),
            'unit'         => $p->unit?->name ?? 'pcs',
            'category_id'  => $p->category_id,
            'batches'      => $p->batches->map(fn ($b) => [
                'id'            => $b->id,
                'batch_number'  => $b->batch_number,
                'expiry_date'   => $b->expiry_date?->format('Y-m-d'),
                'quantity'      => (float) $b->quantity,
                'cost'          => (float) $b->cost,
            ]),
        ]);

        return response()->json($products);
    }

    public function show(string $barcode): JsonResponse
    {
        $product = $this->barcodeService->lookup($barcode);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['product' => $product]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $results = $this->barcodeService->search($request->q);

        return response()->json(['products' => $results]);
    }
}
