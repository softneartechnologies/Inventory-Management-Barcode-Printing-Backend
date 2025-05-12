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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('location_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->string('vendor_id')->nullable();
            $table->string('category_id')->nullable();
            $table->date('stock_date')->nullable();
            $table->integer('current_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->integer('quantity')->default(0);
            $table->string('unit_of_measure')->nullable();
            $table->string('per_unit_cost')->nullable();
            $table->string('total_cost')->nullable();
            $table->text('reason_for_update')->nullable();
            $table->string('adjustment')->nullable(); // Remove the underscore (_) to avoid errors
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
