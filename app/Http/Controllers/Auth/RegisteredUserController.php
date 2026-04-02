<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SwitchDatabase;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, TenantService $tenantService): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $tenant = $tenantService->create([
            'name' => $request->name . '\'s workspace',
            'domain' => $this->generateTenantDomain($request->name),
            'email' => $request->email,
            'subscriber_name' => $request->name,
            'subscriber_email' => $request->email,
            'plan_code' => 'free',
        ]);

        app(SwitchDatabase::class)->handle($tenant);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        app()->instance('tenant', $tenant);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('tenant_id', $tenant->id);

        return to_route('dashboard', ['tenant_id' => $tenant->id]);
    }

    protected function generateTenantDomain(string $name): string
    {
        $base = Str::slug($name) ?: 'tenant';
        $domain = $base . '-' . Str::lower(Str::random(6)) . '.localhost';

        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $base . '-' . Str::lower(Str::random(6)) . '.localhost';
        }

        return $domain;
    }
}
