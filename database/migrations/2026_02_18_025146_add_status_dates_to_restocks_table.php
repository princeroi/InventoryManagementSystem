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
        Schema::table('restocks', function (Blueprint $table) {
            $table->date('delivered_at')->nullable()->after('status');
            $table->date('partial_at')->nullable()->after('delivered_at');
            $table->date('returned_at')->nullable()->after('partial_at');
            $table->date('cancelled_at')->nullable()->after('returned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restocks', function (Blueprint $table) {
            $table->dropColumn([
                'delivered_at',
                'partial_at',
                'returned_at',
                'cancelled_at',
            ]);
        });
    }
};
