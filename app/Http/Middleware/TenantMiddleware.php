<?php

namespace App\Http\Middleware;

use App\Actions\SwitchDatabase;
use App\Models\Tenant;
use App\Support\TenantEnvironment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(
        protected TenantEnvironment $tenantEnvironment,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $mode = 'required'): Response
    {
        $tenant = $this->resolveTenant($request);

        if (! $tenant && $mode === 'optional') {
            return $next($request);
        }

        if (! $tenant) {
            abort(404, 'Tenant not found for this request.');
        }

        if (! $this->tenantEnvironment->tenantDatabaseExists($tenant)) {
            if ((int) $request->session()->get('tenant_id') === $tenant->id) {
                $request->session()->forget('tenant_id');
            }

            if ($mode === 'optional' && $this->isCentralHost($request)) {
                return $next($request);
            }

            if ($this->isCentralHost($request)) {
                throw ValidationException::withMessages([
                    'tenant_id' => 'The selected workspace database is missing. Create it again or choose a working workspace.',
                ]);
            }

            abort(404, 'Tenant database not found for this request.');
        }

        app(SwitchDatabase::class)->handle($tenant);
        app()->instance('tenant', $tenant);

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();

        if ($this->isCentralHost($request)) {
            $requestedTenantId = $request->integer('tenant_id');

            if ($requestedTenantId > 0) {
                return Tenant::find($requestedTenantId);
            }

            $tenantId = $request->session()->get('tenant_id');

            if ($tenantId) {
                return Tenant::find($tenantId);
            }

            return null;
        }

        return Tenant::where('domain', $host)->first();
    }

    protected function isCentralHost(Request $request): bool
    {
        return $this->tenantEnvironment->isCentralHost($request->getHost());
    }
}
