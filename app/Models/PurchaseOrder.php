<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class PurchaseOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'supplier_id',
        'quantity',
        'status',
        'message_body',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'product_id' => 'integer',
        'supplier_id' => 'integer',
    ];

    /**
     * Get the tenant that owns this purchase order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product being ordered.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier who fulfills this order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
