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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->index()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->enum('type', ['oidc', 'oauth', 'email', 'credentials']);
            $table->string('provider');
            $table->string('provider_account_id');
            $table->text('refresh_token')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('token_type')->nullable();
            $table->string('scope')->nullable();
            $table->string('id_token')->nullable();
            $table->string('session_state')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
