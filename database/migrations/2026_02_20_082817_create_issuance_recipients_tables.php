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
        Schema::create('issuance_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // the "issued_to" person
            $table->timestamps();
        });

        Schema::create('issuance_recipient_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_recipient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuance_recipients');
    }
};
