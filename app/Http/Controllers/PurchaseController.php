<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    use HandlesAccounting;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $purchases = Purchase::with('supplier')->orderByDesc('created_at');

            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('supplier_name', fn ($row) => $row->supplier?->first_name . ' ' . $row->supplier?->last_name)
                ->editColumn('order_date', fn ($row) => Carbon::parse($row->order_date)->toDateString())
                ->editColumn('grand_total', fn ($row) => 'PKR ' . number_format($row->grand_total, 2))
                ->editColumn('paid_amount', fn ($row) => 'PKR ' . number_format($row->paid_amount, 2))
                ->editColumn('due_amount', fn ($row) => 'PKR ' . number_format($row->due_amount, 2))
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'received' => '<span class="badge bg-success">Completed</span>',
                        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '<a href="' . route('purchase.show', $row->id) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';
                    if ($row->status !== 'cancelled') {
                        $buttons .= '<a href="' . route('purchase.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                        $buttons .= '<button class="btn btn-sm btn-outline-danger delete-purchase" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('purchase.index');
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::all();
        return view('purchase.create', compact('suppliers', 'products'));
    }

    public function getSupplierProducts(int $id)
    {
        $products = Product::whereHas('suppliers', function ($q) use ($id) {
            $q->where('suppliers.id', $id);
        })
        ->with(['suppliers' => function ($q) use ($id) {
            $q->where('suppliers.id', $id);
        }])
        ->select('id', 'name', 'sku', 'purchase_price', 'is_expiry')
        ->get()
        ->map(function ($product) {
            $product->purchase_price = $product->suppliers->first()?->pivot->cost ?? $product->purchase_price;
            unset($product->suppliers);
            return $product;
        });

        return response()->json($products);
    }

    public function getBankAccounts()
    {
        return BankAccount::select('id', 'bank_name', 'account_number', 'account_title')->get();
    }

    /**
     * Look up a product by barcode or SKU (for barcode scanning)
     */
    public function lookupProduct($barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if (!$product) {
            return response()->json(['found' => false, 'message' => 'No product found with that barcode/SKU.']);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id'             => $product->id,
                'name'           => $product->name,
                'purchase_price' => $product->purchase_price,
                'sku'            => $product->sku,
                'barcode'        => $product->barcode,
                'is_expiry'      => $product->is_expiry,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,bank',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'discount_type' => 'required|in:fixed,percent',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total_discount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric',
            'status' => 'required|in:pending,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $refNo = $validated['reference'] ?? 'PO-' . now()->format('YmdHis');
        $paidAmount = $validated['paid_amount'] ?? 0;
        $status = $validated['status'];

        DB::beginTransaction();

        try {
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'ref_no' => $refNo,
                'order_date' => $validated['order_date'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['total_discount'],
                'discount_type' => $validated['discount_type'],
                'tax_amount' => 0,
                'grand_total' => $validated['grand_total'],
                'paid_amount' => $paidAmount,
                'due_amount' => $validated['due_amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
            ]);

            foreach ($validated['items'] as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_cost'];
                $batchNumber = !empty($item['batch_number']) ? $item['batch_number'] : null;
                $expiryDate = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $lineSubtotal,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->quantity = ($product->quantity ?? 0) + $item['quantity'];
                    $product->save();
                }

                if ($batchNumber || $expiryDate) {
                    $batchQuery = DB::table('product_batches')
                        ->where('product_id', $item['product_id']);

                    if ($batchNumber) {
                        $batchQuery->where('batch_number', $batchNumber);
                    } else {
                        $batchQuery->whereNull('batch_number');
                    }

                    $batch = $batchQuery->first();

                    if ($batch) {
                        DB::table('product_batches')
                            ->where('id', $batch->id)
                            ->update([
                                'quantity' => ($batch->quantity ?? 0) + $item['quantity'],
                                'expiry_date' => $expiryDate ?? $batch->expiry_date,
                                'cost' => $item['unit_cost'],
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('product_batches')->insert([
                            'product_id' => $item['product_id'],
                            'batch_number' => $batchNumber,
                            'expiry_date' => $expiryDate,
                            'quantity' => $item['quantity'],
                            'cost' => $item['unit_cost'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($status === 'received') {
                $supplier = Supplier::findOrFail($validated['supplier_id']);
                $previousBalance = SupplierTransaction::where('supplier_id', $supplier->id)
                    ->orderByDesc('id')
                    ->value('balance') ?? $supplier->balance ?? 0;

                $debit = $validated['grand_total'];
                $credit = $paidAmount;
                $newBalance = $previousBalance + $debit - $credit;

                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $validated['order_date'],
                    'type' => 'purchase',
                    'reference' => $purchase->ref_no,
                    'description' => 'Purchase order created: ' . $purchase->ref_no,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $newBalance,
                ]);

                $supplier->balance = $newBalance;
                $supplier->save();
            }

            if ($paidAmount > 0 && $validated['payment_method'] === 'bank' && !empty($validated['bank_account_id'])) {
                $bankAccount = BankAccount::find($validated['bank_account_id']);
                if ($bankAccount) {
                    $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) - $paidAmount;
                    $bankAccount->save();
                }
            }

            if ($status === 'received') {
                $this->postPurchaseAccounting($purchase->id, $purchase->grand_total, true);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase operation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Something went wrong while processing your request. Please check your input and try again.');
        }

        return redirect()->route('purchase.show', $purchase->id)
            ->with('success', 'Purchase created successfully.');
    }

    public function show($id)
    {
        $purchase = Purchase::with('items.product', 'supplier', 'bankAccount')->findOrFail($id);
        return view('purchase.show', compact('purchase'));
    }

    public function print($id)
    {
        $purchase = Purchase::with('items.product', 'supplier', 'bankAccount')->findOrFail($id);

        $settings = [
            'company_name'    => Setting::getValue('company_name'),
            'company_address' => Setting::getValue('company_address'),
            'company_phone'   => Setting::getValue('company_phone'),
            'company_email'   => Setting::getValue('company_email'),
            'company_website' => Setting::getValue('company_website'),
            'company_logo'    => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];

        $qrData = $settings['company_name'] . "\n"
            . 'Ref: ' . $purchase->ref_no . "\n"
            . 'Date: ' . $purchase->order_date->format('d M, Y') . "\n"
            . 'Total: Rs. ' . number_format($purchase->grand_total, 2) . "\n"
            . 'Status: ' . ucfirst($purchase->status);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Purchase Order';

        return view('purchase.print', compact('purchase', 'settings', 'qrSvg', 'documentTitle'));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchase.create', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'reference' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,bank',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'discount_type' => 'required|in:fixed,percent',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total_discount' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric',
            'status' => 'required|in:pending,received,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        $oldItems = $purchase->items()->get();
        $oldPaid = $purchase->paid_amount;
        $oldTotal = $purchase->grand_total;
        $oldSupplierId = $purchase->supplier_id;
        $oldBankAccountId = $purchase->bank_account_id;
        $oldPaymentMethod = $purchase->payment_method;
        $oldStatus = $purchase->status;

        $paidAmount = $validated['paid_amount'] ?? 0;
        $status = $validated['status'];

        DB::beginTransaction();

        try {
            // Revert old product and batch quantities
            foreach ($oldItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity = max(0, ($product->quantity ?? 0) - $item->quantity);
                    $product->save();
                }

                if ($item->batch_number || $item->expiry_date) {
                    $batchQuery = DB::table('product_batches')
                        ->where('product_id', $item->product_id);

                    if ($item->batch_number) {
                        $batchQuery->where('batch_number', $item->batch_number);
                    } else {
                        $batchQuery->whereNull('batch_number');
                    }

                    $batch = $batchQuery->first();
                    if ($batch) {
                        $newQty = max(0, ($batch->quantity ?? 0) - $item->quantity);
                        DB::table('product_batches')->where('id', $batch->id)->update(['quantity' => $newQty, 'updated_at' => now()]);
                    }
                }
            }

            PurchaseItem::where('purchase_id', $purchase->id)->delete();

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'ref_no' => $validated['reference'] ?? $purchase->ref_no,
                'order_date' => $validated['order_date'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['total_discount'],
                'discount_type' => $validated['discount_type'],
                'tax_amount' => 0,
                'grand_total' => $validated['grand_total'],
                'paid_amount' => $paidAmount,
                'due_amount' => $validated['due_amount'],
                'payment_method' => $validated['payment_method'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
            ]);

            foreach ($validated['items'] as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_cost'];
                $batchNumber = !empty($item['batch_number']) ? $item['batch_number'] : null;
                $expiryDate = !empty($item['expiry_date']) ? $item['expiry_date'] : null;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $lineSubtotal,
                    'batch_number' => $batchNumber,
                    'expiry_date' => $expiryDate,
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->quantity = ($product->quantity ?? 0) + $item['quantity'];
                    $product->save();
                }

                if ($batchNumber || $expiryDate) {
                    $batchQuery = DB::table('product_batches')
                        ->where('product_id', $item['product_id']);

                    if ($batchNumber) {
                        $batchQuery->where('batch_number', $batchNumber);
                    } else {
                        $batchQuery->whereNull('batch_number');
                    }

                    $batch = $batchQuery->first();

                    if ($batch) {
                        DB::table('product_batches')
                            ->where('id', $batch->id)
                            ->update([
                                'quantity' => ($batch->quantity ?? 0) + $item['quantity'],
                                'expiry_date' => $expiryDate ?? $batch->expiry_date,
                                'cost' => $item['unit_cost'],
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('product_batches')->insert([
                            'product_id' => $item['product_id'],
                            'batch_number' => $batchNumber,
                            'expiry_date' => $expiryDate,
                            'quantity' => $item['quantity'],
                            'cost' => $item['unit_cost'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($oldPaymentMethod === 'bank' && $oldBankAccountId) {
                $oldBankAccount = BankAccount::find($oldBankAccountId);
                if ($oldBankAccount) {
                    $oldBankAccount->current_balance = ($oldBankAccount->current_balance ?? 0) + $oldPaid;
                    $oldBankAccount->save();
                }
            }

            if ($paidAmount > 0 && $validated['payment_method'] === 'bank' && $validated['bank_account_id']) {
                $bankAccount = BankAccount::find($validated['bank_account_id']);
                if ($bankAccount) {
                    $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) - $paidAmount;
                    $bankAccount->save();
                }
            }

            if ($oldStatus === 'received' && $status !== 'received') {
                // Scenario A: Received -> Non-Received
                $oldSupplier = Supplier::find($oldSupplierId);
                if ($oldSupplier) {
                    $oldSupplierBalance = SupplierTransaction::where('supplier_id', $oldSupplier->id)
                        ->orderByDesc('id')
                        ->value('balance') ?? $oldSupplier->balance ?? 0;

                    $oldSupplierCredit = $oldTotal - $oldPaid;
                    $oldSupplier->balance = $oldSupplierBalance - $oldSupplierCredit;
                    SupplierTransaction::create([
                        'supplier_id' => $oldSupplier->id,
                        'date' => $validated['order_date'],
                        'type' => 'purchase_reverted',
                        'reference' => $purchase->ref_no,
                        'description' => 'Purchase order reverted: ' . $purchase->ref_no,
                        'debit' => 0,
                        'credit' => $oldSupplierCredit,
                        'balance' => $oldSupplier->balance,
                    ]);
                    $oldSupplier->save();
                }
            } elseif ($oldStatus !== 'received' && $status === 'received') {
                // Scenario B: Non-Received -> Received
                $newSupplier = Supplier::findOrFail($validated['supplier_id']);
                $newSupplierBalance = SupplierTransaction::where('supplier_id', $newSupplier->id)
                    ->orderByDesc('id')
                    ->value('balance') ?? $newSupplier->balance ?? 0;

                $debit = $validated['grand_total'];
                $credit = $paidAmount;
                $newBalance = $newSupplierBalance + $debit - $credit;

                SupplierTransaction::create([
                    'supplier_id' => $newSupplier->id,
                    'date' => $validated['order_date'],
                    'type' => 'purchase',
                    'reference' => $purchase->ref_no,
                    'description' => 'Purchase order completed: ' . $purchase->ref_no,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $newBalance,
                ]);

                $newSupplier->balance = $newBalance;
                $newSupplier->save();
            } elseif ($oldStatus === 'received' && $status === 'received') {
                // Scenario C: Received -> Received
                $newSupplier = Supplier::findOrFail($validated['supplier_id']);
                $newSupplierBalance = SupplierTransaction::where('supplier_id', $newSupplier->id)
                    ->orderByDesc('id')
                    ->value('balance') ?? $newSupplier->balance ?? 0;

                if ($oldSupplierId !== $validated['supplier_id']) {
                    $oldSupplier = Supplier::find($oldSupplierId);
                    if ($oldSupplier) {
                        $oldSupplierBalance = SupplierTransaction::where('supplier_id', $oldSupplier->id)
                            ->orderByDesc('id')
                            ->value('balance') ?? $oldSupplier->balance ?? 0;

                        $oldSupplierCredit = $oldTotal - $oldPaid;
                        $oldSupplier->balance = $oldSupplierBalance - $oldSupplierCredit;
                        SupplierTransaction::create([
                            'supplier_id' => $oldSupplier->id,
                            'date' => $validated['order_date'],
                            'type' => 'purchase_transfer',
                            'reference' => $purchase->ref_no,
                            'description' => 'Purchase order moved from this supplier: ' . $purchase->ref_no,
                            'debit' => 0,
                            'credit' => $oldSupplierCredit,
                            'balance' => $oldSupplier->balance,
                        ]);
                        $oldSupplier->save();
                    }
                    $balanceAdjustment = $validated['grand_total'] - $paidAmount;
                } else {
                    $balanceAdjustment = ($validated['grand_total'] - $oldTotal) - ($paidAmount - $oldPaid);
                }

                $newSupplierBalance = $newSupplierBalance + $balanceAdjustment;
                SupplierTransaction::create([
                    'supplier_id' => $newSupplier->id,
                    'date' => $validated['order_date'],
                    'type' => 'purchase_update',
                    'reference' => $purchase->ref_no,
                    'description' => 'Purchase order updated: ' . $purchase->ref_no,
                    'debit' => max(0, $validated['grand_total'] - $oldTotal),
                    'credit' => max(0, $paidAmount - $oldPaid),
                    'balance' => $newSupplierBalance,
                ]);

                $newSupplier->balance = $newSupplierBalance;
                $newSupplier->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase operation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Something went wrong while processing your request. Please check your input and try again.');
        }

        return redirect()->route('purchase.show', $purchase->id)
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity = max(0, ($product->quantity ?? 0) - $item->quantity);
                    $product->save();
                }

                if ($item->batch_number || $item->expiry_date) {
                    $batchQuery = DB::table('product_batches')
                        ->where('product_id', $item->product_id);

                    if ($item->batch_number) {
                        $batchQuery->where('batch_number', $item->batch_number);
                    } else {
                        $batchQuery->whereNull('batch_number');
                    }

                    $batch = $batchQuery->first();
                    if ($batch) {
                        $newQty = max(0, ($batch->quantity ?? 0) - $item->quantity);
                        DB::table('product_batches')->where('id', $batch->id)->update(['quantity' => $newQty, 'updated_at' => now()]);
                    }
                }
            }

            if ($purchase->payment_method === 'bank' && $purchase->bank_account_id) {
                $bankAccount = BankAccount::find($purchase->bank_account_id);
                if ($bankAccount) {
                    $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) + $purchase->paid_amount;
                    $bankAccount->save();
                }
            }

            if ($purchase->status === 'received') {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplierBalance = SupplierTransaction::where('supplier_id', $supplier->id)
                        ->orderByDesc('id')
                        ->value('balance') ?? $supplier->balance ?? 0;

                    $supplierCredit = max(0, $purchase->grand_total - $purchase->paid_amount);
                    $supplier->balance = $supplierBalance - $supplierCredit;
                    SupplierTransaction::create([
                        'supplier_id' => $supplier->id,
                        'date' => now()->toDateString(),
                        'type' => 'purchase_deleted',
                        'reference' => $purchase->ref_no,
                        'description' => 'Purchase order deleted: ' . $purchase->ref_no,
                        'debit' => 0,
                        'credit' => $supplierCredit,
                        'balance' => $supplier->balance,
                    ]);
                    $supplier->save();
                }
            }

            if ($purchase->status === 'received') {
                $this->reverseAccounting('Purchase', $purchase->id, 'Purchase deleted');
            }

            $purchase->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase delete failed: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while deleting the purchase. Please try again.');
        }

        return redirect()->route('purchase.index')->with('success', 'Purchase deleted successfully.');
    }
}
