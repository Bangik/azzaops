<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitReportRequest;
use App\Models\WorkOrder;
use App\Models\WorkOrderReport;
use App\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function store(SubmitReportRequest $request, WorkOrder $workOrder)
    {
        // Check if current user is assigned to this work order
        $assigned = $workOrder->assignments()
            ->where('technician_id', $request->user()->id)
            ->exists();

        if (!$assigned) {
            return $this->errorResponse('Anda tidak ditugaskan pada perintah kerja ini', 403);
        }

        $report = $this->reportService->submit(
            $workOrder,
            $request->validated(),
            $request->user()->id
        );

        $report->load('photos');

        return $this->successResponse($report, 'Laporan berhasil dikirim');
    }

    public function index(WorkOrder $workOrder)
    {
        $reports = $workOrder->reports()->with(['technician', 'photos'])->latest()->get();

        return $this->successResponse($reports, 'Daftar laporan berhasil diambil');
    }

    public function myReports(Request $request)
    {
        $reports = WorkOrderReport::with(['workOrder.customer', 'photos'])
            ->where('technician_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->paginatedResponse($reports, 'Daftar laporan saya berhasil diambil');
    }
}
