<?php
// database/migrations/xxxx_create_uniform_issuance_billings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniform_issuance_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uniform_issuance_id')
                  ->constrained()->cascadeOnDelete();
            $table->foreignId('uniform_issuance_recipient_id')
                  ->constrained()->cascadeOnDelete();
            $table->string('employee_name');
            $table->string('employee_status');   // posted | reliever
            $table->string('issuance_type');     // New Hire | Salary Deduct | Annual | Additional
            $table->string('bill_status')->default('pending'); // pending | billed
            $table->timestamp('endorsed_at')->nullable();
            $table->timestamp('billed_at')->nullable();
            $table->string('endorsed_by')->nullable();
            $table->string('billed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniform_issuance_billings');
    }
};