<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Models\WorkOrderTakeover;
use App\Models\Notification;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Enums\AssignmentStatus;
use App\Traits\ApiResponse;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FcmService $fcmService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = WorkOrder::with(['customer', 'serviceCategory', 'assignments.technician', 'reports.photos']);

        if ($user->role === UserRole::Teknisi || $user->role === UserRole::KepalaTeknisi) {
            $query->whereNotIn('status', [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        $workOrders = $query->orderByRaw('CASE WHEN job_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('job_order', 'asc')
            ->orderByRaw('CASE WHEN scheduled_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($workOrders, 'Daftar work order berhasil diambil');
    }

    public function today(Request $request)
    {
        $user = $request->user();
        $query = WorkOrder::with(['customer', 'serviceCategory', 'assignments.technician', 'reports.photos'])
            ->whereDate('scheduled_date', today());

        if ($user->role === UserRole::Teknisi || $user->role === UserRole::KepalaTeknisi) {
            $query->whereNotIn('status', [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled]);
        }

        $workOrders = $query->orderByRaw('CASE WHEN job_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('job_order', 'asc')
            ->orderByRaw('CASE WHEN scheduled_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_time', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($workOrders, 'Daftar work order hari ini berhasil diambil');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer',
            'serviceCategory',
            'creator',
            'items',
            'assignments.technician',
            'assignments.assigner',
            'reports.technician',
            'reports.photos',
            'invoice',
            'rab',
            'parentWorkOrder',
            'takeovers.requester',
            'takeovers.originalTechnician',
        ]);

        return $this->successResponse($workOrder, 'Detail work order berhasil diambil');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $statusStr = $request->status;
        $status = null;
        
        // Find matching status enum
        foreach (WorkOrderStatus::cases() as $case) {
            if ($case->value === $statusStr) {
                $status = $case;
                break;
            }
        }

        if (!$status) {
            return $this->errorResponse('Status tidak valid', 422);
        }

        // Update status of work order
        $workOrder->update([
            'status' => $status,
            'notes' => $request->notes ?? $workOrder->notes
        ]);

        // If status is completed, update assignments status to completed as well
        if ($status === WorkOrderStatus::Completed) {
            $workOrder->assignments()->update([
                'status' => AssignmentStatus::Completed,
                'completed_at' => now(),
            ]);
        }

        // If status is in_progress, set started_at if not already set
        if ($status === WorkOrderStatus::InProgress && !$workOrder->started_at) {
            $workOrder->update(['started_at' => now()]);
        }

        // Notify managers about status change
        $managers = \App\Models\User::whereIn('role', [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi])
            ->where('is_active', true)
            ->get();

        foreach ($managers as $manager) {
            \App\Models\Notification::create([
                'user_id' => $manager->id,
                'type' => \App\Enums\NotificationType::WorkOrderUpdated,
                'title' => 'Status Work Order Diperbarui',
                'body' => "Status WO {$workOrder->wo_number} diperbarui menjadi: " . $status->label() . " oleh " . $request->user()->name,
                'data' => ['work_order_id' => $workOrder->id],
                'is_read' => false,
            ]);

            if ($manager->fcm_token) {
                $this->fcmService->sendToToken(
                    $manager->fcm_token,
                    'Status Work Order Diperbarui',
                    "Status WO {$workOrder->wo_number} diperbarui menjadi: " . $status->label() . " oleh " . $request->user()->name,
                    ['work_order_id' => $workOrder->id]
                );
            }
        }

        return $this->successResponse($workOrder->load([
            'customer',
            'serviceCategory',
            'creator',
            'items',
            'assignments.technician',
            'reports.photos',
        ]), 'Status work order berhasil diperbarui');
    }

    public function requestTakeover(Request $request, WorkOrder $workOrder)
    {
        $user = $request->user();
        if ($user->role !== UserRole::Teknisi && $user->role !== UserRole::KepalaTeknisi) {
            return $this->errorResponse('Hanya teknisi atau kepala teknisi yang dapat mengajukan pengambilalihan pekerjaan', 403);
        }

        // Ensure the work order is not yet started (status must be pending or assigned)
        if (!in_array($workOrder->status, [WorkOrderStatus::Pending, WorkOrderStatus::Assigned], true)) {
            return $this->errorResponse('Pekerjaan sudah mulai dikerjakan atau selesai, tidak dapat diambil alih.', 422);
        }

        // Find active assignment
        $originalAssignment = $workOrder->assignments()
            ->whereIn('status', [AssignmentStatus::Pending, AssignmentStatus::Accepted])
            ->first();

        if (!$originalAssignment) {
            return $this->errorResponse('Perintah kerja ini tidak sedang ditugaskan kepada teknisi manapun', 422);
        }

        if ($originalAssignment->technician_id === $user->id) {
            return $this->errorResponse('Perintah kerja ini sudah ditugaskan kepada Anda', 422);
        }

        // Check if there is already a pending takeover request for this work order
        $exists = WorkOrderTakeover::where('work_order_id', $workOrder->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return $this->errorResponse('Pengajuan pengalihan untuk perintah kerja ini sedang diproses', 422);
        }

        $takeover = WorkOrderTakeover::create([
            'work_order_id' => $workOrder->id,
            'requested_by' => $user->id,
            'original_technician_id' => $originalAssignment->technician_id,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // 1. Notify Original Technician
        Notification::create([
            'user_id' => $originalAssignment->technician_id,
            'type' => NotificationType::WorkOrderUpdated,
            'title' => 'Permintaan Pengalihan Pekerjaan',
            'body' => "Teknisi {$user->name} meminta untuk mengambil alih pekerjaan Anda: {$workOrder->wo_number}",
            'data' => ['work_order_id' => $workOrder->id, 'takeover_id' => $takeover->id],
        ]);

        if ($originalAssignment->technician->fcm_token) {
            $this->fcmService->sendToToken(
                $originalAssignment->technician->fcm_token,
                'Permintaan Pengalihan Pekerjaan',
                "Teknisi {$user->name} meminta untuk mengambil alih pekerjaan Anda: {$workOrder->wo_number}",
                ['work_order_id' => $workOrder->id, 'takeover_id' => $takeover->id]
            );
        }

        // 2. Notify Managers (Kepala Teknisi & Admin)
        $managers = \App\Models\User::whereIn('role', [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi])
            ->where('is_active', true)
            ->get();

        foreach ($managers as $manager) {
            Notification::create([
                'user_id' => $manager->id,
                'type' => NotificationType::WorkOrderUpdated,
                'title' => 'Permintaan Pengalihan Pekerjaan',
                'body' => "Teknisi {$user->name} mengajukan pengambilalihan WO {$workOrder->wo_number} dari {$originalAssignment->technician->name}",
                'data' => ['work_order_id' => $workOrder->id, 'takeover_id' => $takeover->id],
            ]);

            if ($manager->fcm_token) {
                $this->fcmService->sendToToken(
                    $manager->fcm_token,
                    'Permintaan Pengalihan Pekerjaan',
                    "Teknisi {$user->name} mengajukan pengambilalihan WO {$workOrder->wo_number} dari {$originalAssignment->technician->name}",
                    ['work_order_id' => $workOrder->id, 'takeover_id' => $takeover->id]
                );
            }
        }

        return $this->successResponse($takeover, 'Permintaan pengambilalihan berhasil diajukan');
    }

    public function approveTakeover(Request $request, WorkOrderTakeover $takeover)
    {
        $user = $request->user();
        
        // Authorization check: Must be original technician, kepala_teknisi, admin, or super_admin
        $isOriginalTech = ($takeover->original_technician_id === $user->id);
        $isManager = in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi], true);

        if (!$isOriginalTech && !$isManager) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menyetujui pengalihan ini', 403);
        }

        if ($takeover->status !== 'pending') {
            return $this->errorResponse('Pengalihan ini sudah diproses sebelumnya', 422);
        }

        DB::transaction(function () use ($takeover, $user) {
            $takeover->update([
                'status' => 'approved',
                'approved_by' => $user->id,
            ]);

            // Update original technician's active assignment to transferred
            WorkOrderAssignment::where('work_order_id', $takeover->work_order_id)
                ->where('technician_id', $takeover->original_technician_id)
                ->whereIn('status', [AssignmentStatus::Pending, AssignmentStatus::Accepted])
                ->update([
                    'status' => AssignmentStatus::Transferred,
                    'completed_at' => now(),
                ]);

            // Create new assignment for the requesting technician
            WorkOrderAssignment::create([
                'work_order_id' => $takeover->work_order_id,
                'technician_id' => $takeover->requested_by,
                'assigned_by' => ($user->role === UserRole::Teknisi || $user->role === UserRole::KepalaTeknisi) ? $takeover->original_technician_id : $user->id,
                'status' => AssignmentStatus::Accepted, // Auto accept upon approval
                'assigned_at' => now(),
                'accepted_at' => now(),
            ]);

            // Ensure work order status is assigned or in_progress (if original was accepted)
            $workOrder = $takeover->workOrder;
            if ($workOrder->status === WorkOrderStatus::Pending) {
                $workOrder->update(['status' => WorkOrderStatus::Assigned]);
            }

            // Notify Requester
            $requester = $takeover->requester;
            Notification::create([
                'user_id' => $takeover->requested_by,
                'type' => NotificationType::WorkOrderUpdated,
                'title' => 'Pengalihan Pekerjaan Disetujui',
                'body' => "Permintaan pengambilalihan WO {$workOrder->wo_number} telah disetujui.",
                'data' => ['work_order_id' => $workOrder->id],
            ]);

            if ($requester->fcm_token) {
                $this->fcmService->sendToToken(
                    $requester->fcm_token,
                    'Pengalihan Pekerjaan Disetujui',
                    "Permintaan pengambilalihan WO {$workOrder->wo_number} telah disetujui.",
                    ['work_order_id' => $workOrder->id]
                );
            }

            // Notify Original Technician (if approved by manager)
            if ($takeover->original_technician_id !== $user->id) {
                $originalTech = $takeover->originalTechnician;
                Notification::create([
                    'user_id' => $takeover->original_technician_id,
                    'type' => NotificationType::WorkOrderUpdated,
                    'title' => 'Pekerjaan Dialihkan',
                    'body' => "Pekerjaan WO {$workOrder->wo_number} telah dialihkan ke {$requester->name} oleh administrator.",
                    'data' => ['work_order_id' => $workOrder->id],
                ]);

                if ($originalTech->fcm_token) {
                    $this->fcmService->sendToToken(
                        $originalTech->fcm_token,
                        'Pekerjaan Dialihkan',
                        "Pekerjaan WO {$workOrder->wo_number} telah dialihkan ke {$requester->name} oleh administrator.",
                        ['work_order_id' => $workOrder->id]
                    );
                }
            }
        });

        return $this->successResponse($takeover->load(['workOrder', 'requester', 'originalTechnician']), 'Pengambilalihan pekerjaan berhasil disetujui');
    }

    public function rejectTakeover(Request $request, WorkOrderTakeover $takeover)
    {
        $user = $request->user();

        $isOriginalTech = ($takeover->original_technician_id === $user->id);
        $isManager = in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi], true);

        if (!$isOriginalTech && !$isManager) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menolak pengalihan ini', 403);
        }

        if ($takeover->status !== 'pending') {
            return $this->errorResponse('Pengalihan ini sudah diproses sebelumnya', 422);
        }

        $takeover->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
        ]);

        $workOrder = $takeover->workOrder;

        // Notify Requester
        $requester = $takeover->requester;
        Notification::create([
            'user_id' => $takeover->requested_by,
            'type' => NotificationType::WorkOrderUpdated,
            'title' => 'Pengalihan Pekerjaan Ditolak',
            'body' => "Permintaan pengambilalihan WO {$workOrder->wo_number} ditolak.",
            'data' => ['work_order_id' => $workOrder->id],
        ]);

        if ($requester->fcm_token) {
            $this->fcmService->sendToToken(
                $requester->fcm_token,
                'Pengalihan Pekerjaan Ditolak',
                "Permintaan pengambilalihan WO {$workOrder->wo_number} ditolak.",
                ['work_order_id' => $workOrder->id]
            );
        }

        return $this->successResponse($takeover, 'Pengambilalihan pekerjaan ditolak');
    }
}
