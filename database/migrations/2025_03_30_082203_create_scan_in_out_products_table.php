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
        Schema::create('scan_in_out_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('issue_from_user_id')->default(0);
            $table->string('vendor_id')->default(0);
            $table->string('location_id')->default(0);
            $table->string('category_id')->default(0);
            $table->dateTime('in_out_date_time');
            $table->enum('type', ['in', 'out']);
            $table->enum('purpose', ['Repairs', 'Personal Use']);
            $table->string('department_id')->nullable();
            $table->string('work_station_id')->nullable();
            $table->string('machine_id')->nullable();
            $table->string('comments')->nullable();
            $table->integer('in_quantity')->default(0);
            $table->integer('out_quantity')->default(0);
            $table->string('previous_stock')->default(0);
            $table->string('total_current_stock')->default(0);
            $table->string('threshold')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_in_out_products');
    }
};
