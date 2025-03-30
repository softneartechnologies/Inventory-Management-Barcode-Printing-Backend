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
        Schema::create('barcode_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('sku')->default(false);
            $table->boolean('product_name')->default(false);
            $table->boolean('description')->default(false);
            $table->boolean('units')->default(false);
            $table->boolean('category')->default(false);
            $table->boolean('sub_category')->default(false);
            $table->boolean('manufacturer')->default(false);
            $table->boolean('vendor')->default(false);
            $table->boolean('model')->default(false);
            $table->boolean('returnable')->default(false);
            $table->boolean('cost_price')->default(false);
            $table->boolean('selling_cost')->default(false);
            $table->boolean('weight')->default(false);
            $table->boolean('weight_unit')->default(false);
            $table->boolean('length')->default(false);
            $table->boolean('width')->default(false);
            $table->boolean('depth')->default(false);
            $table->boolean('measurement_unit')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_settings');
    }
};
