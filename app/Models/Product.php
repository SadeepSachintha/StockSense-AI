<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'category',
        'price',
        'elasticity',
        'current_stock',
        'safety_stock',
        'max_stock',
        'reorder_qty',
        'lead_time_days',
        'supplier_id',
        'wmape',
        'cost_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'elasticity' => 'decimal:2',
        'wmape' => 'decimal:2',
        'current_stock' => 'integer',
        'safety_stock' => 'integer',
        'max_stock' => 'integer',
        'reorder_qty' => 'integer',
        'lead_time_days' => 'integer',
    ];

    /**
     * Get the tenant that owns this product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the supplier for this product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get historical POS transactions for this product.
     */
    public function posTransactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class);
    }
}
