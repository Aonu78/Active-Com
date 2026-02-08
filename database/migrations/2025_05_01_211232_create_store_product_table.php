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
        Schema::create('store_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');        // Store owner
            $table->unsignedBigInteger('product_id');     // Master product ID
            $table->decimal('price', 10, 2)->nullable();  // Store-specific price
            $table->integer('stock')->nullable();         // Store-specific stock
            $table->boolean('is_active')->default(true);  // Optional toggle
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_product');
    }
};
