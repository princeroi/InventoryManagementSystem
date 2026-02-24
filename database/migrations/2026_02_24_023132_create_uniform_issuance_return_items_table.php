<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniform_issuance_return_items', function (Blueprint $table) {
            $table->id();

            // Manual FK (short name)
            $table->unsignedBigInteger('uniform_issuance_recipient_id');
            $table->foreign(
                'uniform_issuance_recipient_id',
                'uir_items_recipient_fk'
            )->references('id')
              ->on('uniform_issuance_recipients')
              ->onDelete('cascade');

            // Item FK (short name)
            $table->unsignedBigInteger('item_id');
            $table->foreign(
                'item_id',
                'uir_items_item_fk'
            )->references('id')
              ->on('items')
              ->onDelete('cascade');

            $table->string('size')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('returned_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniform_issuance_return_items');
    }
};