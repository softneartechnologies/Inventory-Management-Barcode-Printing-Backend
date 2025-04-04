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
            $table->string('product_name');
            $table->string('sku')->unique();
            $table->string('generated_barcode')->nullable();
            $table->string('generated_qrcode')->nullable();
            $table->string('units');
            $table->string('category');
            $table->string('sub_category')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('vendor')->nullable();
            $table->string('model')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->nullable();
            $table->string('location_id')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->boolean('returnable')->default(false);
            $table->boolean('track_inventory')->default(false);
            $table->integer('opening_stock')->default(0);
            $table->decimal('selling_cost', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->boolean('commit_stock_check')->default(false);
            $table->string('project_name')->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->string('measurement_unit')->nullable();
            $table->string('barcode_number')->nullable();
            $table->integer('inventory_alert_threshold')->default(0);
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
