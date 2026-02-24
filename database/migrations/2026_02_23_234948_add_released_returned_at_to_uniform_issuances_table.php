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
        Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->date('released_at')->nullable()->after('pending_at');
            $table->date('returned_at')->nullable()->after('issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('uniform_issuances', function (Blueprint $table) {
            $table->dropColumn(['released_at', 'returned_at']);
        });
    }
};
