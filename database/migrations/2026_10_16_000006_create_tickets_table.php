<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->unique();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->constrained('ticket_categories');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject', 150);
            $table->text('description');
            $table->string('impact');
            $table->string('urgency');
            $table->string('priority');
            $table->string('status')->default('open');
            $table->string('source')->default('web');
            $table->string('pending_reason')->nullable();
            $table->text('pending_note')->nullable();
            $table->text('resolution_summary')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority', 'created_at']);
            $table->index(['assignee_id', 'status']);
            $table->index(['requester_id', 'created_at']);
            $table->index(['category_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_number_sequences');
    }
};
