<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class PosTransaction extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_transactions';
    
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Manually tracking transaction_date & created_at

    protected $fillable = [
        'id',
        'tenant_id',
        'product_id',
        'quantity',
        'price_sold',
        'transaction_date',
        'created_at',
    ];

    protected $casts = [
        'price_sold' => 'decimal:2',
        'quantity' => 'integer',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns this transaction.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product sold in this transaction.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
