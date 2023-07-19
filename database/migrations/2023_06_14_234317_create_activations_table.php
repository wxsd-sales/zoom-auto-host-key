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
        Schema::create('activations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('wbx_wi_org_id');
            $table->string('wbx_wi_manifest_id');
            $table->integer('wbx_wi_manifest_version');
            $table->string('wbx_wi_manifest_url');
            $table->string('wbx_wi_app_url');
            $table->string('wbx_wi_display_name');
            $table->string('wbx_wi_client_id')->unique();
            $table->string('wbx_wi_client_secret');
            $table->string('zm_s2s_account_id');
            $table->string('zm_s2s_client_id')->unique();
            $table->string('zm_s2s_client_secret');
            $table->json('zm_host_accounts')->nullable();
            $table->string('hmac_secret');

            $table->foreignUuid('wbx_wi_oauth_id')->nullable();
            $table->foreignUuid('zm_s2s_oauth_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activations');
    }
};
