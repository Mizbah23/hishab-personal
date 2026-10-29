<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'voucher_number', 'type', 'voucher_date', 'amount', 'status',
        'narration', 'client_id', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'voucher_date' => 'date'];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
