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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('sku');
            $table->integer('current_stock');
            $table->integer('threshold_count');
            $table->string('location_id');
            $table->string('quantity');
            $table->string('category_id');
            $table->string('total_current_stock');
            $table->string('order_by');
            $table->string('status');
            $table->string('deleted')->default(0);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
