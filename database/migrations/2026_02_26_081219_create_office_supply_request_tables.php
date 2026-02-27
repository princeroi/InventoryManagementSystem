<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── officesupplyrequest ──────────────────────────────────
        Schema::create('office_supply_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->date('request_date');
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending | approved | fulfilled | rejected
            $table->timestamps();
        });

        // ── officesupplyrequestitem ──────────────────────────────
        Schema::create('office_supply_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_supply_request_id')
                  ->constrained('office_supply_requests')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('size_label')->nullable(); // denormalized for history
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_supply_request_items');
        Schema::dropIfExists('office_supply_requests');
    }
};