<?php
// database/migrations/xxxx_xx_xx_add_employee_status_to_uniform_issuance_recipients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uniform_issuance_recipients', function (Blueprint $table) {
            $table->string('employee_status')->default('posted')->after('mode');
            // 'posted' | 'reliever'
        });
    }

    public function down(): void
    {
        Schema::table('uniform_issuance_recipients', function (Blueprint $table) {
            $table->dropColumn('employee_status');
        });
    }
};