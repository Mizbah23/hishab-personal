<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'address', 'notes', 'is_active', 'url', 'renewal_date', 'renewal_amount',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'renewal_date' => 'date', 'renewal_amount' => 'decimal:2'];
    }
}
