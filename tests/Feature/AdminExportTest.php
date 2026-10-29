<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_can_download_csv_exports(): void
    {
        $accountant = User::factory()->create(['role' => 'accountant']);
        Income::create(['title' => 'CSV income', 'amount' => 100, 'received_on' => '2026-09-01']);

        $response = $this->actingAs($accountant)->get(route('admin.exports.incomes'));

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV income', $response->streamedContent());
    }

    public function test_administrator_can_download_a_password_free_data_backup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'Backup income', 'amount' => 250, 'received_on' => '2026-09-01']);

        $response = $this->actingAs($admin)->get(route('admin.backups.download'));

        $response->assertOk()->assertHeader('content-type', 'application/json');
        $backup = json_decode($response->streamedContent(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Backup income', $backup['incomes'][0]['title']);
        $this->assertArrayNotHasKey('password', $backup['users'][0]);
    }

    public function test_export_and_backup_access_follow_role_permissions(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($viewer)->get(route('admin.exports.incomes'))->assertForbidden();
        $this->actingAs($accountant)->get(route('admin.backups.index'))->assertForbidden();
    }
}
