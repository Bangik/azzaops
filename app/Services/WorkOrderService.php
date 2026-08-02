<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssignment;
use App\Models\Notification;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderService
{
    public function __construct(
        private readonly FcmService $fcmService
    ) {}

    public function create(array $data, int $createdBy): WorkOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $dateStr = now()->format('Ymd');
            $countToday = WorkOrder::whereDate('created_at', now())->count();
            $sequence = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
            $woNumber = 'WO-' . $dateStr . '-' . $sequence;

            $workOrder = WorkOrder::create([
                'wo_number' => $woNumber,
                'type' => $data['type'],
                'customer_id' => $data['customer_id'],
                'service_category_id' => $data['service_category_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'],
                'gmaps_link' => $data['gmaps_link'] ?? null,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'status' => WorkOrderStatus::Pending,
                'parent_wo_id' => $data['parent_wo_id'] ?? null,
                'created_by' => $createdBy,
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (!empty($item['description'])) {
                        $qty = $item['quantity'] ?? 1;
                        $price = $item['unit_price'] ?? 0;
                        $workOrder->items()->create([
                            'description' => $item['description'],
                            'quantity' => $qty,
                            'unit' => $item['unit'] ?? null,
                            'unit_price' => $price,
                            'total_price' => $qty * $price,
                            'notes' => $item['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Auto create invoice draft
            $subtotal = $workOrder->items->sum('total_price');
            $discount = 0;
            $taxPercentage = \App\Models\Setting::get('tax_default', 0);
            $taxable = max(0, $subtotal - $discount);
            $taxAmount = round($taxable * $taxPercentage / 100, 2);
            $total = $taxable + $taxAmount;

            $invCount = \App\Models\Invoice::whereDate('created_at', now())->count() + 1;
            $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($invCount, 4, '0', STR_PAD_LEFT);

            $invoice = \App\Models\Invoice::create([
                'invoice_number' => $invoiceNumber,
                'work_order_id' => $workOrder->id,
                'customer_id' => $workOrder->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => \App\Enums\InvoiceStatus::Draft,
                'payment_status' => \App\Enums\PaymentStatus::Unpaid,
                'paid_amount' => 0,
                'issued_by' => $createdBy,
            ]);

            foreach ($workOrder->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Notify all active Kepala Teknisi
            $kepalaTeknisis = \App\Models\User::role(\App\Enums\UserRole::KepalaTeknisi)
                ->where('is_active', true)
                ->get();

            foreach ($kepalaTeknisis as $kt) {
                // In-app Notification
                Notification::create([
                    'user_id' => $kt->id,
                    'type' => NotificationType::WorkOrderNew,
                    'title' => 'Work Order Baru',
                    'body' => "Perintah kerja baru dibuat: {$workOrder->title} ({$workOrder->wo_number})",
                    'data' => ['work_order_id' => $workOrder->id],
                    'is_read' => false,
                ]);

                // FCM push
                if ($kt->fcm_token) {
                    $this->fcmService->sendToToken(
                        $kt->fcm_token,
                        'Work Order Baru',
                        "Perintah kerja baru: {$workOrder->title} ({$workOrder->wo_number})",
                        ['work_order_id' => $workOrder->id]
                    );
                }
            }

            return $workOrder;
        });
    }

    public function update(WorkOrder $workOrder, array $data): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data) {
            $workOrder->update([
                'type' => $data['type'],
                'customer_id' => $data['customer_id'],
                'service_category_id' => $data['service_category_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'],
                'gmaps_link' => $data['gmaps_link'] ?? null,
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
            ]);

            // Sync items (delete existing and recreate for simplicity)
            if (isset($data['items'])) {
                $workOrder->items()->delete();
                foreach ($data['items'] as $item) {
                    if (!empty($item['description'])) {
                        $qty = $item['quantity'] ?? 1;
                        $price = $item['unit_price'] ?? 0;
                        $workOrder->items()->create([
                            'description' => $item['description'],
                            'quantity' => $qty,
                            'unit' => $item['unit'] ?? null,
                            'unit_price' => $price,
                            'total_price' => $qty * $price,
                            'notes' => $item['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Sync with draft invoice if exists
            $invoice = $workOrder->invoice;
            if ($invoice && $invoice->status === \App\Enums\InvoiceStatus::Draft) {
                $subtotal = $workOrder->items->sum('total_price');
                $discount = $invoice->discount;
                $taxPercentage = $invoice->tax_percentage;
                $taxable = max(0, $subtotal - $discount);
                $taxAmount = round($taxable * $taxPercentage / 100, 2);
                $total = $taxable + $taxAmount;

                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                ]);

                $invoice->items()->delete();
                foreach ($workOrder->items as $item) {
                    $invoice->items()->create([
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ]);
                }
            }

            return $workOrder;
        });
    }

    public function assign(WorkOrder $workOrder, array $technicianIds, int $assignedBy): void
    {
        DB::transaction(function () use ($workOrder, $technicianIds, $assignedBy) {
            // Delete existing pending assignments if any, or just create new ones
            $workOrder->assignments()->where('status', AssignmentStatus::Pending)->delete();

            foreach ($technicianIds as $techId) {
                WorkOrderAssignment::create([
                    'work_order_id' => $workOrder->id,
                    'technician_id' => $techId,
                    'assigned_by' => $assignedBy,
                    'status' => AssignmentStatus::Pending,
                    'assigned_at' => now(),
                ]);

                // Create in-app Notification
                Notification::create([
                    'user_id' => $techId,
                    'type' => NotificationType::WorkOrderAssigned,
                    'title' => 'Pekerjaan Baru Ditugaskan',
                    'body' => "Anda telah ditugaskan untuk pekerjaan: {$workOrder->title} ({$workOrder->wo_number})",
                    'data' => ['work_order_id' => $workOrder->id],
                    'is_read' => false,
                ]);

                // FCM push
                $tech = \App\Models\User::find($techId);
                if ($tech && $tech->fcm_token) {
                    $this->fcmService->sendToToken(
                        $tech->fcm_token,
                        'Pekerjaan Baru Ditugaskan',
                        "Anda telah ditugaskan untuk pekerjaan: {$workOrder->title} ({$workOrder->wo_number})",
                        ['work_order_id' => $workOrder->id]
                    );
                }
            }

            // Update Work Order Status
            $workOrder->update(['status' => WorkOrderStatus::Assigned]);
        });
    }

    public function continueFromChecking(WorkOrder $workOrder, array $newWoData, int $createdBy): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $newWoData, $createdBy) {
            // 1. Update checking invoice to 0
            $invoice = $workOrder->invoice;
            if ($invoice) {
                $invoice->update([
                    'subtotal' => 0,
                    'discount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total' => 0,
                    'paid_amount' => 0,
                    'status' => \App\Enums\InvoiceStatus::Paid,
                    'payment_status' => \App\Enums\PaymentStatus::Paid,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'promo/free',
                    'notes' => 'Pengecekan gratis karena dilanjutkan ke pengerjaan ' . ($newWoData['title'] ?? ''),
                ]);
                $invoice->items()->update([
                    'unit_price' => 0,
                    'total_price' => 0,
                ]);
            }

            // 2. Mark current WO completed
            $workOrder->update([
                'status' => WorkOrderStatus::Completed,
                'completed_at' => now(),
            ]);

            // 3. Create the new follow-up Work Order
            $newWoData['parent_wo_id'] = $workOrder->id;
            $newWoData['customer_id'] = $workOrder->customer_id;
            $newWoData['location'] = $newWoData['location'] ?? $workOrder->location;
            $newWoData['gmaps_link'] = $newWoData['gmaps_link'] ?? $workOrder->gmaps_link;

            return $this->create($newWoData, $createdBy);
        });
    }
}
