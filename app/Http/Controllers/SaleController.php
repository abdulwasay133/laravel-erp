<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Setting;
use App\Services\CommissionService;
use App\Services\HandlesAccounting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    use HandlesAccounting;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sales = Sale::with('customer')->orderByDesc('created_at');

            return DataTables::of($sales)
                ->addIndexColumn()
                ->addColumn('customer_name', function ($row) {
                    return $row->customer->first_name . ' ' . $row->customer->last_name;
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'completed' => '<span class="badge bg-success">Completed</span>',
                        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-light">Unknown</span>';
                })
                ->editColumn('total_amount', function ($row) {
                    return 'Rs. ' . number_format($row->total_amount, 2);
                })
                ->editColumn('paid_amount', function ($row) {
                    return 'Rs. ' . number_format($row->paid_amount, 2);
                })
                ->editColumn('balance', function ($row) {
                    return 'Rs. ' . number_format($row->balance, 2);
                })
                ->addColumn('action', function ($row) {
                    $actions = '<a href="' . route('sale.show', $row->id) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';
                    if ($row->status !== 'cancelled') {
                        $actions .= '<a href="' . route('sale.edit', $row->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                        $actions .= '<button class="btn btn-sm btn-outline-danger delete-sale" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>';
                    }
                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'total' => Sale::count(),
            'completed' => Sale::where('status', 'completed')->count(),
            'todays_sales' => Sale::where('status', 'completed')
                ->whereDate('sale_date', today())
                ->sum('total_amount'),
            'total_revenue' => Sale::where('status', 'completed')->sum('total_amount'),
        ];

        return view('sale.index', compact('stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generate next invoice number
        $lastSale = Sale::latest()->first();
        $nextNumber = ($lastSale ? intval(substr($lastSale->invoice_no, -6)) + 1 : 1);
        $invoiceNo = 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $customers = Customer::orderBy('first_name')->get();
        $products = Product::with('suppliers')->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();
        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();

        return view('sale.create', compact('invoiceNo', 'customers', 'products', 'bankAccounts', 'orderBookers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_no' => 'required|string|unique:sales',
            'customer_id' => 'required|exists:customers,id',
            'order_booker_id' => 'nullable|exists:employees,id',
            'sale_date' => 'required|date',
            'status' => 'in:draft,completed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:product_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'required|in:cash,bank_transfer',
            'bank_account_id' => 'required_if:payment_type,bank_transfer|nullable|exists:bank_accounts,id',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Validate stock availability if sale is being completed
        if (($validated['status'] ?? 'completed') === 'completed') {
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    // Check total product quantity
                    if (($product->quantity ?? 0) < $item['quantity']) {
                        return back()->withInput()->with('error', "Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Required: {$item['quantity']}");
                    }

                    // If specific batch is selected, validate that batch has enough quantity
                    if (!empty($item['batch_id'])) {
                        $batch = ProductBatch::find($item['batch_id']);
                        if ($batch && ($batch->quantity ?? 0) < $item['quantity']) {
                            return back()->withInput()->with('error', "Insufficient stock in batch {$batch->batch_number} for product: {$product->name}. Available: {$batch->quantity}, Required: {$item['quantity']}");
                        }
                    }
                }
            }
        }

        // Create Sale
        $sale = new Sale();
        $sale->invoice_no = $validated['invoice_no'];
        $sale->customer_id = $validated['customer_id'];
        $sale->order_booker_id = $validated['order_booker_id'] ?? null;
        $sale->created_by = auth()->id();
        $sale->sale_date = $validated['sale_date'];
        $sale->status = $validated['status'] ?? 'completed';
        $sale->notes = $validated['notes'] ?? null;

        // Calculate totals from items
        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $discountPercent = $item['discount_percent'] ?? 0;
            $discountAmount = ($itemSubtotal * $discountPercent) / 100;
            $lineTotal = $itemSubtotal - $discountAmount;

            // If no batch_id is provided, auto-allocate batches or use product stock
            if (empty($item['batch_id'])) {
                $saleProduct = Product::find($item['product_id']);
                // Product doesn't track expiry — create item without batch
                if ($saleProduct && !$saleProduct->is_expiry) {
                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'batch_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $itemSubtotal,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'line_total' => $lineTotal,
                    ];
                    $subtotal += $lineTotal;
                } else {
                    $batchAllocations = $this->allocateBatches($item['product_id'], $item['quantity']);

                    // Check if we have enough stock across all batches
                    $totalAllocated = collect($batchAllocations)->sum('quantity');
                    if ($totalAllocated < $item['quantity']) {
                        return back()->withInput()->with('error', "Insufficient stock across all batches for product ID: {$item['product_id']}. Required: {$item['quantity']}, Available: {$totalAllocated}");
                    }

                    // Create sale items for each batch allocation
                    foreach ($batchAllocations as $allocation) {
                        $allocationSubtotal = $allocation['quantity'] * $item['unit_price'];
                        $allocationDiscountAmount = ($allocationSubtotal * $discountPercent) / 100;
                        $allocationLineTotal = $allocationSubtotal - $allocationDiscountAmount;

                        $itemsData[] = [
                            'product_id' => $item['product_id'],
                            'batch_id' => $allocation['batch_id'],
                            'quantity' => $allocation['quantity'],
                            'unit_price' => $item['unit_price'],
                            'subtotal' => $allocationSubtotal,
                            'discount_percent' => $discountPercent,
                            'discount_amount' => $allocationDiscountAmount,
                            'line_total' => $allocationLineTotal,
                        ];

                        $subtotal += $allocationLineTotal;
                    }
                }
            } else {
                // Use the specified batch
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                ];

                $subtotal += $lineTotal;
            }
        }

        $sale->subtotal = $subtotal;
        $sale->total_amount = $subtotal;
        $sale->paid_amount = $validated['paid_amount'];
        $sale->balance = $subtotal - $validated['paid_amount'];

        $sale->save();

        // Create Sale Items
        foreach ($itemsData as $itemData) {
            SaleItem::create(array_merge(['sale_id' => $sale->id], $itemData));
        }

        // Deduct product and batch stock if sale is completed
        if ($sale->status === 'completed') {
            foreach ($itemsData as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->quantity = max(0, ($product->quantity ?? 0) - $itemData['quantity']);
                    $product->save();
                }
                if (!empty($itemData['batch_id'])) {
                    $batch = ProductBatch::find($itemData['batch_id']);
                    if ($batch) {
                        $batch->quantity = max(0, ($batch->quantity ?? 0) - $itemData['quantity']);
                        $batch->save();
                    }
                }
            }
        }

        if ($sale->status === 'completed') {
            $customer = Customer::find($validated['customer_id']);
            $previousBalance = CustomerTransaction::where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->value('balance') ?? 0;

            $debit = $sale->total_amount;
            $credit = $validated['paid_amount'];
            $newBalance = $previousBalance + $debit - $credit;

            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'date' => $sale->sale_date,
                'type' => 'invoice',
                'reference' => $sale->invoice_no,
                'description' => 'Sale created for invoice ' . $sale->invoice_no,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
            ]);

            $customer->balance = $newBalance;
            $customer->save();

            if ($credit > 0) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_type' => $validated['payment_type'],
                    'bank_account_id' => $validated['bank_account_id'] ?? null,
                    'amount' => $credit,
                    'payment_date' => $validated['sale_date'],
                ]);

                if ($validated['payment_type'] === 'bank_transfer' && !empty($validated['bank_account_id'])) {
                    $bankAccount = BankAccount::find($validated['bank_account_id']);
                    if ($bankAccount) {
                        $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) + $credit;
                        $bankAccount->save();
                    }
                }
            }

            $this->postSaleAccounting($sale->id, $sale->total_amount, true, null);

            if ($credit > 0) {
                $paymentMethod = $validated['payment_type'] === 'bank_transfer' ? 'bank' : 'cash';
                $this->postSalePaymentAccounting($sale->id, $credit, $paymentMethod);
            }
        }

        if ($sale->order_booker_id && $sale->status === 'completed') {
            try {
                app(CommissionService::class)->generateCommission($sale);
            } catch (\Exception $e) {
                \Log::warning('Commission generation failed for sale #' . $sale->id . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('sale.show', $sale->id)
            ->with('success', 'Sale created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sale = Sale::with('items.product', 'customer', 'payments')->findOrFail($id);
        $discountTotal = $sale->items->sum('discount_amount');
        return view('sale.show', compact('sale', 'discountTotal'));
    }

    /**
     * Display a print-friendly invoice.
     */
    public function print($id)
    {
        $sale = Sale::with('items.product', 'items.batch', 'customer', 'payments')->findOrFail($id);

        $settings = [
            'company_name'    => Setting::getValue('company_name'),
            'company_address' => Setting::getValue('company_address'),
            'company_phone'   => Setting::getValue('company_phone'),
            'company_email'   => Setting::getValue('company_email'),
            'company_website' => Setting::getValue('company_website'),
            'company_logo'    => Setting::getValue('company_logo'),
            'terms_conditions' => Setting::getValue('terms_conditions', 'Thank you for your business!'),
        ];

        $discountTotal = $sale->items->sum('discount_amount');
        $discountPercent = $sale->subtotal > 0
            ? round(($discountTotal / ($sale->subtotal + $discountTotal)) * 100, 1)
            : 0;

        $qrData = $settings['company_name'] . "\n"
            . 'Invoice: ' . $sale->invoice_no . "\n"
            . 'Date: ' . $sale->sale_date->format('d M, Y') . "\n"
            . 'Total: Rs. ' . number_format($sale->total_amount, 2) . "\n"
            . 'Status: ' . ucfirst($sale->status);

        $qrCode = new QrCode(
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            size: 200
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrSvg = $result->getString();

        $documentTitle = 'Sale Invoice';

        return view('sale.print', compact('sale', 'settings', 'discountTotal', 'discountPercent', 'qrSvg', 'documentTitle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cannot edit cancelled sales.');
        }

        $customers = Customer::orderBy('first_name')->get();
        $products = Product::with('suppliers')->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();
        $orderBookers = Employee::where('is_order_booker', true)->orderBy('first_name')->get();

        return view('sale.edit', compact('sale', 'customers', 'products', 'bankAccounts', 'orderBookers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Cannot update cancelled sales.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_booker_id' => 'nullable|exists:employees,id',
            'sale_date' => 'required|date',
            'status' => 'in:draft,completed',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:product_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'required|in:cash,bank_transfer',
            'bank_account_id' => 'required_if:payment_type,bank_transfer|nullable|exists:bank_accounts,id',
            'paid_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Validate stock availability if sale is being completed
        if ($validated['status'] === 'completed') {
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    // Check total product quantity
                    if (($product->quantity ?? 0) < $item['quantity']) {
                        return back()->withInput()->with('error', "Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Required: {$item['quantity']}");
                    }

                    // If specific batch is selected, validate that batch has enough quantity
                    if (!empty($item['batch_id'])) {
                        $batch = ProductBatch::find($item['batch_id']);
                        if ($batch && ($batch->quantity ?? 0) < $item['quantity']) {
                            return back()->withInput()->with('error', "Insufficient stock in batch {$batch->batch_number} for product: {$product->name}. Available: {$batch->quantity}, Required: {$item['quantity']}");
                        }
                    }
                }
            }
        }

        // Calculate totals
        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $discountPercent = $item['discount_percent'] ?? 0;
            $discountAmount = ($itemSubtotal * $discountPercent) / 100;
            $lineTotal = $itemSubtotal - $discountAmount;

            // If no batch_id is provided, auto-allocate batches or use product stock
            if (empty($item['batch_id'])) {
                $saleProduct = Product::find($item['product_id']);
                // Product doesn't track expiry — create item without batch
                if ($saleProduct && !$saleProduct->is_expiry) {
                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'batch_id' => null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $itemSubtotal,
                        'discount_percent' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'line_total' => $lineTotal,
                    ];
                    $subtotal += $lineTotal;
                } else {
                    $batchAllocations = $this->allocateBatches($item['product_id'], $item['quantity']);

                    // Check if we have enough stock across all batches
                    $totalAllocated = collect($batchAllocations)->sum('quantity');
                    if ($totalAllocated < $item['quantity']) {
                        return back()->withInput()->with('error', "Insufficient stock across all batches for product ID: {$item['product_id']}. Required: {$item['quantity']}, Available: {$totalAllocated}");
                    }

                    // Create sale items for each batch allocation
                    foreach ($batchAllocations as $allocation) {
                        $allocationSubtotal = $allocation['quantity'] * $item['unit_price'];
                        $allocationDiscountAmount = ($allocationSubtotal * $discountPercent) / 100;
                        $allocationLineTotal = $allocationSubtotal - $allocationDiscountAmount;

                        $itemsData[] = [
                            'product_id' => $item['product_id'],
                            'batch_id' => $allocation['batch_id'],
                            'quantity' => $allocation['quantity'],
                            'unit_price' => $item['unit_price'],
                            'subtotal' => $allocationSubtotal,
                            'discount_percent' => $discountPercent,
                            'discount_amount' => $allocationDiscountAmount,
                            'line_total' => $allocationLineTotal,
                        ];

                        $subtotal += $allocationLineTotal;
                    }
                }
            } else {
                // Use the specified batch
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                ];

                $subtotal += $lineTotal;
            }
        }

        $oldCustomerId = $sale->customer_id;
        $oldBalance = $sale->balance;
        $newBalance = $subtotal - $validated['paid_amount'];

        $sale->customer_id = $validated['customer_id'];
        $sale->order_booker_id = $validated['order_booker_id'] ?? null;
        $sale->sale_date = $validated['sale_date'];
        $sale->status = $validated['status'];
        $sale->notes = $validated['notes'] ?? null;
        $sale->subtotal = $subtotal;
        $sale->total_amount = $subtotal;
        $sale->paid_amount = $validated['paid_amount'];
        $sale->balance = $newBalance;
        $sale->save();

        // Revert product and batch stock for old items if sale was completed
        if ($oldStatus === 'completed') {
            $oldItems = SaleItem::where('sale_id', $sale->id)->get();
            foreach ($oldItems as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->quantity = ($product->quantity ?? 0) + $oldItem->quantity;
                    $product->save();
                }
                if ($oldItem->batch_id) {
                    $batch = ProductBatch::find($oldItem->batch_id);
                    if ($batch) {
                        $batch->quantity = ($batch->quantity ?? 0) + $oldItem->quantity;
                        $batch->save();
                    }
                }
            }
        }

        // Delete old items and create new ones
        SaleItem::where('sale_id', $sale->id)->delete();
        foreach ($itemsData as $itemData) {
            SaleItem::create(array_merge(['sale_id' => $sale->id], $itemData));
        }

        // Apply product and batch stock deduction if new status is completed
        if ($sale->status === 'completed') {
            foreach ($itemsData as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $product->quantity = max(0, ($product->quantity ?? 0) - $itemData['quantity']);
                    $product->save();
                }
                if (!empty($itemData['batch_id'])) {
                    $batch = ProductBatch::find($itemData['batch_id']);
                    if ($batch) {
                        $batch->quantity = max(0, ($batch->quantity ?? 0) - $itemData['quantity']);
                        $batch->save();
                    }
                }
            }
        }

        // Adjust bank account balances for previous bank payment
        $previousPayment = SalePayment::where('sale_id', $sale->id)->first();
        if ($previousPayment && $previousPayment->payment_type === 'bank_transfer' && $previousPayment->bank_account_id) {
            $oldBankAccount = BankAccount::find($previousPayment->bank_account_id);
            if ($oldBankAccount) {
                $oldBankAccount->current_balance = ($oldBankAccount->current_balance ?? 0) - $previousPayment->amount;
                $oldBankAccount->save();
            }
        }

        // Remove old payments before creating a new one
        SalePayment::where('sale_id', $sale->id)->delete();

        if ($sale->status === 'completed') {
            if ($validated['paid_amount'] > 0) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_type' => $validated['payment_type'],
                    'bank_account_id' => $validated['bank_account_id'] ?? null,
                    'amount' => $validated['paid_amount'],
                    'payment_date' => $validated['sale_date'],
                ]);

                if ($validated['payment_type'] === 'bank_transfer' && !empty($validated['bank_account_id'])) {
                    $bankAccount = BankAccount::find($validated['bank_account_id']);
                    if ($bankAccount) {
                        $bankAccount->current_balance = ($bankAccount->current_balance ?? 0) + $validated['paid_amount'];
                        $bankAccount->save();
                    }
                }
            }

            $customer = Customer::find($validated['customer_id']);
            $previousBalance = CustomerTransaction::where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->value('balance') ?? $customer->balance;

            $debit = $sale->total_amount;
            $credit = $validated['paid_amount'];
            $newTransactionBalance = $previousBalance + $debit - $credit;

            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'date' => $sale->sale_date,
                'type' => 'invoice',
                'reference' => $sale->invoice_no,
                'description' => 'Sale updated for invoice ' . $sale->invoice_no,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newTransactionBalance,
            ]);

            if ($oldStatus === 'completed' && $oldCustomerId === $validated['customer_id']) {
                $customer->balance += $newBalance - $oldBalance;
            } elseif ($oldStatus !== 'completed' && $validated['status'] === 'completed') {
                $customer->balance += $newBalance;
            } elseif ($oldStatus === 'completed' && $validated['status'] !== 'completed') {
                $customer->balance -= $oldBalance;
            }

            if ($oldCustomerId !== $validated['customer_id']) {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    if ($oldStatus === 'completed') {
                        $oldCustomer->balance -= $oldBalance;
                        $oldCustomer->save();
                    }
                }
            }

            $customer->save();
        }

        if ($sale->order_booker_id && $sale->status === 'completed') {
            try {
                app(CommissionService::class)->generateCommission($sale);
            } catch (\Exception $e) {
                \Log::warning('Commission generation failed on sale update #' . $sale->id . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('sale.show', $sale->id)
            ->with('success', 'Sale updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        // Revert product and batch stock if sale was completed
        if ($sale->status === 'completed') {
            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->quantity = ($product->quantity ?? 0) + $item->quantity;
                    $product->save();
                }
                if ($item->batch_id) {
                    $batch = ProductBatch::find($item->batch_id);
                    if ($batch) {
                        $batch->quantity = ($batch->quantity ?? 0) + $item->quantity;
                        $batch->save();
                    }
                }
            }
        }

        // Revert customer balance
        $customer = Customer::find($sale->customer_id);
        if ($customer) {
            $customer->balance -= $sale->balance;
            $customer->save();
        }

        if ($sale->status === 'completed') {
            $this->reverseAccounting('Sale', $sale->id, 'Sale deleted');
        }

        $sale->delete();

        return back()->with('success', 'Sale deleted successfully!');
    }

    /**
     * Get customer details via AJAX
     */
    /**
     * Get product batches (with stock > 0) for a product ordered by oldest first (FIFO).
     */
    public function getProductBatches($id)
    {
        $batches = ProductBatch::where('product_id', $id)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO: oldest first
            ->get(['id', 'batch_number', 'expiry_date', 'quantity', 'cost']);

        return response()->json($batches);
    }

    /**
     * Get customer details via AJAX
     */
    public function getCustomerDetails($id)
    {
        $customer = Customer::findOrFail($id);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->first_name . ' ' . $customer->last_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'balance' => $customer->balance,
            'company' => $customer->company ?? 'N/A',
        ]);
    }

    /**
     * Get product price via AJAX
     */
    public function getProductPrice($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->sale_price ?? 0,
            'stock' => $product->quantity ?? 0,
        ]);
    }

    /**
     * Look up a product by barcode or SKU (for barcode scanning)
     */
    public function lookupProduct($barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->with(['batches' => function ($q) {
                $q->where('quantity', '>', 0)->orderBy('expiry_date');
            }])
            ->first();

        if (!$product) {
            return response()->json(['found' => false, 'message' => 'No product found with that barcode/SKU.']);
        }

        $firstBatch = $product->batches->first();

        return response()->json([
            'found' => true,
            'product' => [
                'id'         => $product->id,
                'name'       => $product->name,
                'sale_price' => $product->sale_price,
                'sku'        => $product->sku,
                'barcode'    => $product->barcode,
                'batch_id'   => $firstBatch?->id,
                'stock'      => $product->quantity,
            ],
        ]);
    }

    /**
     * Auto-allocate batches for a product in FIFO order
     * Returns an array of batch allocations with quantities
     */
    private function allocateBatches($productId, $requiredQuantity)
    {
        $allocations = [];
        $remainingQuantity = $requiredQuantity;

        // Get batches with available stock ordered by created_at (FIFO)
        $batches = ProductBatch::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableQuantity = $batch->quantity;
            $allocateQuantity = min($availableQuantity, $remainingQuantity);

            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $allocateQuantity,
                'cost' => $batch->cost,
            ];

            $remainingQuantity -= $allocateQuantity;
        }

        return $allocations;
    }
}
