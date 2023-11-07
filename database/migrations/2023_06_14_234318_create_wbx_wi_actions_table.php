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
        Schema::create('wbx_wi_actions', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('activation_id')->index()
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('sub');
            $table->timestamp('iat');
            $table->string('jti')->unique();
            $table->string('action');
            $table->text('jwt');
            $table->json('jwt_payload')->unique();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wbx_wi_actions');
    }
};
