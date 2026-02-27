<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->string('status')->default('issued')->change();
        });

        // Update any existing rows that were saved as 'pending'
        DB::table('office_supply_requests')
            ->where('status', 'pending')
            ->update(['status' => 'issued']);
    }

    public function down(): void
    {
        Schema::table('office_supply_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};