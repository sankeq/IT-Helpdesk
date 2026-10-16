<?php

namespace Tests\Feature;

use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Asset;
use App\Models\Department;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetRegistryTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $agent;

    private User $supervisor;

    private User $admin;

    private Location $location;

    private Department $department;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->admin = User::where('email', 'admin@iki.co.id')->first();
        $this->location = Location::first();
        $this->department = Department::first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
    }

    public function test_unauthorized_employee_cannot_view_asset_list(): void
    {
        $response = $this->actingAs($this->employee)->get(route('assets.index'));
        $response->assertStatus(403);
    }

    public function test_agent_and_supervisor_can_view_asset_list(): void
    {
        // Agent has assets.view
        $resAgent = $this->actingAs($this->agent)->get(route('assets.index'));
        $resAgent->assertStatus(200);
        $resAgent->assertSeeText('Inventaris Aset Teknologi Informasi');

        // Supervisor has assets.manage
        $resSup = $this->actingAs($this->supervisor)->get(route('assets.index'));
        $resSup->assertStatus(200);
        $resSup->assertSeeText('Inventaris Aset Teknologi Informasi');
    }

    public function test_agent_cannot_create_asset_without_manage_permission(): void
    {
        $response = $this->actingAs($this->agent)->get(route('assets.create'));
        $response->assertStatus(403);

        $postRes = $this->actingAs($this->agent)->post(route('assets.store'), [
            'asset_tag' => 'IKI-AST-FORBIDDEN',
            'name' => 'Unauthorized Asset',
            'category' => 'hardware',
            'status' => 'active',
        ]);
        $postRes->assertStatus(403);
    }

    public function test_supervisor_can_create_new_asset(): void
    {
        $payload = [
            'asset_tag' => 'IKI-AST-2026-999',
            'name' => 'Workstation Galangan Dock 2',
            'category' => 'hardware',
            'model' => 'HP Z4 G5 Workstation',
            'serial_number' => 'SN-HP-Z4-9988',
            'status' => 'active',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'assigned_to_user_id' => $this->employee->id,
            'purchase_date' => '2026-02-01',
            'warranty_expiry' => '2029-02-01',
            'notes' => 'Workstation gambar rancang kapal baru.',
        ];

        $response = $this->actingAs($this->supervisor)->post(route('assets.store'), $payload);

        $asset = Asset::where('asset_tag', 'IKI-AST-2026-999')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('Workstation Galangan Dock 2', $asset->name);
        $this->assertEquals($this->employee->id, $asset->assigned_to_user_id);

        $response->assertRedirect(route('assets.show', $asset));
    }

    public function test_asset_detail_shows_linked_ticket_history(): void
    {
        $asset = Asset::first();

        // Create ticket linked to this asset
        $ticket = Ticket::create([
            'ticket_number' => 'TICK-AST-HIST-01',
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'asset_id' => $asset->id,
            'subject' => 'Monitor Workstation Bergaris dan Berkedip',
            'description' => 'Layar monitor berkedip setiap 5 menit.',
            'status' => TicketStatus::Open->value,
            'priority' => Priority::P3->value,
            'urgency' => Urgency::Medium->value,
            'impact' => Impact::Medium->value,
        ]);

        $response = $this->actingAs($this->agent)->get(route('assets.show', $asset));

        $response->assertStatus(200);
        $response->assertSeeText($asset->asset_tag);
        $response->assertSeeText('TICK-AST-HIST-01');
        $response->assertSeeText('Monitor Workstation Bergaris dan Berkedip');
    }

    public function test_supervisor_can_update_asset(): void
    {
        $asset = Asset::first();

        $response = $this->actingAs($this->supervisor)->put(route('assets.update', $asset), [
            'asset_tag' => $asset->asset_tag,
            'name' => 'Nama Baru Aset Terupdate',
            'category' => $asset->category,
            'status' => 'maintenance',
            'model' => $asset->model,
            'serial_number' => $asset->serial_number,
        ]);

        $response->assertRedirect(route('assets.show', $asset));

        $asset->refresh();
        $this->assertEquals('Nama Baru Aset Terupdate', $asset->name);
        $this->assertEquals('maintenance', $asset->status);
    }

    public function test_deleting_asset_with_tickets_marks_it_as_retired(): void
    {
        $asset = Asset::first();

        // Create ticket linking to this asset
        Ticket::create([
            'ticket_number' => 'TICK-DEL-TEST',
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'asset_id' => $asset->id,
            'subject' => 'Kendala Aset',
            'description' => 'Deskripsi kendala',
            'status' => TicketStatus::Open->value,
            'priority' => Priority::P3->value,
            'urgency' => Urgency::Medium->value,
            'impact' => Impact::Medium->value,
        ]);

        $response = $this->actingAs($this->supervisor)->delete(route('assets.destroy', $asset));

        $response->assertRedirect(route('assets.index'));
        $response->assertSessionHas('warning');

        $asset->refresh();
        $this->assertEquals('retired', $asset->status);
        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    public function test_user_can_create_ticket_with_linked_asset(): void
    {
        $asset = Asset::first();

        $response = $this->actingAs($this->employee)->post(route('tickets.store'), [
            'subject' => 'Komputer Bengkel Mesin Blue Screen',
            'description' => 'Terjadi BSOD saat membuka aplikasi inventaris galangan.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'asset_id' => $asset->id,
            'impact' => 'medium',
            'urgency' => 'high',
        ]);

        $response->assertRedirect();

        $ticket = Ticket::where('subject', 'Komputer Bengkel Mesin Blue Screen')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($asset->id, $ticket->asset_id);
    }
}
