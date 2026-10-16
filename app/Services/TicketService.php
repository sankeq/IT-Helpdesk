<?php

namespace App\Services;

use App\Enums\HistoryEventType;
use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Core business logic for ticket operations.
 * Keeps controllers thin and testable.
 */
class TicketService
{
    public function __construct(
        private TicketNumberGenerator $numberGenerator,
        private TicketWorkflowService $workflowService,
        private ApprovalService $approvalService,
        private SlaCalculatorService $slaCalculator,
    ) {}

    /**
     * Create a new ticket with attachments and history.
     */
    public function createTicket(User $requester, array $data, array $files = []): Ticket
    {
        return DB::transaction(function () use ($requester, $data, $files) {
            // Calculate priority from impact + urgency matrix
            $impact = Impact::from($data['impact']);
            $urgency = Urgency::from($data['urgency']);
            $priority = Priority::fromMatrix($impact, $urgency);

            $ticket = Ticket::create([
                'ticket_number' => $this->numberGenerator->generate(),
                'requester_id' => $requester->id,
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'] ?? $requester->location_id,
                'asset_id' => $data['asset_id'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'impact' => $impact,
                'urgency' => $urgency,
                'priority' => $priority,
                'status' => TicketStatus::Open,
                'source' => 'web',
            ]);

            // Apply SLA Policy and calculate initial due targets
            $this->slaCalculator->applySla($ticket);

            // Handle file attachments
            foreach ($files as $file) {
                $this->storeAttachment($ticket, $requester, $file);
            }

            // Record creation history
            $ticket->histories()->create([
                'actor_id' => $requester->id,
                'event_type' => HistoryEventType::TicketCreated,
                'new_value' => [
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'category_id' => $ticket->category_id,
                    'priority' => $priority->value,
                    'status' => TicketStatus::Open->value,
                ],
                'created_at' => now(),
            ]);

            // If category requires approval, automatically initiate approval workflow
            $category = $ticket->category ?? TicketCategory::find($ticket->category_id);
            if ($category?->requiresApproval()) {
                $approver = ! empty($data['approver_id']) ? User::find($data['approver_id']) : null;
                $this->approvalService->requestApproval($ticket, $requester, $approver);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Assign a ticket to a technician.
     */
    public function assignTicket(Ticket $ticket, User $assignee, User $actor, ?string $reason = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $assignee, $actor, $reason) {
            $previousAssignee = $ticket->assignee;
            $wasOpen = $ticket->status === TicketStatus::Open;

            $ticket->update([
                'assignee_id' => $assignee->id,
                'status' => ($wasOpen || $ticket->status === TicketStatus::Reopened)
                    ? TicketStatus::Assigned
                    : $ticket->status,
            ]);

            $eventType = $previousAssignee
                ? HistoryEventType::Reassigned
                : HistoryEventType::Assigned;

            $ticket->histories()->create([
                'actor_id' => $actor->id,
                'event_type' => $eventType,
                'old_value' => $previousAssignee ? [
                    'assignee_id' => $previousAssignee->id,
                    'assignee_name' => $previousAssignee->name,
                ] : null,
                'new_value' => [
                    'assignee_id' => $assignee->id,
                    'assignee_name' => $assignee->name,
                ],
                'reason' => $reason,
                'created_at' => now(),
            ]);

            // If status changed to assigned, also record status change
            if ($wasOpen || $ticket->status === TicketStatus::Assigned) {
                if ($wasOpen) {
                    $ticket->histories()->create([
                        'actor_id' => $actor->id,
                        'event_type' => HistoryEventType::StatusChanged,
                        'old_value' => ['status' => TicketStatus::Open->value],
                        'new_value' => ['status' => TicketStatus::Assigned->value],
                        'created_at' => now(),
                    ]);
                }
            }

            // Send assignment notification to the technician
            $assignee->notify(new TicketAssignedNotification($ticket, $actor));

            return $ticket->fresh();
        });
    }

    /**
     * Store an attachment for a ticket securely.
     */
    public function storeAttachment(Ticket $ticket, User $uploader, UploadedFile $file): TicketAttachment
    {
        $safeFilename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $storagePath = "ticket-attachments/{$ticket->id}";

        // Store in private disk
        $path = $file->storeAs($storagePath, $safeFilename, 'local');

        $attachment = $ticket->attachments()->create([
            'uploaded_by' => $uploader->id,
            'storage_disk' => 'local',
            'storage_key' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'safe_filename' => $safeFilename,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'checksum' => md5_file($file->getRealPath()),
            'created_at' => now(),
        ]);

        $ticket->histories()->create([
            'actor_id' => $uploader->id,
            'event_type' => HistoryEventType::AttachmentAdded,
            'new_value' => [
                'attachment_id' => $attachment->id,
                'filename' => $attachment->original_filename,
            ],
            'created_at' => now(),
        ]);

        return $attachment;
    }

    /**
     * Add a comment to a ticket with history recording.
     */
    public function addComment(Ticket $ticket, User $author, string $body, string $visibility = 'public'): TicketComment
    {
        return DB::transaction(function () use ($ticket, $author, $body, $visibility) {
            $comment = $ticket->comments()->create([
                'user_id' => $author->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);

            $ticket->histories()->create([
                'actor_id' => $author->id,
                'event_type' => HistoryEventType::CommentAdded,
                'new_value' => [
                    'comment_id' => $comment->id,
                    'visibility' => $visibility,
                ],
                'created_at' => now(),
            ]);

            // If comment is public, record potential first response
            if ($visibility === 'public') {
                $this->slaCalculator->recordFirstResponse($ticket, $author);
            }

            return $comment;
        });
    }

    /**
     * Get allowed file extensions from config.
     */
    public static function allowedExtensions(): array
    {
        $types = config('app.ticket_attachment_allowed_types',
            'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar');

        return explode(',', $types);
    }

    /**
     * Get max file size in KB from config.
     */
    public static function maxFileSizeKB(): int
    {
        return (int) config('app.ticket_attachment_max_size', 10240);
    }
}
