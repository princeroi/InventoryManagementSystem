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
        Schema::create('uniform_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issuance_type_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->date('pending_at')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('cancelled_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('uniform_issuance_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uniform_issuance_id')->constrained()->cascadeOnDelete();
            $table->string('employee_name');
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uniform_set_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode')->default('auto'); // 'auto' or 'manual'
            $table->timestamps();
        });

        Schema::create('uniform_issuance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uniform_issuance_recipient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('uniform_issuance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uniform_issuance_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('performed_by');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uniform_issuance_logs');
        Schema::dropIfExists('uniform_issuance_items');
        Schema::dropIfExists('uniform_issuance_recipients');
        Schema::dropIfExists('uniform_issuances');
    }
};
