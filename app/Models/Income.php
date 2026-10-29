<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'title', 'amount', 'received_on', 'category', 'payment_method', 'notes',
        'client_id', 'voucher_id', 'created_by', 'attachment_path', 'attachment_name',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'received_on' => 'date'];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
