<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_filter_a_voucher_with_an_automatic_timestamp_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['name' => 'Northstar Labs', 'is_active' => true]);
        $this->travelTo('2026-11-05 07:00:01');

        $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'voucher_number' => 'MANUAL-NUMBER-IS-IGNORED',
            'type' => 'income',
            'voucher_date' => '2026-09-01',
            'amount' => '125000.50',
            'status' => 'posted',
            'narration' => 'September retainer',
            'client_id' => $client->id,
        ])->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseHas('vouchers', [
            'voucher_number' => '202611057001',
            'type' => 'income',
            'amount' => 125000.50,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('admin.vouchers.index', ['status' => 'posted']))
            ->assertOk()
            ->assertSee('202611057001')
            ->assertSee('September retainer');
    }

    public function test_voucher_requires_valid_values(): void
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
            'type' => 'unknown',
            'voucher_date' => 'invalid',
            'amount' => 0,
            'status' => 'unknown',
        ])->assertSessionHasErrors(['type', 'voucher_date', 'amount', 'status']);
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
            'type' => 'expense',
            'voucher_date' => '2026-08-15',
            'amount' => 250,
            'status' => 'posted',
        ])->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'voucher_number' => 'VCH-OLD', 'status' => 'posted']);

        $this->actingAs($admin)->delete(route('admin.vouchers.destroy', $voucher))
            ->assertRedirect(route('admin.vouchers.index'));

        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_voucher_accepts_a_two_mb_attachment_and_provides_a_protected_download(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'type' => 'income',
            'voucher_date' => '2026-11-05',
            'amount' => '5000.00',
            'status' => 'posted',
            'attachment' => UploadedFile::fake()->create('voucher.pdf', 2048, 'application/pdf'),
        ])->assertRedirect(route('admin.vouchers.index'));

        $voucher = Voucher::query()->latest()->firstOrFail();

        Storage::disk('local')->assertExists($voucher->attachment_path);
        $this->assertSame('voucher.pdf', $voucher->attachment_name);

        $this->actingAs($admin)->get(route('admin.vouchers.index'))
            ->assertSee('Attachment');

        $this->actingAs($admin)->get(route('admin.vouchers.attachment', $voucher))
            ->assertDownload('voucher.pdf');
    }

    public function test_voucher_rejects_attachments_over_two_mb(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'type' => 'expense',
            'voucher_date' => '2026-11-05',
            'amount' => '5000.00',
            'status' => 'draft',
            'attachment' => UploadedFile::fake()->create('too-large.pdf', 2049, 'application/pdf'),
        ])->assertSessionHasErrors('attachment');
    }
}
