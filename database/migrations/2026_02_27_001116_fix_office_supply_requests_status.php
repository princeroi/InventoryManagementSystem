<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sanitize any bad data before altering the column
        DB::table('office_supply_requests')
            ->whereNotIn('status', ['requested', 'done'])
            ->update(['status' => 'requested']);

        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->enum('status', ['requested', 'done'])
                  ->default('requested')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->enum('status', ['requested', 'done'])
                  ->default('requested')
                  ->change();
        });
    }
};