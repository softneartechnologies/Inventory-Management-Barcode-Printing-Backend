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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail')->nullable();
            $table->string('product_name');
            $table->string('sku')->unique();
            $table->string('generated_barcode')->nullable();
            $table->string('generated_qrcode')->nullable();
            $table->string('barcode_number')->nullable();
            $table->string('category_id');
            $table->string('sub_category_id')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('vendor_id')->nullable();
            $table->string('model')->nullable();
            $table->string('unit_of_measurement_category');
            $table->text('description')->nullable();
            $table->boolean('returnable')->default(false);
            $table->boolean('commit_stock_check')->default(false);
            $table->integer('inventory_alert_threshold')->default(0);
            $table->string('location_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('per_unit_cost')->nullable();
            $table->string('total_cost')->nullable();
            $table->integer('opening_stock')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
