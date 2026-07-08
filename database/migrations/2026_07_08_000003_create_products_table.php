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
            $table->string('tenant_id');
            $table->string('sku');
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->decimal('elasticity', 5, 2)->default(-1.00); // Price elasticity index
            $table->integer('current_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->integer('reorder_qty')->default(0);
            $table->integer('lead_time_days')->default(3);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('wmape', 5, 2)->nullable(); // WMAPE forecasting accuracy (from MLflow)
            $table->timestamps();

            // Compound key check: SKU is unique within each tenant
            $table->unique(['tenant_id', 'sku']);

            // Foreign Key constraints
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
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
