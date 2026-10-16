<?php

namespace Tests\Feature;

use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $employee1;

    private User $employee2;

    private User $agent;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee1 = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();

        // Create second employee for IDOR & isolation tests
        $this->employee2 = User::create([
            'name' => 'Karyawan Uji B',
            'email' => 'karyawan.b@iki.co.id',
            'password' => bcrypt('password123'),
            'employee_number' => 'EMP-099',
            'department_id' => Department::first()->id,
            'is_active' => true,
        ]);
        $employeeRole = Role::where('name', 'employee')->first();
        $this->employee2->roles()->attach($employeeRole);

        // Ensure fake storage disk for testing file uploads/downloads
        Storage::fake('local');
        RateLimiter::clear('employee@iki.co.id|127.0.0.1');
    }

    public function test_health_check_endpoint_returns_healthy_status_and_subsystem_checks(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'app_name',
                'environment',
                'php_version',
                'laravel_version',
                'timestamp_utc',
                'timestamp_wita',
                'checks' => [
                    'database' => ['status', 'latency_ms', 'connection'],
                    'cache' => ['status', 'latency_ms', 'driver'],
                    'storage' => ['status', 'latency_ms', 'disk'],
                ],
            ]);

        $this->assertSame('healthy', $response->json('status'));
        $this->assertSame('up', $response->json('checks.database.status'));
        $this->assertSame('up', $response->json('checks.cache.status'));
        $this->assertSame('up', $response->json('checks.storage.status'));
    }

    public function test_login_rate_limiter_throttles_after_five_failed_attempts(): void
    {
        $targetEmail = 'employee@iki.co.id';

        // 5 consecutive failed attempts
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => $targetEmail,
                'password' => 'wrong-pass-'.$i,
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post('/login', [
            'email' => $targetEmail,
            'password' => 'any-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login gagal', $errorMessage);
    }

    public function test_successful_login_clears_rate_limiter(): void
    {
        $targetEmail = 'employee@iki.co.id';

        // 2 failed attempts
        for ($i = 0; $i < 2; $i++) {
            $this->post('/login', [
                'email' => $targetEmail,
                'password' => 'wrong-pass',
            ]);
        }

        // Successful login
        $response = $this->post('/login', [
            'email' => $targetEmail,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // 5 new failed attempts should now be permitted before lock
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->post('/login', [
                'email' => $targetEmail,
                'password' => 'wrong-again',
            ]);
            $res->assertSessionHasErrors('email');
            $msg = session('errors')->first('email');
            if ($i < 5) {
                $this->assertSame('Email atau password tidak sesuai.', $msg);
            }
        }
    }

    public function test_file_upload_rejects_dangerous_executable_and_script_extensions(): void
    {
        // Attempt creating a ticket with malicious files: .php and .exe
        $dangerousFile1 = UploadedFile::fake()->create('exploit.php', 100, 'text/x-php');
        $dangerousFile2 = UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload');
        $dangerousFile3 = UploadedFile::fake()->create('script.sh', 50, 'application/x-sh');

        foreach ([$dangerousFile1, $dangerousFile2, $dangerousFile3] as $file) {
            $response = $this->actingAs($this->employee1)->post(route('tickets.store'), [
                'subject' => 'Tes Upload File Berbahaya',
                'description' => 'Mencoba upload ekstensi dilarang',
                'category_id' => $this->category->id,
                'impact' => Impact::Low->value,
                'urgency' => Urgency::Low->value,
                'attachments' => [$file],
            ]);

            $response->assertSessionHasErrors('attachments.0');
        }

        // Verify no ticket was created with dangerous attachments
        $this->assertDatabaseMissing('tickets', [
            'subject' => 'Tes Upload File Berbahaya',
        ]);
    }

    public function test_ticket_attachment_upload_rejects_disallowed_types(): void
    {
        $ticket = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000991',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket untuk Upload Lampiran',
            'description' => 'Deskripsi tiket upload lampiran',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
            'source' => 'web',
        ]);

        $dangerousFile = UploadedFile::fake()->create('backdoor.php', 200, 'application/x-php');

        $response = $this->actingAs($this->employee1)
            ->post(route('tickets.uploadAttachment', $ticket), [
                'attachment' => $dangerousFile,
            ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseMissing('ticket_attachments', [
            'ticket_id' => $ticket->id,
            'original_filename' => 'backdoor.php',
        ]);
    }

    public function test_attachment_download_enforces_authorization_preventing_idor(): void
    {
        // Create ticket for employee1
        $ticket1 = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000992',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Rahasia Dokumen Kapal',
            'description' => 'Lampiran rahasia',
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::Medium->value,
            'priority' => Priority::P3->value,
            'status' => TicketStatus::Open->value,
            'assignee_id' => $this->agent->id,
            'source' => 'web',
        ]);

        $storageKey = 'ticket-attachments/'.$ticket1->id.'/secret_plan.pdf';
        Storage::disk('local')->put($storageKey, 'TOP SECRET SHIP DRAWING');

        $attachment1 = TicketAttachment::create([
            'ticket_id' => $ticket1->id,
            'uploaded_by' => $this->employee1->id,
            'original_filename' => 'secret_plan.pdf',
            'safe_filename' => 'uuid_safe.pdf',
            'storage_disk' => 'local',
            'storage_key' => $storageKey,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'created_at' => now(),
        ]);

        // 1. Employee 2 attempts downloading Employee 1's attachment -> 403 Forbidden
        $responseUnauth = $this->actingAs($this->employee2)
            ->get(route('tickets.downloadAttachment', [$ticket1, $attachment1]));
        $responseUnauth->assertStatus(403);

        // 2. Employee 1 (owner) can download -> 200 OK
        $responseOwner = $this->actingAs($this->employee1)
            ->get(route('tickets.downloadAttachment', [$ticket1, $attachment1]));
        $responseOwner->assertStatus(200);

        // 3. IT Agent with permission can download -> 200 OK
        $responseAgent = $this->actingAs($this->agent)
            ->get(route('tickets.downloadAttachment', [$ticket1, $attachment1]));
        $responseAgent->assertStatus(200);

        // 4. IDOR defense: Attempting to download attachment1 via a different ticket2 URL -> 404
        $ticket2 = Ticket::create([
            'ticket_number' => 'IKI-IT-2026-000993',
            'requester_id' => $this->employee1->id,
            'category_id' => $this->category->id,
            'subject' => 'Tiket Lain',
            'description' => 'Deskripsi tiket lain',
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value,
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
            'source' => 'web',
        ]);

        $responseIdorMismatch = $this->actingAs($this->employee1)
            ->get(route('tickets.downloadAttachment', [$ticket2, $attachment1]));
        $responseIdorMismatch->assertStatus(404);
    }
}
