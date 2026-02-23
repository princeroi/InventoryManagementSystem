<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop issuance_recipient_items first (depends on issuance_recipients)
        Schema::dropIfExists('issuance_recipient_items');

        // Drop issuance_recipients
        Schema::dropIfExists('issuance_recipients');

        // Drop issuance_site pivot table
        Schema::dropIfExists('issuance_site');

        // Drop the issuance_type_id column from issuances
        Schema::table('issuances', function (Blueprint $table) {
            $table->dropForeign(['issuance_type_id']);
            $table->dropColumn('issuance_type_id');
        });
    }

    public function down(): void
    {
        // Restore issuance_type_id column
        Schema::table('issuances', function (Blueprint $table) {
            $table->foreignId('issuance_type_id')->nullable()->constrained()->nullOnDelete();
        });

        // Restore issuance_site
        Schema::create('issuance_site', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Restore issuance_recipients
        Schema::create('issuance_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // Restore issuance_recipient_items
        Schema::create('issuance_recipient_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_recipient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }
};