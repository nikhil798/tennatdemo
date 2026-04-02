<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_saml_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');

            $table->string('idp_entity_id')->nullable();
            $table->text('idp_sso_url')->nullable();
            $table->text('idp_x509_cert')->nullable();

            $table->string('sp_entity_id')->nullable();
            $table->string('sp_acs')->nullable();
            $table->string('sp_sls')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_saml_configs');
    }
};
