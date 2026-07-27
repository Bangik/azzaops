<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Enums\WorkOrderStatus;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly FcmService $fcmService
    ) {}

    public function createFromWorkOrder(WorkOrder $workOrder, array $data, int $issuedBy): Invoice
    {
        return DB::transaction(function () use ($workOrder, $data, $issuedBy) {
            $items = $data['items'] ?? $workOrder->items->map(fn ($i) => [
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit' => $i->unit,
                'unit_price' => $i->unit_price,
            ])->toArray();

            $subtotal = collect($items)->sum(fn ($i) => ($i['quantity'] ?? 1) * ($i['unit_price'] ?? 0));
            $discount = (float) ($data['discount'] ?? 0);
            $taxPercentage = (float) ($data['tax_percentage'] ?? Setting::get('tax_default', 0));
            $taxable = max(0, $subtotal - $discount);
            $taxAmount = round($taxable * $taxPercentage / 100, 2);
            $total = $taxable + $taxAmount;

            $dateStr = now()->format('Ymd');
            $count = Invoice::whereDate('created_at', now())->count() + 1;
            $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'work_order_id' => $workOrder->id,
                'customer_id' => $workOrder->customer_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => InvoiceStatus::Draft,
                'payment_status' => PaymentStatus::Unpaid,
                'paid_amount' => 0,
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'issued_by' => $issuedBy,
            ]);

            foreach ($items as $item) {
                if (empty($item['description'])) {
                    continue;
                }
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $price,
                    'total_price' => $qty * $price,
                ]);
            }

            if ($workOrder->status === WorkOrderStatus::Reported) {
                $workOrder->update(['status' => WorkOrderStatus::InvoiceSent]);
            }

            return $invoice->load('items');
        });
    }

    public function markPaid(Invoice $invoice, array $data, int $recordedBy): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $recordedBy) {
            $paidAmount = (float) ($data['paid_amount'] ?? $invoice->total);
            $paymentStatus = $paidAmount >= $invoice->total
                ? PaymentStatus::Paid
                : PaymentStatus::Partial;

            $invoice->update([
                'status' => $paymentStatus === PaymentStatus::Paid ? InvoiceStatus::Paid : InvoiceStatus::Sent,
                'payment_status' => $paymentStatus,
                'paid_amount' => $paidAmount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $data['payment_method'] ?? null,
            ]);

            // Avoid duplicate income for same invoice full payment
            $existing = FinancialTransaction::where('invoice_id', $invoice->id)
                ->where('type', TransactionType::Income)
                ->sum('amount');

            $delta = max(0, $paidAmount - (float) $existing);
            if ($delta > 0) {
                $incomeCategoryId = \App\Models\FinancialCategory::income()
                    ->where('name', 'Pembayaran Jasa')
                    ->value('id');

                FinancialTransaction::create([
                    'type' => TransactionType::Income,
                    'category_id' => $incomeCategoryId,
                    'invoice_id' => $invoice->id,
                    'amount' => $delta,
                    'transaction_date' => $data['payment_date'] ?? now()->toDateString(),
                    'description' => 'Pembayaran invoice ' . $invoice->invoice_number,
                    'reference_number' => $data['payment_method'] ?? null,
                    'recorded_by' => $recordedBy,
                ]);
            }

            if ($paymentStatus === PaymentStatus::Paid) {
                $invoice->workOrder?->update(['status' => WorkOrderStatus::Completed]);
            }

            // Notify managers about payment
            $managers = \App\Models\User::whereIn('role', [\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Admin, \App\Enums\UserRole::KepalaTeknisi])
                ->where('is_active', true)
                ->get();

            foreach ($managers as $manager) {
                \App\Models\Notification::create([
                    'user_id' => $manager->id,
                    'type' => \App\Enums\NotificationType::PaymentReceived,
                    'title' => 'Pembayaran Diterima',
                    'body' => "Pembayaran sebesar Rp " . number_format($paidAmount, 0, ',', '.') . " diterima untuk invoice " . $invoice->invoice_number,
                    'data' => [
                        'invoice_id' => $invoice->id,
                        'work_order_id' => $invoice->work_order_id,
                    ],
                    'is_read' => false,
                ]);

                if ($manager->fcm_token) {
                    $this->fcmService->sendToToken(
                        $manager->fcm_token,
                        'Pembayaran Diterima',
                        "Pembayaran sebesar Rp " . number_format($paidAmount, 0, ',', '.') . " diterima untuk invoice " . $invoice->invoice_number,
                        [
                            'invoice_id' => $invoice->id,
                            'work_order_id' => $invoice->work_order_id,
                        ]
                    );
                }
            }

            return $invoice->fresh();
        });
    }
}
