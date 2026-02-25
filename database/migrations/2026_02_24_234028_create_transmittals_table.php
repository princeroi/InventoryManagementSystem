<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transmittals', function (Blueprint $table) {
            $table->id();

            $table->string('transmittal_number')->unique(); // HR-YYYYMMDD-0001

            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->cascadeOnDelete();

            $table->string('transmitted_by');               // name snapshot
            $table->foreignId('transmitted_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('transmitted_to');               // free-text

            $table->json('items_summary')->nullable();      // [{ item_name, size, quantity }]

            $table->timestamps();
        });

        Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->boolean('is_for_transmit')->default(false)->after('cancelled_at');
            $table->string('transmitted_to')->nullable()->after('is_for_transmit');
            $table->foreignId('transmittal_id')
                  ->nullable()
                  ->constrained('transmittals')
                  ->nullOnDelete()
                  ->after('transmitted_to');
        });
    }

    public function down(): void
    {
        Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->dropForeign(['transmittal_id']);
            $table->dropColumn(['is_for_transmit', 'transmitted_to', 'transmittal_id']);
        });

        Schema::dropIfExists('transmittals');
    }
};