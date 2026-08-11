<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Enums\AssignmentStatus;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        
        $todayAssignments = 0;
        $pendingAssignments = 0;
        $completedToday = 0;
        $totalCompleted = 0;
        $recentWorkOrders = [];

        if ($user->role === UserRole::Teknisi) {
            // Count for this technician
            $todayAssignments = WorkOrderAssignment::where('technician_id', $user->id)
                ->whereHas('workOrder', function ($q) {
                    $q->whereDate('scheduled_date', today());
                })->count();

            $pendingAssignments = WorkOrderAssignment::where('technician_id', $user->id)
                ->where('status', AssignmentStatus::Pending)
                ->count();

            $completedToday = WorkOrderAssignment::where('technician_id', $user->id)
                ->where('status', AssignmentStatus::Completed)
                ->whereDate('completed_at', today())
                ->count();

            $totalCompleted = WorkOrderAssignment::where('technician_id', $user->id)
                ->where('status', AssignmentStatus::Completed)
                ->count();

            // Recent work orders assigned to this technician
            $recentWorkOrders = WorkOrder::whereHas('assignments', function ($q) use ($user) {
                    $q->where('technician_id', $user->id);
                })
                ->with(['customer', 'serviceCategory', 'type'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

        } else if ($user->role === UserRole::KepalaTeknisi || $user->role === UserRole::SuperAdmin || $user->role === UserRole::Admin) {
            // Count overall
            $todayAssignments = WorkOrder::whereDate('scheduled_date', today())->count();
            
            // Pending assign means work order with status pending (no technician assigned yet)
            $pendingAssignments = WorkOrder::where('status', WorkOrderStatus::Pending)->count();

            $completedToday = WorkOrder::where('status', WorkOrderStatus::Completed)
                ->whereDate('completed_at', today())
                ->count();

            $totalCompleted = WorkOrder::where('status', WorkOrderStatus::Completed)->count();

            // Recent work orders overall
            $recentWorkOrders = WorkOrder::with(['customer', 'serviceCategory', 'type'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return $this->successResponse([
            'today_assignments' => $todayAssignments,
            'pending_assignments' => $pendingAssignments,
            'completed_today' => $completedToday,
            'total_completed' => $totalCompleted,
            'recent_work_orders' => $recentWorkOrders,
        ], 'Data dashboard berhasil diambil');
    }
}
