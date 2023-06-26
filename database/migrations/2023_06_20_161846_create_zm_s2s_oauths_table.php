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
        Schema::create('zm_s2s_oauths', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('activation_id')
                ->after('id')->index()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->text('account_id');
            $table->text('refresh_token')->nullable();
            $table->text('access_token');
            $table->timestamp('expires_at');
            $table->string('token_type');
            $table->string('scope');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_server_to_server_oauths');
    }
};
