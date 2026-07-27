<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Enums\AssignmentStatus;
use App\Services\WorkOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $query = WorkOrderAssignment::with(['workOrder.customer', 'workOrder.serviceCategory', 'technician', 'assigner']);

        if ($user->role === UserRole::Teknisi) {
            $query->where('technician_id', $user->id);
        }

        $assignments = $query->latest()->paginate($request->get('per_page', 15));
        return $this->paginatedResponse($assignments, 'Daftar assignment berhasil diambil');
    }

    public function assign(Request $request, WorkOrder $workOrder)
    {
        $user = $request->user();
        if (!in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi])) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menugaskan teknisi', 403);
        }

        $request->validate([
            'technician_ids' => 'required|array',
            'technician_ids.*' => 'required|integer|exists:users,id',
        ]);

        if (in_array($workOrder->status, [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled])) {
            return $this->errorResponse('Work order ini tidak dapat ditugaskan', 422);
        }

        $workOrderService = new WorkOrderService();
        $workOrderService->assign(
            $workOrder,
            $request->technician_ids,
            $user->id
        );

        return $this->successResponse($workOrder->load('assignments.technician'), 'Teknisi berhasil ditugaskan');
    }

    public function accept(WorkOrderAssignment $assignment)
    {
        if ($assignment->status !== AssignmentStatus::Pending) {
            return $this->errorResponse('Assignment ini tidak dalam status pending', 422);
        }

        $assignment->update([
            'status' => AssignmentStatus::Accepted,
            'accepted_at' => now(),
        ]);

        return $this->successResponse($assignment, 'Assignment diterima');
    }

    public function reject(Request $request, WorkOrderAssignment $assignment)
    {
        if ($assignment->status !== AssignmentStatus::Pending) {
            return $this->errorResponse('Assignment ini tidak dalam status pending', 422);
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $assignment->update([
            'status' => AssignmentStatus::Rejected,
            'notes' => $request->notes,
        ]);

        return $this->successResponse($assignment, 'Assignment ditolak');
    }

    public function complete(WorkOrderAssignment $assignment)
    {
        if ($assignment->status !== AssignmentStatus::Accepted) {
            return $this->errorResponse('Assignment ini belum diterima atau sudah diselesaikan', 422);
        }

        $assignment->update([
            'status' => AssignmentStatus::Completed,
            'completed_at' => now(),
        ]);

        return $this->successResponse($assignment, 'Assignment selesai');
    }

    public function availableTechnicians(Request $request)
    {
        $technicians = User::technicians()->where('is_active', true)->orderBy('name')->get();
        return $this->successResponse($technicians, 'Daftar teknisi tersedia berhasil diambil');
    }
}
