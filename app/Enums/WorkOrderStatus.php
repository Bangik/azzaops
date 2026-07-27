<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Checking = 'checking';
    case Reported = 'reported';
    case InvoiceSent = 'invoice_sent';
    case Negotiating = 'negotiating';
    case Approved = 'approved';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Assigned => 'Ditugaskan',
            self::InProgress => 'Dikerjakan',
            self::Checking => 'Pengecekan',
            self::Reported => 'Dilaporkan',
            self::InvoiceSent => 'Invoice Terkirim',
            self::Negotiating => 'Negosiasi',
            self::Approved => 'Disetujui',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Assigned => 'info',
            self::InProgress => 'primary',
            self::Checking => 'secondary',
            self::Reported => 'dark',
            self::InvoiceSent => 'info',
            self::Negotiating => 'warning',
            self::Approved => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
