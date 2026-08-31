<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\WorkOrderController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\RabController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\FinancialCategoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorInvoiceController;

// Auth routes (no Breeze, manual)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Admin routes
Route::middleware(['auth', 'role:super_admin,admin,kepala_teknisi'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('customers', CustomerController::class);
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::resource('work-order-types', \App\Http\Controllers\Admin\WorkOrderTypeController::class);
    Route::resource('work-orders', WorkOrderController::class);
    Route::resource('vendors', VendorController::class)->except(['show']);
    Route::get('vendor-invoices/create', [VendorInvoiceController::class, 'create'])->name('vendor-invoices.create');
    Route::post('vendor-invoices/download', [VendorInvoiceController::class, 'download'])->name('vendor-invoices.download');
    Route::post('work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::get('work-orders/{workOrder}/continue', [WorkOrderController::class, 'continue'])->name('work-orders.continue');
    Route::post('work-orders/{workOrder}/continue', [WorkOrderController::class, 'storeContinue'])->name('work-orders.store-continue');
    Route::put('work-orders/{workOrder}/reports/{report}', [WorkOrderController::class, 'updateReport'])->name('work-orders.update-report');
    Route::get('work-orders/{workOrder}/report/pdf', [WorkOrderController::class, 'downloadReportPdf'])->name('work-orders.report-pdf');
    Route::get('work-orders/{workOrder}/invoice-report/pdf', [WorkOrderController::class, 'downloadInvoiceReportPdf'])->name('work-orders.invoice-report-pdf');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receiptPdf'])->name('invoices.receipt');
    Route::put('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');

    Route::resource('rab', RabController::class);
    Route::get('rab/{rab}/pdf', [RabController::class, 'downloadPdf'])->name('rab.pdf');
    Route::get('rab/{rab}/preview', [RabController::class, 'previewPdf'])->name('rab.preview');
    Route::put('rab/{rab}/approve', [RabController::class, 'approve'])->name('rab.approve');
    Route::put('rab/{rab}/send', [RabController::class, 'send'])->name('rab.send');
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');

    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finance/incomes/create', [FinanceController::class, 'createIncome'])->name('incomes.create');
    Route::post('finance/incomes', [FinanceController::class, 'storeIncome'])->name('incomes.store');
    Route::resource('finance/expenses', FinanceController::class)->names('expenses')->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Super Admin only (Admin no longer has access to staff and devices as requested)
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('staff', StaffController::class);
        Route::get('devices', [\App\Http\Controllers\Admin\UserDeviceController::class, 'index'])->name('devices.index');
    });

    // Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('financial-accounts', \App\Http\Controllers\Admin\FinancialAccountController::class);
        Route::resource('financial-categories', FinancialCategoryController::class)->except(['show']);
        Route::resource('income-categories', FinancialCategoryController::class)
            ->parameters(['income-categories' => 'financialCategory'])
            ->except(['show']);
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('app-versions', \App\Http\Controllers\Admin\AppVersionController::class)->only(['index', 'store', 'destroy']);
    });
});
