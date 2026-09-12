<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFinancialAccountFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_with_all_accounts(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $accountA = FinancialAccount::create([
            'name' => 'BCA Operasional',
            'code' => 'BCA-01',
            'is_active' => true,
        ]);

        $accountB = FinancialAccount::create([
            'name' => 'Mandiri Kas',
            'code' => 'MDR-01',
            'is_active' => true,
        ]);

        $category = FinancialCategory::create([
            'name' => 'Pendapatan Jasa',
            'type' => TransactionType::Income,
            'is_active' => true,
        ]);

        FinancialTransaction::create([
            'type' => TransactionType::Income,
            'category_id' => $category->id,
            'financial_account_id' => $accountA->id,
            'amount' => 1500000,
            'transaction_date' => now(),
            'recorded_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'type' => TransactionType::Income,
            'category_id' => $category->id,
            'financial_account_id' => $accountB->id,
            'amount' => 2500000,
            'transaction_date' => now(),
            'recorded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Semua Akun Keuangan');
        $response->assertSee('BCA Operasional');
        $response->assertSee('Mandiri Kas');
        // Total should be 1.500.000 + 2.500.000 = 4.000.000
        $response->assertSee('Rp 4.000.000');
    }

    public function test_admin_can_filter_dashboard_by_specific_financial_account(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $accountA = FinancialAccount::create([
            'name' => 'BCA Operasional',
            'code' => 'BCA-01',
            'is_active' => true,
        ]);

        $accountB = FinancialAccount::create([
            'name' => 'Mandiri Kas',
            'code' => 'MDR-01',
            'is_active' => true,
        ]);

        $category = FinancialCategory::create([
            'name' => 'Pendapatan Jasa',
            'type' => TransactionType::Income,
            'is_active' => true,
        ]);

        FinancialTransaction::create([
            'type' => TransactionType::Income,
            'category_id' => $category->id,
            'financial_account_id' => $accountA->id,
            'amount' => 1500000,
            'transaction_date' => now(),
            'recorded_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'type' => TransactionType::Income,
            'category_id' => $category->id,
            'financial_account_id' => $accountB->id,
            'amount' => 2500000,
            'transaction_date' => now(),
            'recorded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'financial_account_id' => $accountA->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('BCA Operasional');
        $response->assertSee('Rp 1.500.000');
        $response->assertDontSee('Rp 4.000.000');
    }
}
