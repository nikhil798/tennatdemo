<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSamlConfig extends LandlordModel
{
    protected $table = 'tenant_saml_configs';

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'login_url',
        'logout_url',
        'certificate',
        'metadata_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
