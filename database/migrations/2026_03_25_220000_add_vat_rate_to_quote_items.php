<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            // Per-item VAT rate (0 = exempt / zero-rated).  Defaults to 20% for existing rows.
            $table->decimal('vat_rate', 5, 2)->default(20.00)->after('total_price');
            // Explicit tax-exempt flag (when true, vat_rate should be 0 but stored separately for clarity)
            $table->boolean('tax_exempt')->default(false)->after('vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'tax_exempt']);
        });
    }
};
