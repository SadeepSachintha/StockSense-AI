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
        Schema::create('register_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_float', 10, 2);
            $table->decimal('actual_cash', 10, 2)->nullable();
            $table->decimal('cash_sales', 10, 2)->default(0.00);
            $table->decimal('card_sales', 10, 2)->default(0.00);
            $table->decimal('qr_sales', 10, 2)->default(0.00);
            $table->string('status')->default('open');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_shifts');
    }
};
