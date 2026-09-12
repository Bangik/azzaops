<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderReport;
use App\Models\WorkOrderReportPhoto;
use App\Models\WorkOrderAssignment;
use App\Models\Notification;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportService
{
    public function getDraft(WorkOrder $workOrder, int $technicianId): ?WorkOrderReport
    {
        return WorkOrderReport::with('photos')
            ->where('work_order_id', $workOrder->id)
            ->where('technician_id', $technicianId)
            ->where('is_draft', true)
            ->first();
    }

    public function saveDraft(WorkOrder $workOrder, array $data, int $technicianId): WorkOrderReport
    {
        return DB::transaction(function () use ($workOrder, $data, $technicianId) {
            $report = WorkOrderReport::where('work_order_id', $workOrder->id)
                ->where('technician_id', $technicianId)
                ->where('is_draft', true)
                ->first();

            if (!$report) {
                $report = WorkOrderReport::create([
                    'work_order_id' => $workOrder->id,
                    'technician_id' => $technicianId,
                    'findings' => $data['findings'] ?? '',
                    'work_done' => $data['work_done'] ?? '',
                    'recommendations' => $data['recommendations'] ?? null,
                    'materials_used' => $data['materials_used'] ?? null,
                    'is_draft' => true,
                ]);
            } else {
                $updateData = [];
                if (isset($data['findings'])) {
                    $updateData['findings'] = $data['findings'];
                }
                if (isset($data['work_done'])) {
                    $updateData['work_done'] = $data['work_done'];
                }
                if (array_key_exists('recommendations', $data)) {
                    $updateData['recommendations'] = $data['recommendations'];
                }
                if (array_key_exists('materials_used', $data)) {
                    $updateData['materials_used'] = $data['materials_used'];
                }

                if (!empty($updateData)) {
                    $report->update($updateData);
                }
            }

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
                            'photo_type' => $photoData['type'] ?? 'progress',
                            'caption' => $photoData['caption'] ?? null,
                            'file_size' => $fileSize,
                        ]);
                    }
                }
            }

            return $report->load('photos');
        });
    }

    public function deleteDraftPhoto(int $photoId, int $technicianId): bool
    {
        $photo = WorkOrderReportPhoto::where('id', $photoId)
            ->whereHas('report', function ($q) use ($technicianId) {
                $q->where('technician_id', $technicianId)->where('is_draft', true);
            })
            ->first();

        if (!$photo) {
            return false;
        }

        if ($photo->photo_path && file_exists(public_path($photo->photo_path))) {
            @unlink(public_path($photo->photo_path));
        }

        $photo->delete();

        return true;
    }

    public function submit(WorkOrder $workOrder, array $data, int $technicianId): WorkOrderReport
    {
        return DB::transaction(function () use ($workOrder, $data, $technicianId) {
            // Check if there is an existing draft report for this technician & WO
            $report = WorkOrderReport::where('work_order_id', $workOrder->id)
                ->where('technician_id', $technicianId)
                ->where('is_draft', true)
                ->first();

            if ($report) {
                $report->update([
                    'findings' => $data['findings'],
                    'work_done' => $data['work_done'],
                    'recommendations' => $data['recommendations'] ?? $report->recommendations,
                    'materials_used' => $data['materials_used'] ?? $report->materials_used,
                    'is_draft' => false,
                    'submitted_at' => now(),
                ]);
            } else {
                // Check if report was already submitted in this same 30 seconds
                $existingReport = WorkOrderReport::where('work_order_id', $workOrder->id)
                    ->where('technician_id', $technicianId)
                    ->where('is_draft', false)
                    ->where('created_at', '>=', now()->subSeconds(30))
                    ->first();

                if ($existingReport) {
                    return $existingReport;
                }

                $report = WorkOrderReport::create([
                    'work_order_id' => $workOrder->id,
                    'technician_id' => $technicianId,
                    'findings' => $data['findings'],
                    'work_done' => $data['work_done'],
                    'recommendations' => $data['recommendations'] ?? null,
                    'materials_used' => $data['materials_used'] ?? null,
                    'is_draft' => false,
                    'submitted_at' => now(),
                ]);
            }

            // Save new photos if present
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
                            'photo_type' => $photoData['type'] ?? 'after',
                            'caption' => $photoData['caption'] ?? null,
                            'file_size' => $fileSize,
                        ]);
                    }
                }
            }

            // Close active session
            app(WorkOrderService::class)->endSession($workOrder);

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
            }

            return $report->load('photos');
        });
    }
}
