<?php

namespace App\Actions;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabase
{
    public function handle(Tenant $tenant): void
    {
        if (! $tenant->database) {
            throw new \InvalidArgumentException('Tenant database not found.');
        }

        Config::set('database.connections.tenant', $this->tenantConnectionConfig($tenant));

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    public function landlord(): void
    {
        DB::purge('tenant');
        DB::setDefaultConnection('landlord');
        app()->forgetInstance('tenant');
    }

    /**
     * @return array<string, mixed>
     */
    protected function tenantConnectionConfig(Tenant $tenant): array
    {
        return [
            'driver' => 'mysql',
            'host' => $tenant->db_host ?: config('database.connections.landlord.host'),
            'port' => $tenant->db_port ?: config('database.connections.landlord.port'),
            'database' => $tenant->database,
            'username' => $tenant->db_username ?: config('database.connections.landlord.username'),
            'password' => $tenant->db_password ?: config('database.connections.landlord.password'),
            'unix_socket' => config('database.connections.landlord.unix_socket', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => config('database.connections.landlord.options', []),
        ];
    }
}
