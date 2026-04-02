<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends LandlordModel
{
    protected $fillable = [
        'tenant_id',
        'subscriber_id',
        'plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'trial_ends_at',
        'ends_at',
        'renews_at',
        'seats',
        'amount',
        'currency',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'seats' => 'integer',
            'amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
