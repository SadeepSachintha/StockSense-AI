<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class RegisterShift extends Model
{
    use BelongsToTenant;

    protected $table = 'register_shifts';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'opened_at',
        'closed_at',
        'opening_float',
        'actual_cash',
        'cash_sales',
        'card_sales',
        'qr_sales',
        'status',
    ];

    protected $casts = [
        'opening_float' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'qr_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns this shift.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get transactions in this shift.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class, 'shift_id', 'id');
    }
}
