<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => fn () => $request->user('landlord') ?? (app()->bound('tenant') ? $request->user() : null),
                'guard' => fn () => $request->user('landlord')
                    ? 'landlord'
                    : ((app()->bound('tenant') && $request->user()) ? 'tenant' : null),
            ],
            'tenantContext' => fn () => app()->bound('tenant') ? [
                'id' => app('tenant')->id,
                'name' => app('tenant')->name,
                'domain' => app('tenant')->domain,
                'database' => app('tenant')->database,
                'status' => app('tenant')->status,
            ] : null,
        ]);
    }
}
