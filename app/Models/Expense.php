<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'title', 'amount', 'spent_on', 'category', 'payment_method', 'notes',
        'voucher_id', 'created_by', 'attachment_path', 'attachment_name',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'spent_on' => 'date'];
    }
}
