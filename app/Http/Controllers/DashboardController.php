<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $tenant = app('tenant')->loadMissing(['subscriber', 'currentSubscription.plan']);
        $subscription = $tenant->currentSubscription;
        $plan = $subscription?->plan;
        $hasEmployeesTable = Schema::connection('tenant')->hasTable('employees');
        $employees = $hasEmployeesTable
            ? Employee::query()->latest()->take(5)->get()
            : collect();

        return Inertia::render('Dashboard', [
            'name' => $request->user()?->name,
            'tenant' => [
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'database' => $tenant->database,
                'status' => $tenant->status,
            ],
            'subscriber' => $tenant->subscriber ? [
                'name' => $tenant->subscriber->name,
                'email' => $tenant->subscriber->email,
                'company' => $tenant->subscriber->company,
            ] : null,
            'subscription' => $subscription ? [
                'status' => $subscription->status,
                'billing_cycle' => $subscription->billing_cycle,
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
                'starts_at' => optional($subscription->starts_at)->toDateTimeString(),
                'trial_ends_at' => optional($subscription->trial_ends_at)->toDateTimeString(),
                'renews_at' => optional($subscription->renews_at)->toDateTimeString(),
            ] : null,
            'plan' => $plan ? [
                'name' => $plan->name,
                'code' => $plan->code,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'billing_cycle' => $plan->billing_cycle,
                'features' => $plan->features ?? [],
            ] : null,
            'globalConfigCount' => GlobalSetting::query()->count(),
            'employeeSummary' => [
                'total' => $hasEmployeesTable ? Employee::query()->count() : 0,
                'active' => $hasEmployeesTable ? Employee::query()->where('status', 'active')->count() : 0,
                'recent' => $employees->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'designation' => $employee->designation,
                    'department' => $employee->department,
                    'status' => $employee->status,
                ])->values(),
            ],
        ]);
    }
}
