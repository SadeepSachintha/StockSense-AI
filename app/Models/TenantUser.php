<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class TenantUser extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_users';

    protected $fillable = [
        'tenant_id',
        'username',
        'password',
        'role',
        'name',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the tenant that owns this user profile.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
