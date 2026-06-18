<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $returns = SaleReturn::with(['sale', 'customer'])->orderByDesc('created_at');

            return DataTables::of($returns)
                ->addIndexColumn()
                ->addColumn('customer_name', fn ($row) => $row->customer
                    ? trim($row->customer->first_name . ' ' . $row->customer->last_name)
                    : '-')
                ->addColumn('invoice_no', fn ($row) => $row->sale?->invoice_no ?? '-')
                ->editColumn('return_date', fn ($row) => $row->return_date->format('d M Y'))
                ->editColumn('total_amount', fn ($row) => number_format($row->total_amount, 2))
                ->editColumn('refund_amount', fn ($row) => number_format($row->refund_amount, 2))
                ->addColumn('status_badge', fn ($row) => $row->status === 'completed'
                    ? '<span class="badge bg-success">Completed</span>'
                    : '<span class="badge bg-danger">Cancelled</span>')
                ->addColumn('action', fn ($row) => '<a href="' . route('sale-returns.show', $row->id) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>')
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('sale-returns.index');
    }

    public function create()
    {
        $returnNo = $this->generateReturnNo();
        $bankAccounts = BankAccount::select('id', 'bank_name', 'account_number', 'account_title')->get();

        return view('sale-returns.create', compact('returnNo', 'bankAccounts'));
    }

    public function lookup(Request $request)
    {
        $request->validate(['invoice_no' => 'required|string']);

        $sale = Sale::with(['customer', 'items.product', 'items.batch'])
            ->where('invoice_no', $request->invoice_no)
            ->first();

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        if ($sale->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Only completed sales can be returned.'], 422);
        }

        $items = $sale->items->map(function (SaleItem $item) {
            $remaining = $item->remainingQuantity();

            return [
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? '-',
                'batch_id' => $item->batch_id,
                'batch_number' => $item->batch?->batch_number ?? '-',
                'sale_quantity' => $item->quantity,
                'returned_quantity' => $item->returnedQuantity(),
                'remaining_quantity' => $remaining,
                'unit_price' => (float) $item->unit_price,
                'discount_percent' => (float) $item->discount_percent,
                'discount_amount' => (float) $item->discount_amount,
                'line_total' => (float) $item->line_total,
            ];
        });

        if ($items->every(fn ($item) => $item['remaining_quantity'] <= 0)) {
            return response()->json(['success' => false, 'message' => 'All items on this invoice have already been returned.'], 422);
        }

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'sale_date' => $sale->sale_date->format('Y-m-d'),
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customer
                    ? trim($sale->customer->first_name . ' ' . $sale->customer->last_name)
                    : '-',
                'subtotal' => (float) $sale->subtotal,
                'discount_amount' => (float) $sale->discount_amount,
                'total_amount' => (float) $sale->total_amount,
                'paid_amount' => (float) $sale->paid_amount,
                'balance' => (float) $sale->balance,
                'status' => $sale->status,
            ],
            'items' => $items->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_no' => 'required|string|unique:sale_returns,return_no',
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'refund_method' => 'required|in:none,cash,bank_transfer',
            'bank_account_id' => 'required_if:refund_method,bank_transfer|nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.return_quantity' => 'required|integer|min:0',
        ]);

        $returnItems = collect($validated['items'])->filter(fn ($item) => (int) $item['return_quantity'] > 0)->values();

        if ($returnItems->isEmpty()) {
            return back()->withInput()->with('error', 'Please enter return quantity for at least one item.');
        }

        $sale = Sale::with(['items.product', 'items.batch', 'customer'])->findOrFail($validated['sale_id']);

        if ($sale->status !== 'completed') {
            return back()->withInput()->with('error', 'Only completed sales can be returned.');
        }

        try {
            DB::beginTransaction();

            $returnItemsData = [];
            $totalReturnAmount = 0;

            foreach ($returnItems as $itemInput) {
                $saleItem = $sale->items->firstWhere('id', $itemInput['sale_item_id']);

                if (!$saleItem) {
                    throw new \RuntimeException('Invalid sale item selected.');
                }

                $returnQty = (int) $itemInput['return_quantity'];
                $remaining = $saleItem->remainingQuantity();

                if ($returnQty > $remaining) {
                    throw new \RuntimeException(
                        "Return quantity ({$returnQty}) exceeds remaining quantity ({$remaining}) for {$saleItem->product?->name}."
                    );
                }

                $unitLineTotal = $saleItem->quantity > 0 ? $saleItem->line_total / $saleItem->quantity : 0;
                $unitDiscount = $saleItem->quantity > 0 ? $saleItem->discount_amount / $saleItem->quantity : 0;
                $lineTotal = round($unitLineTotal * $returnQty, 2);
                $discountAmount = round($unitDiscount * $returnQty, 2);

                $returnItemsData[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'batch_id' => $saleItem->batch_id,
                    'quantity' => $returnQty,
                    'unit_price' => $saleItem->unit_price,
                    'discount_percent' => $saleItem->discount_percent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                ];

                $totalReturnAmount += $lineTotal;
            }

            if ($totalReturnAmount <= 0) {
                throw new \RuntimeException('Return total must be greater than zero.');
            }

            $refundFromPaid = min((float) $sale->paid_amount, max(0, $totalReturnAmount - (float) $sale->balance));
            $refundAmount = 0;

            if ($validated['refund_method'] !== 'none') {
                $refundAmount = $refundFromPaid;
            }

            $saleReturn = SaleReturn::create([
                'return_no' => $validated['return_no'],
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
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
                SaleReturnItem::create(array_merge(['sale_return_id' => $saleReturn->id], $itemData));

                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->quantity = ($product->quantity ?? 0) + $itemData['quantity'];
                    $product->save();
                }

                if (!empty($itemData['batch_id'])) {
                    $batch = ProductBatch::find($itemData['batch_id']);
                    if ($batch) {
                        $batch->quantity = ($batch->quantity ?? 0) + $itemData['quantity'];
                        $batch->save();
                    }
                }
            }

            $sale->total_amount = max(0, (float) $sale->total_amount - $totalReturnAmount);
            $sale->subtotal = max(0, (float) $sale->subtotal - $totalReturnAmount);
            $sale->balance = max(0, (float) $sale->balance - $totalReturnAmount);
            $sale->paid_amount = max(0, (float) $sale->paid_amount - $refundAmount);
            $sale->save();

            $customer = Customer::findOrFail($sale->customer_id);
            $previousBalance = CustomerTransaction::where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->value('balance') ?? (float) $customer->balance;

            $newBalance = $previousBalance - $totalReturnAmount;
            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'date' => $validated['return_date'],
                'type' => 'credit_note',
                'reference' => $saleReturn->return_no,
                'description' => 'Sale return against invoice ' . $sale->invoice_no . '. Reason: ' . $validated['reason'],
                'debit' => 0,
                'credit' => $totalReturnAmount,
                'balance' => $newBalance,
            ]);

            if ($refundAmount > 0) {
                $newBalance = $newBalance + $refundAmount;
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => $validated['return_date'],
                    'type' => 'refund',
                    'reference' => $saleReturn->return_no,
                    'description' => 'Cash/bank refund for sale return ' . $saleReturn->return_no,
                    'debit' => $refundAmount,
                    'credit' => 0,
                    'balance' => $newBalance,
                ]);

                if ($validated['refund_method'] === 'bank_transfer' && !empty($validated['bank_account_id'])) {
                    $bankAccount = BankAccount::find($validated['bank_account_id']);
                    if ($bankAccount) {
                        $bankAccount->current_balance = max(0, ($bankAccount->current_balance ?? 0) - $refundAmount);
                        $bankAccount->save();
                    }
                }
            }

            $customer->balance = $newBalance;
            $customer->save();

            DB::commit();

            return redirect()->route('sale-returns.show', $saleReturn->id)
                ->with('success', 'Sale return processed successfully.');
        } catch (\RuntimeException $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process sale return. Please try again.');
        }
    }

    public function show($id)
    {
        $saleReturn = SaleReturn::with([
            'sale.customer',
            'items.product',
            'items.saleItem',
            'bankAccount',
            'createdBy',
        ])->findOrFail($id);

        return view('sale-returns.show', compact('saleReturn'));
    }

    private function generateReturnNo(): string
    {
        $last = SaleReturn::orderByDesc('id')->first();
        $nextNumber = $last ? intval(substr($last->return_no, -6)) + 1 : 1;

        return 'SR-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
