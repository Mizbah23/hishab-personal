<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Voucher extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'voucher_number', 'type', 'voucher_date', 'amount', 'status',
        'narration', 'client_id', 'created_by', 'attachment_path', 'attachment_name',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'voucher_date' => 'date'];
    }

    public static function financialEntries(string $type, ?string $from = null, ?string $to = null): Collection
    {
        $model = $type === 'income' ? Income::class : Expense::class;
        $dateColumn = $type === 'income' ? 'received_on' : 'spent_on';
        $entries = $model::query()
            ->when($from, fn ($query) => $query->whereDate($dateColumn, '>=', $from))
            ->when($to, fn ($query) => $query->whereDate($dateColumn, '<=', $to))
            ->get();
        $vouchers = static::query()->where('status', 'posted')->where('type', $type)
            ->whereNotIn('id', Income::query()->whereNotNull('voucher_id')->select('voucher_id'))
            ->whereNotIn('id', Expense::query()->whereNotNull('voucher_id')->select('voucher_id'))
            ->when($from, fn ($query) => $query->whereDate('voucher_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('voucher_date', '<=', $to))
            ->get();

        return $entries->toBase()->concat($vouchers->map(fn (Voucher $voucher) => new $model([
            'amount' => $voucher->amount,
            $dateColumn => $voucher->voucher_date,
            'category' => 'Posted vouchers',
            'title' => $voucher->narration ?: $voucher->voucher_number,
        ])));
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function nextVoucherNumber(): string
    {
        $timestamp = now();

        do {
            $voucherNumber = $timestamp->format('YmdGi').$timestamp->second;
            $timestamp->addSecond();
        } while (static::query()->where('voucher_number', $voucherNumber)->exists());

        return $voucherNumber;
    }
}
