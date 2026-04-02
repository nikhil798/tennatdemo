<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use App\Support\TenantEnvironment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request, TenantEnvironment $tenantEnvironment): Response
    {
        $showTenantSelector = $tenantEnvironment->isCentralHost($request->getHost());
        $selectedTenantId = $request->integer('tenant_id') ?: $request->session()->get('tenant_id');
        $tenants = [];

        if ($showTenantSelector && ! app()->runningUnitTests()) {
            $tenants = Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name', 'domain', 'database', 'status'])
                ->map(fn (Tenant $tenant) => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domain' => $tenant->domain,
                    'database' => $tenant->database,
                    'status' => $tenant->status,
                    'available' => $tenantEnvironment->tenantDatabaseExists($tenant),
                ])
                ->values();
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'showTenantSelector' => $showTenantSelector,
            'selectedTenantId' => $selectedTenantId ? (int) $selectedTenantId : null,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('tenant_id', app('tenant')->id);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $tenantId = $request->session()->get('tenant_id');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($tenantId) {
            $request->session()->put('tenant_id', $tenantId);
        }

        return redirect()->route('login');
    }
}
