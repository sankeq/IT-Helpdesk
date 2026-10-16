<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Business Calendars
        Schema::create('business_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('timezone')->default('Asia/Makassar');
            $table->string('work_start_time')->default('08:00');
            $table->string('work_end_time')->default('17:00');
            $table->json('work_days')->nullable(); // [1, 2, 3, 4, 5] (Monday to Friday)
            $table->json('holidays')->nullable(); // Array of "YYYY-MM-DD"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. SLA Policies
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('priority'); // P1, P2, P3, P4
            $table->integer('first_response_minutes');
            $table->integer('resolution_minutes');
            $table->foreignId('business_calendar_id')->nullable()->constrained('business_calendars')->nullOnDelete();
            $table->json('pause_on_pending_reasons')->nullable(); // ["waiting_vendor", "waiting_parts", "waiting_approval"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['priority', 'is_active']);
        });

        // 3. Add SLA columns to tickets table
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('sla_policy_id')->nullable()->after('priority')->constrained('sla_policies')->nullOnDelete();
            $table->json('sla_policy_snapshot')->nullable()->after('sla_policy_id');
            $table->timestamp('first_response_due_at')->nullable()->after('sla_policy_snapshot');
            $table->timestamp('resolution_due_at')->nullable()->after('first_response_due_at');
            $table->timestamp('first_responded_at')->nullable()->after('resolution_due_at');
            $table->unsignedBigInteger('total_paused_seconds')->default(0)->after('first_responded_at');
            $table->timestamp('last_paused_at')->nullable()->after('total_paused_seconds');
            $table->boolean('is_sla_paused')->default(false)->after('last_paused_at');

            $table->index(['first_response_due_at', 'status']);
            $table->index(['resolution_due_at', 'status']);
        });

        // 4. Ticket SLA Events (Audit & Idempotency)
        Schema::create('ticket_sla_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('event_type'); // warning, pause, resume, response_breach, resolution_breach, target_recalculated
            $table->timestamp('occurred_at');
            $table->unsignedBigInteger('duration_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['ticket_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sla_events');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['sla_policy_id']);
            $table->dropColumn([
                'sla_policy_id',
                'sla_policy_snapshot',
                'first_response_due_at',
                'resolution_due_at',
                'first_responded_at',
                'total_paused_seconds',
                'last_paused_at',
                'is_sla_paused',
            ]);
        });

        Schema::dropIfExists('sla_policies');
        Schema::dropIfExists('business_calendars');
    }
};
