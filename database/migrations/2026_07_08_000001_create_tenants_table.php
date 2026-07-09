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
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g., 'tenant_001', 'greenvalley'
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('subscription_tier')->default('basic'); // basic, premium, enterprise
            $table->string('status')->default('active'); // active, suspended, trial
            $table->decimal('tax_rate', 5, 2)->default(8.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
