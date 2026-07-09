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
        if (!Schema::hasColumn('tenants', 'tax_rate')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 2)->default(8.00);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'tax_rate')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('tax_rate');
            });
        }
    }
};
