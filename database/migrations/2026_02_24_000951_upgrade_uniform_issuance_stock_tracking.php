<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uniform_issuance_items', function (Blueprint $table) {
            $table->unsignedInteger('released_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('remaining_quantity')->default(0)->after('released_quantity');
        });

        Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->date('partial_at')->nullable()->after('released_at');
        });
    }

    public function down(): void
    {
        Schema::table('uniform_issuance_items', function (Blueprint $table) {
            $table->dropColumn(['released_quantity', 'remaining_quantity']);
        });

        Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->dropColumn('partial_at');
        });
    }
};
