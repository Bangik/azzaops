<?php

namespace App\Services;

use App\Enums\RabStatus;
use App\Enums\WorkOrderStatus;
use App\Models\Rab;
use App\Models\Setting;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class RabService
{
    public function create(array $data, int $createdBy): Rab
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $workOrder = WorkOrder::findOrFail($data['work_order_id']);
            $items = $data['items'] ?? [];

            $subtotal = collect($items)->sum(fn ($i) => ($i['quantity'] ?? 1) * ($i['unit_price'] ?? 0));
            $discount = (float) ($data['discount'] ?? 0);
            $taxPercentage = (float) ($data['tax_percentage'] ?? Setting::get('tax_default', 0));
            $taxable = max(0, $subtotal - $discount);
            $taxAmount = round($taxable * $taxPercentage / 100, 2);
            $total = $taxable + $taxAmount;

            $dateStr = now()->format('Ymd');
            $count = Rab::whereDate('created_at', now())->count() + 1;
            $rabNumber = 'RAB-' . $dateStr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $rab = Rab::create([
                'rab_number' => $rabNumber,
                'work_order_id' => $workOrder->id,
                'customer_id' => $workOrder->customer_id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => RabStatus::Draft,
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $createdBy,
            ]);

            foreach ($items as $item) {
                if (empty($item['description'])) {
                    continue;
                }
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $rab->items()->create([
                    'category' => $item['category'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $price,
                    'total_price' => $qty * $price,
                ]);
            }

            return $rab->load('items');
        });
    }

    public function markSent(Rab $rab): Rab
    {
        $rab->update(['status' => RabStatus::Sent]);
        $rab->workOrder?->update(['status' => WorkOrderStatus::Negotiating]);

        return $rab->fresh();
    }

    public function markApproved(Rab $rab): Rab
    {
        $rab->update([
            'status' => RabStatus::Approved,
            'approved_at' => now(),
        ]);
        $rab->workOrder?->update(['status' => WorkOrderStatus::Approved]);

        return $rab->fresh();
    }
}
