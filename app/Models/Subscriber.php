<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends LandlordModel
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
