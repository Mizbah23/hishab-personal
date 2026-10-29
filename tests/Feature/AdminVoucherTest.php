<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_filter_a_voucher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['name' => 'Northstar Labs', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'voucher_number' => 'VCH-2026-0001',
            'type' => 'income',
            'voucher_date' => '2026-09-01',
            'amount' => '125000.50',
            'status' => 'posted',
            'narration' => 'September retainer',
            'client_id' => $client->id,
        ])->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseHas('vouchers', [
            'voucher_number' => 'VCH-2026-0001',
            'type' => 'income',
            'amount' => 125000.50,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('admin.vouchers.index', ['status' => 'posted']))
            ->assertOk()
            ->assertSee('VCH-2026-0001')
            ->assertSee('September retainer');
    }

    public function test_voucher_requires_a_unique_number_and_valid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Voucher::create([
            'voucher_number' => 'VCH-EXISTING',
            'type' => 'general',
            'voucher_date' => '2026-09-01',
            'amount' => 10,
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'voucher_number' => 'VCH-EXISTING',
            'type' => 'unknown',
            'voucher_date' => 'invalid',
            'amount' => 0,
            'status' => 'unknown',
        ])->assertSessionHasErrors(['voucher_number', 'type', 'voucher_date', 'amount', 'status']);
    }

    public function test_admin_can_update_and_remove_a_voucher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $voucher = Voucher::create([
            'voucher_number' => 'VCH-OLD',
            'type' => 'general',
            'voucher_date' => '2026-08-01',
            'amount' => 100,
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->put(route('admin.vouchers.update', $voucher), [
            'voucher_number' => 'VCH-UPDATED',
            'type' => 'expense',
            'voucher_date' => '2026-08-15',
            'amount' => 250,
            'status' => 'posted',
        ])->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'voucher_number' => 'VCH-UPDATED', 'status' => 'posted']);

        $this->actingAs($admin)->delete(route('admin.vouchers.destroy', $voucher))
            ->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }
}
