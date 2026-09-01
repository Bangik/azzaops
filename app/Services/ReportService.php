<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderReport;
use App\Models\WorkOrderAssignment;
use App\Models\Notification;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        private readonly FcmService $fcmService
    ) {}

    public function submit(WorkOrder $workOrder, array $data, int $technicianId): WorkOrderReport
    {
        return DB::transaction(function () use ($workOrder, $data, $technicianId) {
            // Check if report was already submitted in this same minute or pending duplicate submission
            $existingReport = WorkOrderReport::where('work_order_id', $workOrder->id)
                ->where('technician_id', $technicianId)
                ->where('created_at', '>=', now()->subSeconds(30))
                ->first();

            if ($existingReport) {
                return $existingReport;
            }

            // Create Report
            $report = WorkOrderReport::create([
                'work_order_id' => $workOrder->id,
                'technician_id' => $technicianId,
                'findings' => $data['findings'],
                'work_done' => $data['work_done'],
                'recommendations' => $data['recommendations'] ?? null,
                'materials_used' => $data['materials_used'] ?? null,
                'submitted_at' => now(),
            ]);

            // Save photos if present
            if (isset($data['photos']) && is_array($data['photos'])) {
                $uploadPath = public_path("uploads/reports/{$workOrder->id}");
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                foreach ($data['photos'] as $photoData) {
                    if (isset($photoData['file']) && $photoData['file']->isValid()) {
                        $file = $photoData['file'];
                        $fileSize = $file->getSize();
                        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                        $file->move($uploadPath, $filename);

                        $report->photos()->create([
                            'photo_path' => "uploads/reports/{$workOrder->id}/{$filename}",
                            'photo_type' => $photoData['type'],
                            'caption' => $photoData['caption'] ?? null,
                            'file_size' => $fileSize,
                        ]);
                    }
                }
            }

            // Update assignment status for this technician
            WorkOrderAssignment::where('work_order_id', $workOrder->id)
                ->where('technician_id', $technicianId)
                ->update([
                    'status' => AssignmentStatus::Completed,
                    'completed_at' => now(),
                ]);

            // Update Work Order Status to reported & set completed_at if not set
            $workOrder->update([
                'status' => WorkOrderStatus::Reported,
                'completed_at' => $workOrder->completed_at ?? now(),
            ]);

            // Find administrators / kepala teknisi to notify
            $managers = \App\Models\User::whereIn('role', [\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Admin, \App\Enums\UserRole::KepalaTeknisi])
                ->where('is_active', true)
                ->get();

            $technicianName = \App\Models\User::find($technicianId)?->name ?? 'Teknisi';

            foreach ($managers as $manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => NotificationType::ReportSubmitted,
                    'title' => 'Laporan Pekerjaan Disubmit',
                    'body' => "Teknisi " . $technicianName . " telah mengirim laporan untuk " . $workOrder->wo_number,
                    'data' => [
                        'work_order_id' => $workOrder->id,
                        'report_id' => $report->id,
                    ],
                ]);

                if ($manager->fcm_token) {
                    try {
                        $this->fcmService->sendToToken(
                            $manager->fcm_token,
                            'Laporan Pekerjaan Disubmit',
                            "Teknisi " . $technicianName . " telah mengirim laporan untuk " . $workOrder->wo_number,
                            [
                                'work_order_id' => $workOrder->id,
                                'report_id' => $report->id,
                            ]
                        );
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('FCM notification failed: ' . $e->getMessage());
                    }
                }
            }

            return $report;
        });
    }
}
