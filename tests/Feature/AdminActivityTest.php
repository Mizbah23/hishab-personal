<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_changes_are_logged_with_the_current_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $income = Income::create([
            'title' => 'First payment',
            'amount' => 1000,
            'received_on' => '2026-09-01',
        ]);
        $income->update(['title' => 'Updated payment']);
        $income->delete();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'created',
            'auditable_type' => Income::class,
            'auditable_id' => $income->id,
        ]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'updated', 'auditable_id' => $income->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'deleted', 'auditable_id' => $income->id]);
        $this->assertSame(3, ActivityLog::count());
    }

    public function test_admin_can_filter_activity_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        Income::create(['title' => 'Filtered payment', 'amount' => 100, 'received_on' => '2026-09-01']);

        $this->get(route('admin.activity.index', ['module' => 'income', 'action' => 'created']))
            ->assertOk()
            ->assertSee('Filtered payment')
            ->assertSee('Created');
    }

    public function test_non_admin_users_cannot_view_activity_history(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get(route('admin.activity.index'))->assertForbidden();
    }
}
