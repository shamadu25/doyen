<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_content', function (Blueprint $table) {
            $table->id();
            $table->string('section');       // hero, testimonials, process, hours, seo, social
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, json, url, boolean
            $table->timestamps();

            $table->unique(['section', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_content');
    }
};
