<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_accepts_an_attachment_and_provides_a_protected_download(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.incomes.store'), [
            'title' => 'Design payment',
            'amount' => 5000,
            'received_on' => '2026-09-01',
            'attachment' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('admin.incomes.index'));

        $income = Income::firstOrFail();
        Storage::disk('local')->assertExists($income->attachment_path);

        $this->actingAs($admin)->get(route('admin.incomes.attachment', $income))
            ->assertDownload('invoice.pdf');
    }

    public function test_expense_accepts_an_image_attachment(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'title' => 'Office supplies',
            'amount' => 1250,
            'spent_on' => '2026-09-01',
            'attachment' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::firstOrFail();
        Storage::disk('local')->assertExists($expense->attachment_path);
        $this->assertSame('receipt.jpg', $expense->attachment_name);
    }

    public function test_attachments_over_five_mb_are_rejected(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.incomes.store'), [
            'title' => 'Large invoice',
            'amount' => 5000,
            'received_on' => '2026-09-01',
            'attachment' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'),
        ])->assertSessionHasErrors('attachment');

        $this->assertDatabaseCount('incomes', 0);
    }
}
