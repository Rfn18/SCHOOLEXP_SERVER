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
        Schema::create('feedback_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->char('fingerprint_hash', 64)->unique();
            $table->timestamp('last_submit_at');
            $table->unsignedInteger('submit_count')->default(1);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_rate_limits');
    }
};
