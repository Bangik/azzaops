<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Enums\WorkOrderStatus;
use App\Enums\AssignmentStatus;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Filter date: defaults to today if not provided or empty
        $filterDate = $request->filled('date') ? $request->get('date') : today()->toDateString();

        // 1. angka semua pekerjaan anda (ditugaskan ke teknisi yang login pada tanggal tsb)
        $myTotalWorkOrders = WorkOrder::whereHas('assignments', function ($q) use ($user) {
                $q->where('technician_id', $user->id);
            })
            ->whereDate('scheduled_date', $filterDate)
            ->count();

        // 2. angka semua pekerjaan (seluruh work order di sistem pada tanggal tsb)
        $allTotalWorkOrders = WorkOrder::whereDate('scheduled_date', $filterDate)
            ->count();

        // 3. angka pekerjaan selesai anda (pekerjaan teknisi yang login berstatus selesai pada tanggal tsb)
        $myCompletedWorkOrders = WorkOrder::whereHas('assignments', function ($q) use ($user) {
                $q->where('technician_id', $user->id);
            })
            ->where('status', WorkOrderStatus::Completed)
            ->whereDate('scheduled_date', $filterDate)
            ->count();

        // 4. angka semua pekerjaan selesai (seluruh pekerjaan di sistem yang selesai pada tanggal tsb)
        $allCompletedWorkOrders = WorkOrder::where('status', WorkOrderStatus::Completed)
            ->whereDate('scheduled_date', $filterDate)
            ->count();

        // Recent work orders (max 5)
        $recentWorkOrders = WorkOrder::whereHas('assignments', function ($q) use ($user) {
                $q->where('technician_id', $user->id);
            })
            ->with(['customer', 'serviceCategory', 'type'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'date' => $filterDate,
            // 4 Card Metrics
            'my_total_work_orders' => $myTotalWorkOrders,
            'all_total_work_orders' => $allTotalWorkOrders,
            'my_completed_work_orders' => $myCompletedWorkOrders,
            'all_completed_work_orders' => $allCompletedWorkOrders,

            // Backward compatibility aliases for existing mobile app code if needed
            'today_assignments' => $myTotalWorkOrders,
            'pending_assignments' => WorkOrderAssignment::where('technician_id', $user->id)->where('status', AssignmentStatus::Pending)->count(),
            'completed_today' => $myCompletedWorkOrders,
            'total_completed' => $allCompletedWorkOrders,

            'recent_work_orders' => $recentWorkOrders,
        ], 'Data dashboard berhasil diambil');
    }
}

