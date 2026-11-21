<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WebRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_log_into_web(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->post('/login', ['email' => $supervisor->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($supervisor);
    }

    public function test_technicians_cannot_log_into_web_but_can_use_mobile_login(): void
    {
        foreach ([UserRole::Teknisi, UserRole::KepalaTeknisi] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertSessionHasErrors('email');
            $this->assertGuest();
            $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
            $this->post(route('logout'));

            $this->postJson(route('api.login'), ['email' => $user->email, 'password' => 'password'])
                ->assertOk()->assertJsonPath('data.user.role', $role->value);
            $this->post(route('logout'));
        }
    }

    public function test_web_menu_access_for_admin_and_supervisor(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        foreach (['dashboard', 'work-orders.index', 'customers.index', 'service-categories.index',
            'work-order-types.index', 'vendors.index', 'attendances.my', 'google-sheet-sync.index'] as $name) {
            $this->actingAs($supervisor)->get(route('admin.'.$name))->assertOk();
            $this->actingAs($admin)->get(route('admin.'.$name))->assertOk();
        }

        foreach (['staff.index', 'devices.index', 'attendances.index', 'app-versions.index'] as $name) {
            $this->actingAs($supervisor)->get(route('admin.'.$name))->assertOk();
            $this->actingAs($admin)->get(route('admin.'.$name))->assertForbidden();
        }

        foreach (['invoices.index', 'rab.index', 'finance.index', 'vendor-invoices.create',
            'combined-invoices.create', 'settings.index'] as $name) {
            $this->actingAs($admin)->get(route('admin.'.$name))->assertOk();
            $this->actingAs($supervisor)->get(route('admin.'.$name))->assertForbidden();
        }

        foreach ([$admin, $supervisor] as $user) {
            $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.reports.export'))->assertForbidden();
        }
        $this->actingAs($supervisor)->post(route('admin.invoices.store'))->assertForbidden();
        $this->actingAs($supervisor)->post(route('admin.vendor-invoices.download'))->assertForbidden();
        $this->actingAs($supervisor)->post(route('admin.combined-invoices.download'))->assertForbidden();
        $this->actingAs($supervisor)->post(route('admin.incomes.store'))->assertForbidden();
        $this->actingAs($supervisor)->put(route('admin.attendances.update-schedule'))->assertForbidden();
        $customer = Customer::create(['name' => 'Customer', 'phone' => '08123456789', 'type' => 'individual']);
        $category = ServiceCategory::create(['name' => 'AC', 'is_active' => true]);
        $type = WorkOrderType::firstOrCreate(['code' => 'service'], ['name' => 'Servis', 'is_active' => true]);
        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id, 'service_category_id' => $category->id,
            'work_order_type_id' => $type->id, 'wo_number' => 'WO-TEST', 'title' => 'Servis',
            'location' => 'Jakarta', 'status' => 'pending', 'created_by' => $admin->id,
        ]);
        $this->actingAs($supervisor)->get(route('admin.work-orders.invoice-report-pdf', $workOrder))->assertForbidden();
        $this->actingAs($supervisor)->get(route('admin.work-orders.show', $workOrder))
            ->assertOk()->assertDontSee(route('admin.invoices.create', ['work_order_id' => $workOrder->id]));
        $technician = User::factory()->create(['role' => UserRole::Teknisi]);
        $this->actingAs($supervisor)->post(route('admin.work-orders.assign', $workOrder), [
            'technician_ids' => [$technician->id],
        ])->assertRedirect(route('admin.work-orders.show', $workOrder));
        $this->assertDatabaseHas('work_order_assignments', [
            'work_order_id' => $workOrder->id, 'technician_id' => $technician->id,
        ]);
    }

    public function test_supervisor_cannot_create_or_modify_super_admin(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $staff = User::factory()->create(['role' => UserRole::Teknisi]);
        $data = [
            'name' => 'Staff Baru',
            'email' => 'staff@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::SuperAdmin->value,
        ];

        $this->actingAs($supervisor)->get(route('admin.staff.create'))->assertDontSee('value="super_admin"', false);
        $this->actingAs($supervisor)->post(route('admin.staff.store'), $data)->assertSessionHasErrors('role');
        $this->actingAs($supervisor)->get(route('admin.staff.edit', $superAdmin))->assertForbidden();
        $this->actingAs($supervisor)->put(route('admin.staff.update', $superAdmin), $data)->assertForbidden();
        $this->actingAs($supervisor)->delete(route('admin.staff.destroy', $superAdmin))->assertForbidden();
        $this->actingAs($supervisor)->put(route('admin.staff.update', $staff), $data)->assertSessionHasErrors('role');
        $this->assertTrue($superAdmin->fresh()->is_active);
        $this->assertSame(UserRole::Teknisi, $staff->fresh()->role);

        $data['role'] = UserRole::Supervisor->value;
        $this->actingAs($supervisor)->post(route('admin.staff.store'), $data)->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseHas('users', ['email' => $data['email'], 'role' => 'supervisor']);
    }

    public function test_supervisor_cannot_gain_admin_access_through_staff_management(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $otherSupervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $data = [
            'name' => 'Staff',
            'email' => 'staff@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::Admin->value,
        ];

        $this->actingAs($supervisor)->get(route('admin.staff.index'))
            ->assertOk()->assertSee($admin->name)->assertDontSee(route('admin.staff.edit', $admin));
        $this->actingAs($supervisor)->get(route('admin.staff.create'))->assertDontSee('value="admin"', false);
        $this->actingAs($supervisor)->post(route('admin.staff.store'), $data)->assertSessionHasErrors('role');
        $this->actingAs($supervisor)->put(route('admin.staff.update', $supervisor), $data)->assertSessionHasErrors('role');
        $this->actingAs($supervisor)->put(route('admin.staff.update', $otherSupervisor), $data)->assertSessionHasErrors('role');
        $this->actingAs($supervisor)->get(route('admin.staff.edit', $admin))->assertForbidden();
        $this->actingAs($supervisor)->put(route('admin.staff.update', $admin), $data)->assertForbidden();
        $this->actingAs($supervisor)->delete(route('admin.staff.destroy', $admin))->assertForbidden();
        $this->assertSame(UserRole::Supervisor, $supervisor->fresh()->role);
        $this->assertSame(UserRole::Supervisor, $otherSupervisor->fresh()->role);
        $this->assertTrue($admin->fresh()->is_active);

        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($superAdmin)->put(route('admin.staff.update', $supervisor), $data)
            ->assertRedirect(route('admin.staff.index'));
        $this->assertSame(UserRole::Admin, $supervisor->fresh()->role);
    }

    public function test_admin_can_update_settings_and_supervisor_cannot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->actingAs($admin)->put(route('admin.settings.update'), ['company_name' => 'Azza Test'])
            ->assertRedirect(route('admin.settings.index'));
        $this->assertSame('Azza Test', Setting::get('company_name'));
        $this->actingAs($supervisor)->put(route('admin.settings.update'), ['company_name' => 'Tidak Diizinkan'])
            ->assertForbidden();
        $this->assertSame('Azza Test', Setting::get('company_name'));
    }

    public function test_deactivating_staff_revokes_mobile_tokens_and_blocks_existing_web_session(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $token = $supervisor->createToken('mobile-app')->accessToken;

        $this->actingAs($superAdmin)->delete(route('admin.staff.destroy', $supervisor))
            ->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
        $this->actingAs($supervisor->fresh())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_inactive_staff_cannot_use_an_existing_mobile_token(): void
    {
        $technician = User::factory()->create(['role' => UserRole::Teknisi]);
        $token = $technician->createToken('mobile-app')->plainTextToken;
        $technician->update(['is_active' => false]);

        $this->withToken($token)->getJson(route('api.me'))
            ->assertForbidden()->assertJsonPath('success', false);
    }

    public function test_supervisor_can_sync_finance(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        Artisan::shouldReceive('call')->once()->with('sync:finance-sheet', [])->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('OK');

        $this->actingAs($supervisor)->post(route('admin.google-sheet-sync.sync'), ['type' => 'finance'])
            ->assertRedirect(route('admin.google-sheet-sync.index'))->assertSessionHas('success');
    }

    public function test_sidebar_and_report_links_follow_permissions(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($supervisor)->get(route('admin.dashboard'))
            ->assertSee(route('admin.staff.index'))
            ->assertSee(route('admin.google-sheet-sync.index'))
            ->assertDontSee(route('admin.invoices.index'))
            ->assertDontSee(route('admin.settings.index'));
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee(route('admin.invoices.index'))
            ->assertSee(route('admin.settings.index'))
            ->assertDontSee(route('admin.staff.index'));
        $this->actingAs($admin)->get(route('admin.work-orders.index'))->assertDontSee(route('admin.reports.index'));
        $this->actingAs($supervisor)->get(route('admin.staff.index'))->assertDontSee(route('admin.reports.index'));
    }
}
