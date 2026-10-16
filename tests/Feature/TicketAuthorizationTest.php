<?php

namespace Tests\Feature;

use App\Enums\CommentVisibility;
use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $employee1;

    private User $employee2;

    private User $agent;

    private User $supervisor;

    private User $admin;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee1 = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->admin = User::where('email', 'admin@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();

        // Create second employee for isolation checks
        $this->employee2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@iki.co.id',
            'password' => bcrypt('password123'),
            'employee_number' => 'EMP-002',
            'department_id' => Department::first()->id,
            'is_active' => true,
        ]);
        $employeeRole = Role::where('name', 'employee')->first();
        $this->employee2->roles()->attach($employeeRole);
    }

    public function test_employee_cannot_see_other_employees_ticket(): void
    {
        $ticketOfEmployee1 = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000201',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Rahasia Employee 1',
            'description' => 'Deskripsi tiket karyawan 1',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        // Employee 2 attempts to view Employee 1's ticket -> 403 Forbidden
        $response = $this->actingAs($this->employee2)->get(route('tickets.show', $ticketOfEmployee1));
        $response->assertStatus(403);

        // Employee 2's index view does not contain Employee 1's ticket
        $indexResponse = $this->actingAs($this->employee2)->get(route('tickets.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Tiket Rahasia Employee 1');
    }

    public function test_supervisor_and_admin_can_see_all_tickets(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000202',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Untuk Supervisor Check',
            'description' => 'Deskripsi tiket',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        // Supervisor can view
        $response1 = $this->actingAs($this->supervisor)->get(route('tickets.show', $ticket));
        $response1->assertStatus(200);

        // Super Admin can view
        $response2 = $this->actingAs($this->admin)->get(route('tickets.show', $ticket));
        $response2->assertStatus(200);
    }

    public function test_employee_cannot_view_internal_notes(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000203',
            'requester_id' => $this->employee1->id,
            'assignee_id' => $this->agent->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Uji Catatan Internal',
            'description' => 'Deskripsi tiket',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Assigned->value,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->employee1->id,
            'body' => 'Ini komentar publik karyawan.',
            'visibility' => CommentVisibility::Public,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->agent->id,
            'body' => 'RAHASIA: Jangan beri tahu karyawan tentang kerusakan motherboard.',
            'visibility' => CommentVisibility::Internal,
        ]);

        // Employee views ticket
        $response = $this->actingAs($this->employee1)->get(route('tickets.show', $ticket));
        $response->assertStatus(200);
        $response->assertSee('Ini komentar publik karyawan.');
        $response->assertDontSee('RAHASIA: Jangan beri tahu karyawan tentang kerusakan motherboard.');

        // Agent views ticket
        $agentResponse = $this->actingAs($this->agent)->get(route('tickets.show', $ticket));
        $agentResponse->assertStatus(200);
        $agentResponse->assertSee('RAHASIA: Jangan beri tahu karyawan tentang kerusakan motherboard.');
    }

    public function test_employee_cannot_post_internal_notes(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000204',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Uji Larangan Catatan Internal',
            'description' => 'Deskripsi tiket',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
        ]);

        // Employee attempts to post internal comment -> 403 Forbidden
        $response = $this->actingAs($this->employee1)->post(route('tickets.comment', $ticket), [
            'body' => 'Mencoba kirim catatan internal ilegal.',
            'visibility' => CommentVisibility::Internal->value,
        ]);

        $response->assertStatus(403);
    }
}
