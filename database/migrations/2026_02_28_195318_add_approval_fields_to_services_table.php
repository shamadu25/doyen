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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('is_active');
            $table->boolean('show_on_website')->default(false)->after('is_approved');
            $table->text('website_description')->nullable()->after('show_on_website');
            $table->string('icon')->nullable()->after('website_description');
            $table->integer('sort_order')->default(0)->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'show_on_website', 'website_description', 'icon', 'sort_order']);
        });
    }
};
