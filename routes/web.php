<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //---------------------------- My Web routs---------------------
    Route::get('/addunit', [App\Http\Controllers\UnitController::class, 'create'])->name('unit.create');
    Route::post('/addunit', [App\Http\Controllers\UnitController::class, 'store'])->name('unit.store');
    Route::get('/unitlist', [App\Http\Controllers\UnitController::class, 'index'])->name('unit.unitlist');
    Route::put('/updateunit/{id}', [App\Http\Controllers\UnitController::class, 'update'])->name('unit.update');
    Route::delete('/deleteunit/{id}', [App\Http\Controllers\UnitController::class, 'distroy'])->name('unit.destroy');
    Route::get('/editunit/{id}', [App\Http\Controllers\UnitController::class, 'edit'])->name('unit.edit');

    // ********** Category ********
    Route::group(['prefix' => 'category'], function () {
        Route::get('/create', [App\Http\Controllers\CategoryController::class, 'create'])->name('category.create');
        Route::post('/create', [App\Http\Controllers\CategoryController::class, 'store'])->name('category.store');
        Route::get('/index', [App\Http\Controllers\CategoryController::class, 'index'])->name('category.index');
        Route::put('/update/{id}', [App\Http\Controllers\CategoryController::class, 'update'])->name('category.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\CategoryController::class, 'distroy'])->name('category.destroy');
        Route::get('/edit/{id}', [App\Http\Controllers\CategoryController::class, 'edit'])->name('category.edit');
    });

    // ********** Supplier ********
    Route::group(['prefix' => 'supplier'], function () {
       Route::get('/create', [App\Http\Controllers\SupplierController::class, 'create'])->name('supplier.create');
       Route::post('/create', [App\Http\Controllers\SupplierController::class, 'store'])->name('supplier.store');
       Route::get('/index', [App\Http\Controllers\SupplierController::class, 'index'])->name('supplier.index');
       Route::put('/update/{id}', [App\Http\Controllers\SupplierController::class, 'update'])->name('supplier.update');
       Route::delete('/delete/{id}', [App\Http\Controllers\SupplierController::class, 'distroy'])->name('supplier.destroy');
       Route::get('/edit/{id}', [App\Http\Controllers\SupplierController::class, 'edit'])->name('supplier.edit'); 
       Route::get('/view/{id}', [App\Http\Controllers\SupplierController::class, 'view'])->name('supplier.view');
    });

    Route::group(['prefix' => 'product'], function () {
        Route::get('/create', [App\Http\Controllers\ProductController::class, 'create'])->name('product.create');
        Route::post('/create', [App\Http\Controllers\ProductController::class, 'store'])->name('product.store');
        Route::get('/index', [App\Http\Controllers\ProductController::class, 'index'])->name('product.index');
        Route::put('/update/{id}', [App\Http\Controllers\ProductController::class, 'update'])->name('product.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\ProductController::class, 'distroy'])->name('product.destroy');
        Route::get('/edit/{id}', [App\Http\Controllers\ProductController::class, 'edit'])->name('product.edit');
        Route::get('/show/{id}', [App\Http\Controllers\ProductController::class, 'show'])->name('product.show');
    });

    Route::group(['prefix' => 'purchase'], function () {
        Route::get('/create', [App\Http\Controllers\PurchaseController::class, 'create'])->name('purchase.create');
        Route::post('/create', [App\Http\Controllers\PurchaseController::class, 'store'])->name('purchase.store');
        Route::get('/index', [App\Http\Controllers\PurchaseController::class, 'index'])->name('purchase.index');
        Route::put('/update/{id}', [App\Http\Controllers\PurchaseController::class, 'update'])->name('purchase.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\PurchaseController::class, 'destroy'])->name('purchase.destroy');
        Route::get('/edit/{id}', [App\Http\Controllers\PurchaseController::class, 'edit'])->name('purchase.edit');
        Route::get('/show/{id}', [App\Http\Controllers\PurchaseController::class, 'show'])->name('purchase.show');
        Route::get('/print/{id}', [App\Http\Controllers\PurchaseController::class, 'print'])->name('purchase.print');

        Route::get('/supplier/{id}/products', [App\Http\Controllers\PurchaseController::class, 'getSupplierProducts'])->name('supplier.products');
        Route::get('bank-accounts', [App\Http\Controllers\PurchaseController::class, 'getBankAccounts'])->name('bank-accounts.index');
        Route::get('/product/lookup/{barcode}', [App\Http\Controllers\PurchaseController::class, 'lookupProduct'])->name('purchase.product.lookup');
    });

    Route::group(['prefix' => 'supplier'], function () {
        Route::get('/ledger', [App\Http\Controllers\SupplierLedgerController::class, 'index'])->name('supplier.ledger');
        Route::post('/ledger/search', [App\Http\Controllers\SupplierLedgerController::class, 'search'])->name('supplier.ledger.search');
        Route::get('/ledger/print', [App\Http\Controllers\SupplierLedgerController::class, 'print'])->name('supplier.ledger.print');
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::get('/cashbook', [App\Http\Controllers\CashbookController::class, 'index'])->name('cashbook.index');
        Route::post('/cashbook/search', [App\Http\Controllers\CashbookController::class, 'search'])->name('cashbook.search');
        Route::get('/cashbook/print', [App\Http\Controllers\CashbookController::class, 'print'])->name('cashbook.print');
        Route::get('/inventory-ledger', [App\Http\Controllers\InventoryLedgerController::class, 'index'])->name('inventory-ledger.index');
        Route::post('/inventory-ledger/search', [App\Http\Controllers\InventoryLedgerController::class, 'search'])->name('inventory-ledger.search');
        Route::get('/inventory-ledger/print', [App\Http\Controllers\InventoryLedgerController::class, 'print'])->name('inventory-ledger.print');
        Route::get('/bank-book', [App\Http\Controllers\BankBookController::class, 'index'])->name('bank-book.index');
        Route::post('/bank-book/search', [App\Http\Controllers\BankBookController::class, 'search'])->name('bank-book.search');
        Route::get('/bank-book/print', [App\Http\Controllers\BankBookController::class, 'print'])->name('bank-book.print');
        Route::get('/general-ledgers', [App\Http\Controllers\GeneralLedgerController::class, 'index'])->name('general-ledgers.index');
        Route::post('/general-ledgers/search', [App\Http\Controllers\GeneralLedgerController::class, 'search'])->name('general-ledgers.search');
        Route::get('/general-ledgers/print', [App\Http\Controllers\GeneralLedgerController::class, 'print'])->name('general-ledgers.print');
        Route::get('/profit-loss', [App\Http\Controllers\ProfitLossController::class, 'index'])->name('profit-loss.index');
        Route::post('/profit-loss/search', [App\Http\Controllers\ProfitLossController::class, 'search'])->name('profit-loss.search');
        Route::get('/profit-loss/print', [App\Http\Controllers\ProfitLossController::class, 'print'])->name('profit-loss.print');
        Route::get('/balance-sheet', [App\Http\Controllers\BalanceSheetController::class, 'index'])->name('balance-sheet.index');
        Route::post('/balance-sheet/search', [App\Http\Controllers\BalanceSheetController::class, 'search'])->name('balance-sheet.search');
        Route::get('/balance-sheet/print', [App\Http\Controllers\BalanceSheetController::class, 'print'])->name('balance-sheet.print');
        Route::get('/cash-flow', [App\Http\Controllers\CashFlowController::class, 'index'])->name('cash-flow.index');
        Route::post('/cash-flow/search', [App\Http\Controllers\CashFlowController::class, 'search'])->name('cash-flow.search');
        Route::get('/cash-flow/print', [App\Http\Controllers\CashFlowController::class, 'print'])->name('cash-flow.print');
        Route::get('/daily-closing', [App\Http\Controllers\DailyClosingController::class, 'index'])->name('daily-closing.index');
        Route::post('/daily-closing/close', [App\Http\Controllers\DailyClosingController::class, 'closeDay'])->name('daily-closing.close');
        Route::get('/daily-closing/print', [App\Http\Controllers\DailyClosingController::class, 'printClosing'])->name('daily-closing.print');
        Route::get('/closing-report', [App\Http\Controllers\DailyClosingController::class, 'report'])->name('closing-report.index');
        Route::post('/closing-report/search', [App\Http\Controllers\DailyClosingController::class, 'reportSearch'])->name('closing-report.search');
        Route::get('/closing-report/print', [App\Http\Controllers\DailyClosingController::class, 'reportPrint'])->name('closing-report.print');
        Route::get('/today-report', [App\Http\Controllers\TodayReportController::class, 'index'])->name('today-report.index');
        Route::get('/today-report/print', [App\Http\Controllers\TodayReportController::class, 'print'])->name('today-report.print');
        Route::get('/sale-report', [App\Http\Controllers\SaleReportController::class, 'index'])->name('sale-report.index');
        Route::post('/sale-report/search', [App\Http\Controllers\SaleReportController::class, 'search'])->name('sale-report.search');
        Route::get('/sale-report/print', [App\Http\Controllers\SaleReportController::class, 'print'])->name('sale-report.print');
        Route::get('/due-report', [App\Http\Controllers\DueReportController::class, 'index'])->name('due-report.index');
        Route::post('/due-report/search', [App\Http\Controllers\DueReportController::class, 'search'])->name('due-report.search');
        Route::get('/due-report/print', [App\Http\Controllers\DueReportController::class, 'print'])->name('due-report.print');
        Route::get('/purchase-report', [App\Http\Controllers\PurchaseReportController::class, 'index'])->name('purchase-report.index');
        Route::post('/purchase-report/search', [App\Http\Controllers\PurchaseReportController::class, 'search'])->name('purchase-report.search');
        Route::get('/purchase-report/print', [App\Http\Controllers\PurchaseReportController::class, 'print'])->name('purchase-report.print');
    });

    Route::group(['prefix' => 'customers'], function () {
        Route::get('/create', [App\Http\Controllers\CustomerController::class, 'create'])->name('customers.create');
        Route::post('/create', [App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
        Route::get('/index', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/ledger', [App\Http\Controllers\CustomerLedgerController::class, 'index'])->name('customers.ledger');
        Route::post('/ledger/search', [App\Http\Controllers\CustomerLedgerController::class, 'search'])->name('customers.ledger.search');
        Route::get('/ledger/print', [App\Http\Controllers\CustomerLedgerController::class, 'print'])->name('customers.ledger.print');
        Route::get('/credit', [App\Http\Controllers\CustomerLedgerController::class, 'creditCustomers'])->name('customers.credit');
        Route::put('/update/{id}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\CustomerController::class, 'distroy'])->name('customers.destroy');
        Route::get('/edit/{id}', [App\Http\Controllers\CustomerController::class, 'edit'])->name('customers.edit');
        Route::get('/show/{id}', [App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
        Route::get('/view/{id}', [App\Http\Controllers\CustomerController::class, 'view'])->name('customers.view');
    });

        Route::group(['prefix' => 'bank-account'], function () {
            Route::get('/index', [App\Http\Controllers\BankAccountController::class, 'index'])->name('bank.index');
            Route::get('/create', [App\Http\Controllers\BankAccountController::class, 'create'])->name('bank.create');
            Route::post('/store', [App\Http\Controllers\BankAccountController::class, 'store'])->name('bank.store');
            Route::get('/edit/{id}', [App\Http\Controllers\BankAccountController::class, 'edit'])->name('bank.edit');
            Route::put('/update/{id}', [App\Http\Controllers\BankAccountController::class, 'update'])->name('bank.update');
            Route::delete('/delete/{id}', [App\Http\Controllers\BankAccountController::class, 'destroy'])->name('bank.destroy');
        });

    // ********** Sales ********
    Route::group(['prefix' => 'sale'], function () {
        Route::get('/index', [App\Http\Controllers\SaleController::class, 'index'])->name('sale.index');
        Route::get('/create', [App\Http\Controllers\SaleController::class, 'create'])->name('sale.create');
        Route::post('/store', [App\Http\Controllers\SaleController::class, 'store'])->name('sale.store');
        Route::get('/show/{id}', [App\Http\Controllers\SaleController::class, 'show'])->name('sale.show');
        Route::get('/print/{id}', [App\Http\Controllers\SaleController::class, 'print'])->name('sale.print');
        Route::get('/edit/{id}', [App\Http\Controllers\SaleController::class, 'edit'])->name('sale.edit');
        Route::put('/update/{id}', [App\Http\Controllers\SaleController::class, 'update'])->name('sale.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\SaleController::class, 'destroy'])->name('sale.destroy');
        Route::get('/customer/{id}/details', [App\Http\Controllers\SaleController::class, 'getCustomerDetails'])->name('sale.customer.details');
        Route::get('/product/{id}/price', [App\Http\Controllers\SaleController::class, 'getProductPrice'])->name('sale.product.price');
        Route::get('/product/{id}/batches', [App\Http\Controllers\SaleController::class, 'getProductBatches'])->name('sale.product.batches');
        Route::get('/product/lookup/{barcode}', [App\Http\Controllers\SaleController::class, 'lookupProduct'])->name('sale.product.lookup');
    });

    // ********** Stock ********
    Route::group(['prefix' => 'stock'], function () {
        Route::get('/index', [App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
    });

    // ********** Product Waste ********
    Route::group(['prefix' => 'product-waste'], function () {
        Route::get('/index', [App\Http\Controllers\ProductWasteController::class, 'index'])->name('product-waste.index');
        Route::get('/create', [App\Http\Controllers\ProductWasteController::class, 'create'])->name('product-waste.create');
        Route::post('/store', [App\Http\Controllers\ProductWasteController::class, 'store'])->name('product-waste.store');
        Route::get('/show/{id}', [App\Http\Controllers\ProductWasteController::class, 'show'])->name('product-waste.show');
        Route::get('/batches', [App\Http\Controllers\ProductWasteController::class, 'getBatches'])->name('product-waste.batches');
    });

    // ********** Chart of Accounts ********
    Route::group(['prefix' => 'chart-of-accounts'], function () {
        Route::get('/index', [App\Http\Controllers\ChartOfAccountController::class, 'index'])->name('chart-of-accounts.index');
        Route::get('/create', [App\Http\Controllers\ChartOfAccountController::class, 'create'])->name('chart-of-accounts.create');
        Route::post('/store', [App\Http\Controllers\ChartOfAccountController::class, 'store'])->name('chart-of-accounts.store');
        Route::get('/show/{id}', [App\Http\Controllers\ChartOfAccountController::class, 'show'])->name('chart-of-accounts.show');
        Route::get('/edit/{id}', [App\Http\Controllers\ChartOfAccountController::class, 'edit'])->name('chart-of-accounts.edit');
        Route::put('/update/{id}', [App\Http\Controllers\ChartOfAccountController::class, 'update'])->name('chart-of-accounts.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\ChartOfAccountController::class, 'destroy'])->name('chart-of-accounts.destroy');
    });

    // ********** Opening Balances ********
    Route::group(['prefix' => 'opening-balances'], function () {
        Route::get('/index', [App\Http\Controllers\OpeningBalanceController::class, 'index'])->name('opening-balances.index');
        Route::get('/create', [App\Http\Controllers\OpeningBalanceController::class, 'create'])->name('opening-balances.create');
        Route::post('/store', [App\Http\Controllers\OpeningBalanceController::class, 'store'])->name('opening-balances.store');
        Route::get('/edit/{id}', [App\Http\Controllers\OpeningBalanceController::class, 'edit'])->name('opening-balances.edit');
        Route::put('/update/{id}', [App\Http\Controllers\OpeningBalanceController::class, 'update'])->name('opening-balances.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\OpeningBalanceController::class, 'destroy'])->name('opening-balances.destroy');
        Route::get('/generate-voucher-no', [App\Http\Controllers\OpeningBalanceController::class, 'generateVoucherNo'])->name('opening-balances.generate-voucher-no');
    });

    // ********** Cash Adjustments ********
    Route::group(['prefix' => 'cash-adjustments'], function () {
        Route::get('/index', [App\Http\Controllers\CashAdjustmentController::class, 'index'])->name('cash-adjustments.index');
        Route::get('/create', [App\Http\Controllers\CashAdjustmentController::class, 'create'])->name('cash-adjustments.create');
        Route::post('/store', [App\Http\Controllers\CashAdjustmentController::class, 'store'])->name('cash-adjustments.store');
        Route::get('/edit/{id}', [App\Http\Controllers\CashAdjustmentController::class, 'edit'])->name('cash-adjustments.edit');
        Route::put('/update/{id}', [App\Http\Controllers\CashAdjustmentController::class, 'update'])->name('cash-adjustments.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\CashAdjustmentController::class, 'destroy'])->name('cash-adjustments.destroy');
        Route::get('/generate-voucher-no', [App\Http\Controllers\CashAdjustmentController::class, 'generateVoucherNo'])->name('cash-adjustments.generate-voucher-no');
    });

    // ********** Customer Payments ********
    Route::group(['prefix' => 'customer-payments'], function () {
        Route::get('/index', [App\Http\Controllers\CustomerPaymentController::class, 'index'])->name('customer-payments.index');
        Route::get('/create', [App\Http\Controllers\CustomerPaymentController::class, 'create'])->name('customer-payments.create');
        Route::post('/store', [App\Http\Controllers\CustomerPaymentController::class, 'store'])->name('customer-payments.store');
        Route::get('/edit/{id}', [App\Http\Controllers\CustomerPaymentController::class, 'edit'])->name('customer-payments.edit');
        Route::put('/update/{id}', [App\Http\Controllers\CustomerPaymentController::class, 'update'])->name('customer-payments.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\CustomerPaymentController::class, 'destroy'])->name('customer-payments.destroy');
        Route::get('/generate-voucher-no', [App\Http\Controllers\CustomerPaymentController::class, 'generateVoucherNo'])->name('customer-payments.generate-voucher-no');
    });

    // ********** Supplier Payments ********
    Route::group(['prefix' => 'supplier-payments'], function () {
        Route::get('/index', [App\Http\Controllers\SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
        Route::get('/create', [App\Http\Controllers\SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
        Route::post('/store', [App\Http\Controllers\SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
        Route::get('/edit/{id}', [App\Http\Controllers\SupplierPaymentController::class, 'edit'])->name('supplier-payments.edit');
        Route::put('/update/{id}', [App\Http\Controllers\SupplierPaymentController::class, 'update'])->name('supplier-payments.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\SupplierPaymentController::class, 'destroy'])->name('supplier-payments.destroy');
        Route::get('/generate-voucher-no', [App\Http\Controllers\SupplierPaymentController::class, 'generateVoucherNo'])->name('supplier-payments.generate-voucher-no');
    });

    // ********** Expenses ********
    Route::group(['prefix' => 'expenses'], function () {
        Route::get('/index', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/create', [App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/store', [App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/edit/{id}', [App\Http\Controllers\ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/update/{id}', [App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::get('/generate-voucher-no', [App\Http\Controllers\ExpenseController::class, 'generateVoucherNo'])->name('expenses.generate-voucher-no');
    });

    // ********** Sale Returns ********
    Route::group(['prefix' => 'sale-returns'], function () {
        Route::get('/index', [App\Http\Controllers\SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::get('/create', [App\Http\Controllers\SaleReturnController::class, 'create'])->name('sale-returns.create');
        Route::get('/lookup', [App\Http\Controllers\SaleReturnController::class, 'lookup'])->name('sale-returns.lookup');
        Route::post('/store', [App\Http\Controllers\SaleReturnController::class, 'store'])->name('sale-returns.store');
        Route::get('/show/{id}', [App\Http\Controllers\SaleReturnController::class, 'show'])->name('sale-returns.show');
    });

    // ********** Employees ********
    Route::group(['prefix' => 'employees'], function () {
        Route::get('/index', [App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/create', [App\Http\Controllers\EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/create', [App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/edit/{employee}', [App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/update/{employee}', [App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/delete/{employee}', [App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // ********** Salary Management ********
    Route::group(['prefix' => 'salary'], function () {
        Route::get('/index', [App\Http\Controllers\SalaryController::class, 'index'])->name('salary.index');
        Route::get('/pending', [App\Http\Controllers\SalaryController::class, 'pending'])->name('salary.pending');
        Route::get('/create', [App\Http\Controllers\SalaryController::class, 'create'])->name('salary.create');
        Route::post('/store', [App\Http\Controllers\SalaryController::class, 'store'])->name('salary.store');
    });

    // ********** Purchase Returns ********
    Route::group(['prefix' => 'purchase-returns'], function () {
        Route::get('/index', [App\Http\Controllers\PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('/create', [App\Http\Controllers\PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::get('/lookup', [App\Http\Controllers\PurchaseReturnController::class, 'lookup'])->name('purchase-returns.lookup');
        Route::post('/store', [App\Http\Controllers\PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
        Route::get('/show/{id}', [App\Http\Controllers\PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
    });

    // ********** Near to Expiry ********
    Route::group(['prefix' => 'near-to-expiry'], function () {
        Route::get('/index', [App\Http\Controllers\NearToExpiryController::class, 'index'])->name('near-to-expiry.index');
        Route::get('/print', [App\Http\Controllers\NearToExpiryController::class, 'print'])->name('near-to-expiry.print');
        Route::post('/waste', [App\Http\Controllers\NearToExpiryController::class, 'waste'])->name('near-to-expiry.waste');
        Route::post('/return', [App\Http\Controllers\NearToExpiryController::class, 'returnBatch'])->name('near-to-expiry.return');
    });

    // ********** Database Backup ********
    Route::group(['prefix' => 'backup'], function () {
        Route::get('/index', [App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
        Route::post('/create', [App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
        Route::get('/download/{filename}', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
        Route::delete('/delete/{filename}', [App\Http\Controllers\BackupController::class, 'destroy'])->name('backup.destroy');
        Route::post('/import', [App\Http\Controllers\BackupController::class, 'import'])->name('backup.import');
        Route::post('/update-path', [App\Http\Controllers\BackupController::class, 'updatePath'])->name('backup.update-path');
    });

    // ********** Settings ********
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/index', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/update', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });

    // ********** User Management ********
    Route::group(['prefix' => 'users'], function () {
        Route::get('/index', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('/store', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('/edit/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });

    // ********** Order Booker Commissions ********
    Route::group(['prefix' => 'commissions'], function () {
        Route::get('/index', [App\Http\Controllers\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/approve/{id}', [App\Http\Controllers\CommissionController::class, 'approve'])->name('commissions.approve');
        Route::delete('/cancel/{id}', [App\Http\Controllers\CommissionController::class, 'cancel'])->name('commissions.cancel');
    });

    // ********** Commission Payments ********
    Route::group(['prefix' => 'commission-payments'], function () {
        Route::get('/index', [App\Http\Controllers\CommissionPaymentController::class, 'index'])->name('commission-payments.index');
        Route::get('/create', [App\Http\Controllers\CommissionPaymentController::class, 'create'])->name('commission-payments.create');
        Route::post('/store', [App\Http\Controllers\CommissionPaymentController::class, 'store'])->name('commission-payments.store');
        Route::get('/show/{id}', [App\Http\Controllers\CommissionPaymentController::class, 'show'])->name('commission-payments.show');
        Route::get('/print/{id}', [App\Http\Controllers\CommissionPaymentController::class, 'print'])->name('commission-payments.print');
        Route::get('/commissions/{orderBookerId}', [App\Http\Controllers\CommissionPaymentController::class, 'getCommissions'])->name('commission-payments.commissions');
    });

    // ********** Commission Reports ********
    Route::group(['prefix' => 'commission-reports'], function () {
        Route::get('/performance', [App\Http\Controllers\CommissionReportController::class, 'orderBookerPerformance'])->name('commissions.reports.performance');
        Route::get('/performance/print', [App\Http\Controllers\CommissionReportController::class, 'printPerformance'])->name('commissions.reports.performance.print');
        Route::get('/due', [App\Http\Controllers\CommissionReportController::class, 'dueReport'])->name('commissions.reports.due');
        Route::get('/due/print', [App\Http\Controllers\CommissionReportController::class, 'printDue'])->name('commissions.reports.due.print');
        Route::get('/monthly', [App\Http\Controllers\CommissionReportController::class, 'monthlyReport'])->name('commissions.reports.monthly');
        Route::get('/monthly/print', [App\Http\Controllers\CommissionReportController::class, 'printMonthly'])->name('commissions.reports.monthly.print');
    });
});

require __DIR__.'/auth.php';
