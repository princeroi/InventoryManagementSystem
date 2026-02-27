<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Convert any invalid status to 'requested'
        DB::table('office_supply_requests')
            ->whereNotIn('status', ['requested', 'completed'])
            ->update(['status' => 'requested']);

        // 2️⃣ Modify ENUM column
        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->enum('status', ['requested', 'completed'])
                  ->default('requested')
                  ->change();
        });
    }

    public function down(): void
    {
        // If you rollback, allow both again (safe fallback)
        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->enum('status', ['requested', 'completed'])
                  ->default('requested')
                  ->change();
        });
    }
};