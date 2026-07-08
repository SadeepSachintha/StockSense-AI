<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price_sold', 10, 2);
            $table->timestamp('transaction_date');
            $table->timestamp('created_at')->useCurrent();

            // Composite primary key including partition time dimension (required for hypertable)
            $table->primary(['id', 'transaction_date']);

            // Indexing for faster aggregation queries
            $table->index(['tenant_id', 'product_id', 'transaction_date']);
        });

        // Add foreign key constraint separately to avoid interference with hypertable initialization
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // TimescaleDB hypertable setup (conditional on PostgreSQL driver with fallback safety)
        if (DB::getDriverName() === 'pgsql') {
            try {
                DB::statement('CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;');
                
                // Convert standard table into a TimescaleDB hypertable partitioned by transaction_date
                DB::statement("SELECT create_hypertable('pos_transactions', 'transaction_date', if_not_exists => TRUE);");
            } catch (\Exception $e) {
                // Suppress failure and fallback to normal Postgres tables if extension is missing (e.g. on Neon)
                \Illuminate\Support\Facades\Log::warning('TimescaleDB extension not available or hypertable creation failed. Falling back to standard PostgreSQL table. Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_transactions');
    }
};
