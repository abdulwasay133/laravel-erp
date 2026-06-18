<?php

use Illuminate\Support\Facades\Route;

// ── POS Web Interface ──────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [App\Http\Controllers\POSController::class, 'index'])->name('index');
    Route::get('/list', [App\Http\Controllers\POSController::class, 'list'])->name('list');
    Route::get('/refund/{transaction}', [App\Http\Controllers\POSController::class, 'refund'])->name('refund');
});

// ── POS API ────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('api/pos')->name('api.pos.')->group(function () {

    // Sessions
    Route::get('session/current', [App\Http\Controllers\Api\POS\POSSessionController::class, 'current'])->name('session.current');
    Route::post('session/open', [App\Http\Controllers\Api\POS\POSSessionController::class, 'open'])->name('session.open');
    Route::post('session/{session}/close', [App\Http\Controllers\Api\POS\POSSessionController::class, 'close'])->name('session.close');
    Route::get('session/{session}', [App\Http\Controllers\Api\POS\POSSessionController::class, 'show'])->name('session.show');

    // Products
    Route::get('products', [App\Http\Controllers\Api\POS\POSProductController::class, 'index'])->name('products.index');
    Route::get('products/search', [App\Http\Controllers\Api\POS\POSProductController::class, 'search'])->name('products.search');
    Route::get('products/{barcode}', [App\Http\Controllers\Api\POS\POSProductController::class, 'show'])->name('products.show');

    // Transactions
    Route::post('transaction/process', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'process'])->name('transaction.process');
    Route::get('transaction/{transaction}', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'show'])->name('transaction.show');
    Route::post('transaction/{transaction}/void', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'void'])->name('transaction.void');
    Route::post('transaction/{transaction}/refund', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'refund'])->name('transaction.refund');
    Route::post('transaction/{transaction}/refund-items', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'refundItems'])->name('transaction.refund-items');
    Route::get('session/{session}/history', [App\Http\Controllers\Api\POS\POSTransactionController::class, 'history'])->name('transaction.history');

    // Holds
    Route::get('session/{session}/holds', [App\Http\Controllers\Api\POS\POSHoldController::class, 'index'])->name('holds.index');
    Route::post('hold/store', [App\Http\Controllers\Api\POS\POSHoldController::class, 'store'])->name('holds.store');
    Route::get('hold/{hold}/resume', [App\Http\Controllers\Api\POS\POSHoldController::class, 'resume'])->name('holds.resume');
    Route::delete('hold/{hold}', [App\Http\Controllers\Api\POS\POSHoldController::class, 'destroy'])->name('holds.destroy');

    // Receipts
    Route::get('receipt/{transaction}', [App\Http\Controllers\Api\POS\POSReceiptController::class, 'show'])->name('receipt.show');
    Route::get('receipt/{transaction}/print', [App\Http\Controllers\Api\POS\POSReceiptController::class, 'print'])->name('receipt.print');
    Route::get('receipt/{transaction}/pdf', [App\Http\Controllers\Api\POS\POSReceiptController::class, 'pdf'])->name('receipt.pdf');

    // Customers
    Route::get('customers', [App\Http\Controllers\Api\POS\POSCustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [App\Http\Controllers\Api\POS\POSCustomerController::class, 'store'])->name('customers.store');

    // Bank Accounts (for POS payment)
    Route::get('bank-accounts', function () {
        return response()->json(['accounts' => \App\Models\BankAccount::all(['id', 'bank_name', 'account_number'])]);
    })->name('bank-accounts');

    // Categories (for POS filter)
    Route::get('categories', function () {
        return response()->json(['categories' => \App\Models\Category::orderBy('name')->get(['id', 'name'])]);
    })->name('categories');
});
