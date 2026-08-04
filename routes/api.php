<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;

Route::prefix('v1')->name('api.')->group(function () {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('me');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('profile/fcm-token', [ProfileController::class, 'updateFcmToken'])->name('profile.fcm-token');

        // Work Orders
        Route::get('work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
        Route::get('work-orders/today', [WorkOrderController::class, 'today'])->name('work-orders.today');
        Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
        Route::put('work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
        Route::post('work-orders/{workOrder}/takeover', [WorkOrderController::class, 'requestTakeover'])->name('work-orders.takeover');

        // Takeovers
        Route::post('takeovers/{takeover}/approve', [WorkOrderController::class, 'approveTakeover'])->name('takeovers.approve');
        Route::post('takeovers/{takeover}/reject', [WorkOrderController::class, 'rejectTakeover'])->name('takeovers.reject');

        // Assignments
        Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('work-orders/{workOrder}/assign', [AssignmentController::class, 'assign'])->name('assignments.assign');
        Route::put('assignments/{assignment}/accept', [AssignmentController::class, 'accept'])->name('assignments.accept');
        Route::put('assignments/{assignment}/reject', [AssignmentController::class, 'reject'])->name('assignments.reject');
        Route::put('assignments/{assignment}/complete', [AssignmentController::class, 'complete'])->name('assignments.complete');
        Route::get('technicians/available', [AssignmentController::class, 'availableTechnicians'])->name('technicians.available');

        // Reports
        Route::post('work-orders/{workOrder}/reports', [ReportController::class, 'store'])->name('reports.store');
        Route::get('work-orders/{workOrder}/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/my', [ReportController::class, 'myReports'])->name('reports.my');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // User Devices
        Route::post('devices', [\App\Http\Controllers\Api\DeviceController::class, 'upsert'])->name('devices.upsert');
    });
});
