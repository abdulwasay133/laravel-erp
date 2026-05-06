<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
        Route::delete('/delete/{id}', [App\Http\Controllers\PurchaseController::class, 'distroy'])->name('purchase.destroy');
        Route::get('/edit/{id}', [App\Http\Controllers\PurchaseController::class, 'edit'])->name('purchase.edit');
        Route::get('/show/{id}', [App\Http\Controllers\PurchaseController::class, 'show'])->name('purchase.show');

        Route::get('/supplier/{id}/products', [App\Http\Controllers\PurchaseController::class, 'getSupplierProducts'])->name('supplier.products');

        Route::get('bank-accounts', [App\Http\Controllers\PurchaseController::class, 'getBankAccounts'])->name('bank-accounts.index');
    });

        Route::group(['prefix' => 'bank-account'], function () {
            Route::get('/index', [App\Http\Controllers\BankAccountController::class, 'index'])->name('bank.index');
            Route::get('/create', [App\Http\Controllers\BankAccountController::class, 'create'])->name('bank.create');
            Route::post('/store', [App\Http\Controllers\BankAccountController::class, 'store'])->name('bank.store');
            Route::get('/edit/{id}', [App\Http\Controllers\BankAccountController::class, 'edit'])->name('bank.edit');
            Route::put('/update/{id}', [App\Http\Controllers\BankAccountController::class, 'update'])->name('bank.update');
            Route::delete('/delete/{id}', [App\Http\Controllers\BankAccountController::class, 'destroy'])->name('bank.destroy');
        });
});

require __DIR__.'/auth.php';
