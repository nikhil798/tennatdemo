<?php

namespace App\Services;

use App\Actions\SwitchDatabase;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        protected SwitchDatabase $switchDatabase,
    ) {
    }

    public function create(array $data): Tenant
    {
        $dbName = $data['database'] ?? 'tenant_' . Str::slug($data['name']) . '_' . Str::lower(Str::random(6));
        $landlordConnection = DB::connection('landlord');
        $subscriber = $this->resolveSubscriber($data);
        $plan = $this->resolvePlan($data);

        $landlordConnection->statement(sprintf(
            'CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '``', $dbName)
        ));

        $tenant = Tenant::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $this->generateUniqueSlug($data['name']),
            'domain' => $data['domain'],
            'database' => $dbName,
            'db_username' => $data['db_username'] ?? config('database.connections.landlord.username'),
            'db_password' => $data['db_password'] ?? config('database.connections.landlord.password'),
            'db_host' => $data['db_host'] ?? config('database.connections.landlord.host'),
            'db_port' => $data['db_port'] ?? config('database.connections.landlord.port'),
            'subscriber_id' => $subscriber?->id,
            'status' => $data['status'] ?? 'active',
            'settings' => $data['settings'] ?? null,
        ]);

        $this->switchDatabase->handle($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => '/database/migrations/tenant',
            '--realpath' => false,
            '--force' => true,
        ]);

        $this->switchDatabase->landlord();

        if ($plan) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'subscriber_id' => $subscriber?->id,
                'plan_id' => $plan->id,
                'status' => $data['subscription_status'] ?? 'active',
                'billing_cycle' => $data['billing_cycle'] ?? $plan->billing_cycle,
                'starts_at' => now(),
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'renews_at' => ($data['billing_cycle'] ?? $plan->billing_cycle) === 'yearly'
                    ? now()->addYear()
                    : now()->addMonth(),
                'seats' => $data['seats'] ?? 1,
                'amount' => $data['amount'] ?? $plan->price,
                'currency' => $data['currency'] ?? $plan->currency,
                'meta' => $data['subscription_meta'] ?? null,
            ]);
        }

        return $tenant->fresh(['subscriber', 'currentSubscription.plan']);
    }

    protected function resolveSubscriber(array $data): ?Subscriber
    {
        $email = $data['subscriber_email'] ?? $data['email'] ?? null;

        if (! $email) {
            return null;
        }

        return Subscriber::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $data['subscriber_name'] ?? $data['name'],
                'phone' => $data['subscriber_phone'] ?? null,
                'company' => $data['subscriber_company'] ?? null,
                'meta' => $data['subscriber_meta'] ?? null,
            ],
        );
    }

    protected function resolvePlan(array $data): ?Plan
    {
        if (isset($data['plan_id'])) {
            return Plan::query()->find($data['plan_id']);
        }

        if (isset($data['plan_code'])) {
            return Plan::query()->where('code', $data['plan_code'])->first();
        }

        return Plan::query()->firstOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Free',
                'description' => 'Default free tenant plan',
                'price' => 0,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 0,
                'features' => [
                    'single_workspace' => true,
                    'database_isolation' => true,
                ],
                'is_active' => true,
            ],
        );
    }

    protected function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tenant';
        $slug = $base;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . Str::lower(Str::random(6));
        }

        return $slug;
    }
}
