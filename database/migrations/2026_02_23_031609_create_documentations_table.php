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
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('width')->nullable();;
            $table->unsignedInteger('height')->nullable();;
            $table->foreignId('gallery_id')
                ->constrained('doc_galleries')
                ->onDelete('cascade');
            $table->integer('soft_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
