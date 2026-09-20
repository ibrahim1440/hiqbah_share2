<?php

use App\Modules\Procurement\Controllers\PurchaseOrderController;
use App\Modules\Procurement\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('api/v1')->group(function () {
    // Suppliers
    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('permission:suppliers.view');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.update');
    Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete');

    // Purchase Orders
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('permission:purchase_orders.view');
    Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->middleware('permission:purchase_orders.view');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('permission:purchase_orders.create');
    Route::put('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update'])->middleware('permission:purchase_orders.update');
    Route::patch('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update'])->middleware('permission:purchase_orders.update');
    Route::delete('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->middleware('permission:purchase_orders.delete');
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->middleware('permission:purchase_orders.approve');
    Route::put('purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->middleware('permission:purchase_orders.update');
});
