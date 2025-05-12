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
        Schema::create('uom_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uom_category_id')->constrained()->onDelete('cascade');
            $table->string('unit_name'); // e.g., Meter, Kilogram
            $table->string('abbreviation'); // e.g., m, kg
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_units');
    }
};
