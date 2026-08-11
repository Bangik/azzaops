<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Rab;
use App\Models\FinancialTransaction;
use App\Exports\WorkOrdersExport;
use App\Exports\CustomersExport;
use App\Exports\InvoicesExport;
use App\Exports\RabsExport;
use App\Exports\FinanceExport;
use App\Exports\StaffExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'wo');
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $status = $request->get('status');

        $technicians = User::active()->orderBy('name')->get();
        $techId = $request->get('technician_id');

        $data = [];

        switch ($type) {
            case 'customers':
                $data = Customer::withCount(['workOrders' => function ($q) use ($from, $to) {
                        $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                    }])
                    ->orderBy('name')
                    ->paginate(20)
                    ->withQueryString();
                break;

            case 'invoices':
                $query = Invoice::with(['customer', 'workOrder'])
                    ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                if ($status) $query->where('status', $status);
                $data = $query->latest()->paginate(20)->withQueryString();
                break;

            case 'rab':
                $query = Rab::with(['customer', 'workOrder'])
                    ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                if ($status) $query->where('status', $status);
                $data = $query->latest()->paginate(20)->withQueryString();
                break;

            case 'finance':
                $query = FinancialTransaction::with(['category', 'invoice', 'expense', 'recorder'])
                    ->whereBetween('transaction_date', [$from, $to]);
                if ($status) $query->where('type', $status); // status filter maps to transaction type here
                $data = $query->latest('transaction_date')->paginate(20)->withQueryString();
                break;

            case 'staff':
                $data = User::withCount([
                    'assignments' => function ($q) use ($from, $to) {
                        $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                    },
                    'reports' => function ($q) use ($from, $to) {
                        $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                    }
                ])->orderBy('name')->paginate(20)->withQueryString();
                break;

            case 'wo':
            default:
                $query = WorkOrder::with(['customer', 'serviceCategory', 'type', 'assignments.technician'])
                    ->whereBetween('scheduled_date', [$from, $to]);

                if ($techId) {
                    $query->whereHas('assignments', function ($q) use ($techId) {
                        $q->where('technician_id', $techId);
                    });
                }

                if ($status) {
                    $query->where('status', $status);
                }

                $data = $query->latest('scheduled_date')->paginate(20)->withQueryString();
                break;
        }

        return view('admin.reports.index', compact('data', 'technicians', 'from', 'to', 'techId', 'status', 'type'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'wo');
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $status = $request->get('status');
        $techId = $request->get('technician_id');
        $format = $request->get('format', 'xlsx'); // xlsx or csv

        $fileName = 'laporan-' . $type . '-' . $from . '-to-' . $to . '.' . $format;
        $exportClass = null;

        switch ($type) {
            case 'customers':
                $exportClass = new CustomersExport($from, $to);
                break;
            case 'invoices':
                $exportClass = new InvoicesExport($from, $to, $status);
                break;
            case 'rab':
                $exportClass = new RabsExport($from, $to, $status);
                break;
            case 'finance':
                $exportClass = new FinanceExport($from, $to, $status);
                break;
            case 'staff':
                $exportClass = new StaffExport($from, $to);
                break;
            case 'wo':
            default:
                $exportClass = new WorkOrdersExport($from, $to, $techId, $status);
                break;
        }

        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download($exportClass, $fileName, $writerType);
    }
}
