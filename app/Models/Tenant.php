<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    // String primary key (non-incrementing)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'company_name',
        'subscription_tier',
        'status',
        'tax_rate',
    ];

    /**
     * Get suppliers scoped under this tenant.
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Get products owned by this tenant.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get POS transactions recorded by this tenant.
     */
    public function posTransactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class);
    }
}
