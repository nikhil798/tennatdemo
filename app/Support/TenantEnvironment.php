<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantEnvironment
{
    public function isCentralHost(string $host): bool
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return in_array($host, array_filter([
            '127.0.0.1',
            'localhost',
            $appHost ?: null,
        ]), true);
    }

    public function tenantDatabaseExists(?Tenant $tenant): bool
    {
        if (! $tenant?->database || app()->runningUnitTests()) {
            return (bool) $tenant;
        }

        try {
            $connection = DB::connection('landlord');

            if ($connection->getDriverName() !== 'mysql') {
                return true;
            }

            return $connection->selectOne(
                'select schema_name from information_schema.schemata where schema_name = ? limit 1',
                [$tenant->database],
            ) !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
