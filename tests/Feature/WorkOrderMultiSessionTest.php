<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Models\Customer;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use App\Services\WorkOrderService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkOrderMultiSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;
    protected Customer $customer;
    protected ServiceCategory $category;
    protected WorkOrderType $type;
    protected WorkOrder $workOrder;
    protected WorkOrderService $workOrderService;
    protected ReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $this->technician = User::factory()->create([
            'role' => UserRole::Teknisi,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Jeruk No 12',
            'type' => 'individual',
        ]);

        $this->category = ServiceCategory::create([
            'name' => 'AC Residential',
            'is_active' => true,
        ]);
        $this->type = WorkOrderType::firstOrCreate([
            'code' => 'service',
        ], [
            'name' => 'Servis',
            'is_active' => true,
        ]);

        $this->workOrderService = app(WorkOrderService::class);
        $this->reportService = app(ReportService::class);

        $this->workOrder = $this->workOrderService->create([
            'work_order_type_id' => $this->type->id,
            'customer_id' => $this->customer->id,
            'service_category_id' => $this->category->id,
            'title' => 'Servis AC Bermasalah',
            'location' => 'Jl. Merdeka No 1',
        ], $this->admin->id);

        $this->workOrderService->assign($this->workOrder, [$this->technician->id], $this->admin->id);
    }

    public function test_multi_session_duration_and_pause_logic(): void
    {
        // 1. Session 1: Day 1, 09:00 - 12:00
        $day1Start = now()->subDays(1)->setTime(9, 0, 0);
        $day1Pause = now()->subDays(1)->setTime(12, 0, 0);

        $this->travelTo($day1Start);
        $this->workOrderService->startSession($this->workOrder, $this->technician->id, WorkOrderStatus::InProgress);

        $this->assertEquals(WorkOrderStatus::InProgress, $this->workOrder->fresh()->status);
        $this->assertCount(1, $this->workOrder->sessions);

        // Travel to 12:00 PM and pause
        $this->travelTo($day1Pause);
        $this->workOrderService->pauseSession($this->workOrder, $this->technician->id, 'Customer mau pergi, dilanjut besok');

        $this->workOrder->refresh();
        $this->assertEquals(WorkOrderStatus::Pending, $this->workOrder->status);
        $this->assertNotNull($this->workOrder->sessions->first()->ended_at);
        $this->assertEquals('Customer mau pergi, dilanjut besok', $this->workOrder->sessions->first()->notes);
        $this->assertEquals(180, $this->workOrder->duration_minutes);

        // 2. Session 2: Day 2, 09:00 - 10:00
        $day2Start = now()->setTime(9, 0, 0);
        $day2End = now()->setTime(10, 0, 0);

        $this->travelTo($day2Start);
        $this->workOrderService->startSession($this->workOrder, $this->technician->id, WorkOrderStatus::InProgress);

        $this->assertCount(2, $this->workOrder->fresh()->sessions);

        $this->travelTo($day2End);

        // Total duration right now (Session 1: 180m + Session 2 open: 60m = 240m = 4 hours)
        $this->assertEquals(240, $this->workOrder->fresh()->duration_minutes);
        $this->assertEquals('4 jam 0 menit', $this->workOrder->fresh()->duration);
    }

    public function test_draft_report_and_photos_cicil_upload(): void
    {
        Storage::fake('public');

        // Login as technician
        $this->actingAs($this->technician, 'sanctum');

        // Start session
        $this->workOrderService->startSession($this->workOrder, $this->technician->id, WorkOrderStatus::InProgress);

        // Upload draft photo on Day 1
        $file1 = UploadedFile::fake()->image('before.jpg');
        $responseDraft1 = $this->postJson(route('api.reports.save-draft', $this->workOrder), [
            'findings' => 'AC tidak dingin, pipa agak bocor',
            'photos' => [
                [
                    'file' => $file1,
                    'type' => 'before',
                    'caption' => 'Kondisi awal sebelum bongkar',
                ]
            ]
        ]);

        $responseDraft1->assertStatus(200)
            ->assertJsonPath('data.is_draft', true);

        $draftReport = $this->reportService->getDraft($this->workOrder, $this->technician->id);
        $this->assertNotNull($draftReport);
        $this->assertTrue($draftReport->is_draft);
        $this->assertCount(1, $draftReport->photos);

        // Pause session
        $this->postJson(route('api.work-orders.pause', $this->workOrder), [
            'notes' => 'Customer pergi',
        ])->assertStatus(200);

        $this->assertEquals(WorkOrderStatus::Pending, $this->workOrder->fresh()->status);

        // Day 2: Resume and submit final report
        $this->putJson(route('api.work-orders.update-status', $this->workOrder), [
            'status' => 'in_progress'
        ])->assertStatus(200);

        $file2 = UploadedFile::fake()->image('after.jpg');
        $responseFinal = $this->postJson(route('api.reports.store', $this->workOrder), [
            'findings' => 'AC tidak dingin, pipa bocor sudah di-las dan isi freon R32',
            'work_done' => 'Pengelasan pipa dan pengisian freon R32 sampai penuh',
            'recommendations' => 'Pembersihan rutin 3 bulan sekali',
            'photos' => [
                [
                    'file' => $file2,
                    'type' => 'after',
                    'caption' => 'Kondisi akhir setelah diisi freon',
                ]
            ]
        ]);

        $responseFinal->assertStatus(200);

        $this->workOrder->refresh();
        $this->assertEquals(WorkOrderStatus::Reported, $this->workOrder->status);

        $finalReport = $this->workOrder->reports()->first();
        $this->assertFalse($finalReport->is_draft);
        $this->assertCount(2, $finalReport->photos);
    }
}
