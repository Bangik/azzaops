<?php

namespace App\Enums;

enum NotificationType: string
{
    case WorkOrderNew = 'work_order_new';
    case WorkOrderAssigned = 'work_order_assigned';
    case WorkOrderUpdated = 'work_order_updated';
    case ReportSubmitted = 'report_submitted';
    case InvoiceCreated = 'invoice_created';
    case PaymentReceived = 'payment_received';
}
