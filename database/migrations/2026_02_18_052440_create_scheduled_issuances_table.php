<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('issued_to');
            $table->date('scheduled_date');
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly'])->default('once');
            $table->unsignedTinyInteger('repeat_day_of_week')->nullable();
            $table->unsignedTinyInteger('repeat_day_of_month')->nullable();
            $table->date('repeat_until')->nullable();
            $table->enum('status', ['scheduled', 'processing', 'completed', 'cancelled'])->default('scheduled');
            $table->date('last_processed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_issuance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_issuance_id')
                  ->constrained('scheduled_issuances')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('size');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_issuance_items');
        Schema::dropIfExists('scheduled_issuances');
    }
};
