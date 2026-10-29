<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_has_read_access_but_cannot_manage_records(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.reports'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.incomes.index'))->assertOk()->assertDontSee('Record income');
        $this->actingAs($viewer)->get(route('admin.incomes.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('admin.expenses.store'), [
            'title' => 'Not allowed',
            'amount' => 10,
            'spent_on' => '2026-09-01',
        ])->assertForbidden();
    }

    public function test_accountant_can_manage_financial_modules_but_not_users(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($accountant)->get(route('admin.vouchers.create'))->assertOk();
        $this->actingAs($accountant)->get(route('admin.clients.create'))->assertOk();
        $this->actingAs($accountant)->get(route('admin.users.index'))->assertForbidden();
    }
}
