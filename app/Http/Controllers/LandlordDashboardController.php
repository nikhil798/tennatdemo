<?php

namespace App\Http\Controllers;

use App\Actions\SwitchDatabase;
use App\Models\GlobalSetting;
use App\Models\Plan;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Support\TenantEnvironment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class LandlordDashboardController extends Controller
{
    public function __invoke(
        Request $request,
        SwitchDatabase $switchDatabase,
        TenantEnvironment $tenantEnvironment,
    ): Response
    {
        $tenantSnapshots = Tenant::query()
            ->with(['subscriber', 'currentSubscription.plan'])
            ->latest()
            ->take(6)
            ->get();

        $tenantUserOverview = $tenantSnapshots
            ->map(function (Tenant $tenant) use ($switchDatabase, $tenantEnvironment) {
                $available = $tenantEnvironment->tenantDatabaseExists($tenant);
                $userCount = 0;
                $recentUsers = [];

                if ($available) {
                    $switchDatabase->handle($tenant);

                    if (Schema::connection('tenant')->hasTable('users')) {
                        $userCount = DB::connection('tenant')->table('users')->count();
                        $recentUsers = DB::connection('tenant')
                            ->table('users')
                            ->latest('id')
                            ->limit(3)
                            ->get(['name', 'email'])
                            ->map(fn ($user) => [
                                'name' => $user->name,
                                'email' => $user->email,
                            ])
                            ->values()
                            ->all();
                    }

                    $switchDatabase->landlord();
                }

                return [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'tenant_domain' => $tenant->domain,
                    'database' => $tenant->database,
                    'database_available' => $available,
                    'user_count' => $userCount,
                    'recent_users' => $recentUsers,
                ];
            })
            ->values();

        return Inertia::render('Landlord/Dashboard', [
            'landlordUser' => [
                'name' => $request->user('landlord')?->name,
                'email' => $request->user('landlord')?->email,
            ],
            'stats' => [
                'tenants' => Tenant::query()->count(),
                'plans' => Plan::query()->count(),
                'subscriptions' => Subscription::query()->count(),
                'subscribers' => Subscriber::query()->count(),
                'global_settings' => GlobalSetting::query()->count(),
                'tenant_users' => $tenantUserOverview->sum('user_count'),
            ],
            'tenants' => $tenantSnapshots
                ->map(fn (Tenant $tenant) => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domain' => $tenant->domain,
                    'database' => $tenant->database,
                    'status' => $tenant->status,
                    'subscriber' => $tenant->subscriber?->email,
                    'plan' => $tenant->currentSubscription?->plan?->name,
                ])
                ->values(),
            'plans' => Plan::query()
                ->orderBy('price')
                ->get()
                ->map(fn (Plan $plan) => [
                    'name' => $plan->name,
                    'code' => $plan->code,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'billing_cycle' => $plan->billing_cycle,
                    'trial_days' => $plan->trial_days,
                    'is_active' => $plan->is_active,
                ])
                ->values(),
            'settings' => GlobalSetting::query()
                ->where('autoload', true)
                ->latest()
                ->take(8)
                ->get(['key', 'value', 'type'])
                ->values(),
            'tenantUserOverview' => $tenantUserOverview,
        ]);
    }
}
