<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $returns = PurchaseReturn::with(['purchase', 'supplier'])->orderByDesc('created_at');

            return DataTables::of($returns)
                ->addIndexColumn()
                ->addColumn('supplier_name', fn ($row) => $row->supplier
                    ? trim($row->supplier->first_name . ' ' . $row->supplier->last_name)
                    : '-')
                ->addColumn('ref_no', fn ($row) => $row->purchase?->ref_no ?? '-')
                ->editColumn('return_date', fn ($row) => $row->return_date->format('d M Y'))
                ->editColumn('total_amount', fn ($row) => number_format($row->total_amount, 2))
                ->editColumn('refund_amount', fn ($row) => number_format($row->refund_amount, 2))
                ->addColumn('status_badge', fn ($row) => $row->status === 'completed'
                    ? '<span class="badge bg-success">Completed</span>'
                    : '<span class="badge bg-danger">Cancelled</span>')
                ->addColumn('action', fn ($row) => '<a href="' . route('purchase-returns.show', $row->id) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('purchase-returns.index');
    }

    public function create()
    {
        $returnNo = $this->generateReturnNo();
        $bankAccounts = BankAccount::select('id', 'bank_name', 'account_number', 'account_title')->get();

        return view('purchase-returns.create', compact('returnNo', 'bankAccounts'));
    }

    public function lookup(Request $request)
    {
        $request->validate(['ref_no' => 'required|string']);

        $purchase = Purchase::with(['supplier', 'items.product'])
            ->where('ref_no', $request->ref_no)
            ->first();

        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Purchase invoice not found.'], 404);
        }

        if ($purchase->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cancelled purchases cannot be returned.'], 422);
        }

        $items = $purchase->items->map(function (PurchaseItem $item) {
            $remaining = $item->remainingQuantity();

            return [
                'purchase_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? '-',
                'batch_number' => $item->batch_number ?? '-',
                'purchase_quantity' => $item->quantity,
                'returned_quantity' => $item->returnedQuantity(),
                'remaining_quantity' => $remaining,
                'unit_cost' => (float) $item->unit_cost,
                'subtotal' => (float) $item->subtotal,
            ];
        });

        if ($items->every(fn ($item) => $item['remaining_quantity'] <= 0)) {
            return response()->json(['success' => false, 'message' => 'All items on this purchase have already been returned.'], 422);
        }

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'ref_no' => $purchase->ref_no,
                'order_date' => Carbon::parse($purchase->order_date)->format('Y-m-d'),
                'supplier_id' => $purchase->supplier_id,
                'supplier_name' => $purchase->supplier
                    ? trim($purchase->supplier->first_name . ' ' . $purchase->supplier->last_name)
                    : '-',
                'subtotal' => (float) $purchase->subtotal,
                'discount' => (float) $purchase->discount,
                'grand_total' => (float) $purchase->grand_total,
                'paid_amount' => (float) $purchase->paid_amount,
                'due_amount' => (float) $purchase->due_amount,
                'status' => $purchase->status,
            ],
            'items' => $items->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_no' => 'required|string|unique:purchase_returns,return_no',
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'refund_method' => 'required|in:none,cash,bank_transfer',
            'bank_account_id' => 'required_if:refund_method,bank_transfer|nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.return_quantity' => 'required|integer|min:0',
        ]);

        $returnItems = collect($validated['items'])->filter(fn ($item) => (int) $item['return_quantity'] > 0)->values();

        if ($returnItems->isEmpty()) {
            return back()->withInput()->with('error', 'Please enter return quantity for at least one item.');
        }

        $purchase = Purchase::with(['items.product', 'supplier'])->findOrFail($validated['purchase_id']);

        if ($purchase->status === 'cancelled') {
            return back()->withInput()->with('error', 'Cancelled purchases cannot be returned.');
        }

        try {
            DB::beginTransaction();

            $returnItemsData = [];
            $totalReturnAmount = 0;

            foreach ($returnItems as $itemInput) {
                $purchaseItem = $purchase->items->firstWhere('id', $itemInput['purchase_item_id']);

                if (!$purchaseItem) {
                    throw new \RuntimeException('Invalid purchase item selected.');
                }

                $returnQty = (int) $itemInput['return_quantity'];
                $remaining = $purchaseItem->remainingQuantity();

                if ($returnQty > $remaining) {
                    throw new \RuntimeException(
                        "Return quantity ({$returnQty}) exceeds remaining quantity ({$remaining}) for {$purchaseItem->product?->name}."
                    );
                }

                $unitLineTotal = $purchaseItem->quantity > 0 ? $purchaseItem->subtotal / $purchaseItem->quantity : 0;
                $lineTotal = round($unitLineTotal * $returnQty, 2);

                $returnItemsData[] = [
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $purchaseItem->product_id,
                    'batch_number' => $purchaseItem->batch_number,
                    'quantity' => $returnQty,
                    'unit_cost' => $purchaseItem->unit_cost,
                    'line_total' => $lineTotal,
                ];

                $totalReturnAmount += $lineTotal;
            }

            if ($totalReturnAmount <= 0) {
                throw new \RuntimeException('Return total must be greater than zero.');
            }

            $refundFromPaid = min((float) $purchase->paid_amount, max(0, $totalReturnAmount - (float) $purchase->due_amount));
            $refundAmount = 0;

            if ($validated['refund_method'] !== 'none') {
                $refundAmount = $refundFromPaid;
            }

            $purchaseReturn = PurchaseReturn::create([
                'return_no' => $validated['return_no'],
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => $validated['return_date'],
                'total_amount' => $totalReturnAmount,
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'],
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'reason' => $validated['reason'],
                'status' => 'completed',
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($returnItemsData as $itemData) {
                PurchaseReturnItem::create(array_merge(['purchase_return_id' => $purchaseReturn->id], $itemData));

                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->quantity = max(0, ($product->quantity ?? 0) - $itemData['quantity']);
                    $product->save();
                }

                if (!empty($itemData['batch_number'])) {
                    $batch = DB::table('product_batches')
                        ->where('product_id', $itemData['product_id'])
                        ->where('batch_number', $itemData['batch_number'])
                        ->first();

                    if ($batch) {
                        DB::table('product_batches')
                            ->where('id', $batch->id)
                            ->update([
                                'quantity' => max(0, ($batch->quantity ?? 0) - $itemData['quantity']),
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            $purchase->grand_total = max(0, (float) $purchase->grand_total - $totalReturnAmount);
            $purchase->subtotal = max(0, (float) $purchase->subtotal - $totalReturnAmount);
            $purchase->due_amount = max(0, (float) $purchase->due_amount - $totalReturnAmount);
            $purchase->paid_amount = max(0, (float) $purchase->paid_amount - $refundAmount);
            $purchase->save();

            if ($purchase->status === 'received') {
                $supplier = Supplier::findOrFail($purchase->supplier_id);
                $previousBalance = SupplierTransaction::where('supplier_id', $supplier->id)
                    ->orderByDesc('id')
                    ->value('balance') ?? (float) $supplier->balance;

                $newBalance = $previousBalance - $totalReturnAmount;
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $validated['return_date'],
                    'type' => 'purchase_return',
                    'reference' => $purchaseReturn->return_no,
                    'description' => 'Purchase return against ' . $purchase->ref_no . '. Reason: ' . $validated['reason'],
                    'debit' => 0,
                    'credit' => $totalReturnAmount,
                    'balance' => $newBalance,
                ]);

                if ($refundAmount > 0) {
                    $newBalance = $newBalance + $refundAmount;
                    SupplierTransaction::create([
                        'supplier_id' => $supplier->id,
                        'date' => $validated['return_date'],
                        'type' => 'purchase_refund',
                        'reference' => $purchaseReturn->return_no,
                        'description' => 'Refund received for purchase return ' . $purchaseReturn->return_no,
                        'debit' => $refundAmount,
                        'credit' => 0,
                        'balance' => $newBalance,
                    ]);

                    if ($validated['refund_method'] === 'bank_transfer' && !empty($validated['bank_account_id'])) {
                        $bankAccount = BankAccount::find($validated['bank_account_id']);
                        if ($bankAccount) {
                            $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) + $refundAmount;
                            $bankAccount->save();
                        }
                    }
                }

                $supplier->balance = $newBalance;
                $supplier->save();
            }

            DB::commit();

            return redirect()->route('purchase-returns.show', $purchaseReturn->id)
                ->with('success', 'Purchase return processed successfully.');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process purchase return. Please try again.');
        }
    }

    public function show($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase.supplier',
            'items.product',
            'items.purchaseItem',
            'bankAccount',
            'createdBy',
        ])->findOrFail($id);

        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    private function generateReturnNo(): string
    {
        $last = PurchaseReturn::orderByDesc('id')->first();
        $nextNumber = $last ? intval(substr($last->return_no, -6)) + 1 : 1;

        return 'PR-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
