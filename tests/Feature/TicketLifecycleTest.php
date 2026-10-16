<?php

namespace Tests\Feature;

use App\Enums\CommentVisibility;
use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $agent;

    private User $supervisor;

    private TicketCategory $category;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
        $this->location = Location::first();
    }

    public function test_authenticated_user_can_view_ticket_create_form(): void
    {
        $response = $this->actingAs($this->employee)->get(route('tickets.create'));
        $response->assertStatus(200);
        $response->assertSee('Form Permohonan Bantuan IT');
    }

    public function test_user_can_create_ticket(): void
    {
        $response = $this->actingAs($this->employee)->post(route('tickets.store'), [
            'subject' => 'Komputer Bengkel Mesin Tidak Nyala',
            'description' => 'Layar monitor hitam dan kipas CPU berbunyi keras.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::High->value,
        ]);

        $this->assertDatabaseHas('tickets', [
            'subject' => 'Komputer Bengkel Mesin Tidak Nyala',
            'requester_id' => $this->employee->id,
            'status' => TicketStatus::Open->value,
            'priority' => Priority::P2->value, // Medium + High = P2
        ]);

        $ticket = Ticket::where('subject', 'Komputer Bengkel Mesin Tidak Nyala')->first();
        $response->assertRedirect(route('tickets.show', $ticket));
    }

    public function test_supervisor_can_assign_ticket(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000099',
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'subject' => 'Test Assign Subject',
            'description' => 'Test Assign Description',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        $response = $this->actingAs($this->supervisor)->post(route('tickets.assign', $ticket), [
            'assignee_id' => $this->agent->id,
            'reason' => 'Tolong prioritaskan',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertEquals($this->agent->id, $ticket->assignee_id);
        $this->assertEquals(TicketStatus::Assigned, $ticket->status);
    }

    public function test_agent_can_transition_to_in_progress_and_resolve(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000100',
            'requester_id' => $this->employee->id,
            'assignee_id' => $this->agent->id,
            'category_id' => $this->category->id,
            'subject' => 'Test Transition Subject',
            'description' => 'Test Transition Description',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Assigned->value,
        ]);

        // 1. Move to in_progress
        $response = $this->actingAs($this->agent)->post(route('tickets.transition', $ticket), [
            'status' => TicketStatus::InProgress->value,
        ]);
        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertEquals(TicketStatus::InProgress, $ticket->status);

        // 2. Resolve
        $response = $this->actingAs($this->agent)->post(route('tickets.transition', $ticket), [
            'status' => TicketStatus::Resolved->value,
            'resolution_summary' => 'Masalah telah diselesaikan dengan penggantian kabel data.',
        ]);
        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertEquals(TicketStatus::Resolved, $ticket->status);
        $this->assertEquals('Masalah telah diselesaikan dengan penggantian kabel data.', $ticket->resolution_summary);
    }

    public function test_user_can_add_comment(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000101',
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'subject' => 'Test Comment Subject',
            'description' => 'Test Comment Description',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        $response = $this->actingAs($this->employee)->post(route('tickets.comment', $ticket), [
            'body' => 'Saya ingin menanyakan kapan teknisi bisa datang?',
            'visibility' => CommentVisibility::Public->value,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->employee->id,
            'body' => 'Saya ingin menanyakan kapan teknisi bisa datang?',
            'visibility' => CommentVisibility::Public->value,
        ]);
    }

    public function test_attachment_upload_and_download(): void
    {
        Storage::fake('local');

        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000102',
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'subject' => 'Test Attachment Subject',
            'description' => 'Test Attachment Description',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        $file = UploadedFile::fake()->create('foto_kendala.png', 500, 'image/png');

        $response = $this->actingAs($this->employee)->post(route('tickets.uploadAttachment', $ticket), [
            'attachment' => $file,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $attachment = $ticket->attachments()->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('foto_kendala.png', $attachment->original_filename);

        // Download attachment
        $downloadResponse = $this->actingAs($this->employee)->get(route('tickets.downloadAttachment', [
            'ticket' => $ticket,
            'attachment' => $attachment,
        ]));
        $downloadResponse->assertStatus(200);
    }
}
