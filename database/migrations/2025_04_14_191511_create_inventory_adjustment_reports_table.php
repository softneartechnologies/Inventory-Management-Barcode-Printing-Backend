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
        Schema::create('inventory_adjustment_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('current_stock')->default(0);
            $table->integer('new_stock')->default(0);
            $table->integer('quantity')->default(0);
            $table->string('unit_of_measure')->nullable();
            $table->text('reason_for_update')->nullable();
            $table->string('location_id')->nullable();
            $table->date('stock_date')->nullable();
            $table->string('vendor_id')->nullable();
            $table->string('category_id')->nullable();
            $table->string('adjustment')->nullable(); // Remove the underscore (_) to avoid errors
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_reports');
    }
};
