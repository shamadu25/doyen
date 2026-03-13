<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_conversations')) {
            Schema::create('whatsapp_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('customer_phone', 30);
                $table->string('customer_name', 150)->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
                $table->integer('unread_count')->default(0);
                $table->text('last_message')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'last_message_at']);
                $table->index('customer_phone');
            });
        }

        if (!Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
                $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
                $table->text('body');
                $table->string('twilio_sid', 50)->nullable()->unique();
                $table->enum('status', ['received', 'queued', 'sent', 'delivered', 'read', 'failed'])->default('received');
                $table->string('media_url')->nullable();
                $table->string('media_content_type', 80)->nullable();
                $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_conversations');
    }
};
