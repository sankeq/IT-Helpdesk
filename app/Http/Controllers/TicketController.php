<?php

namespace App\Http\Controllers;

use App\Enums\CommentVisibility;
use App\Enums\Impact;
use App\Enums\PendingReason;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\FilterTicketRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TransitionTicketRequest;
use App\Http\Requests\UploadAttachmentRequest;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use App\Services\TicketWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
        private TicketWorkflowService $workflowService,
    ) {}

    /**
     * Display ticket list with search, filter, sort, and pagination.
     */
    public function index(FilterTicketRequest $request)
    {
        Gate::authorize('viewAny', Ticket::class);

        $validated = $request->validated();

        $query = Ticket::with(['requester', 'assignee', 'category'])
            ->visibleTo($request->user())
            ->search($validated['search'] ?? null)
            ->filterStatus($validated['status'] ?? null)
            ->filterPriority($validated['priority'] ?? null)
            ->filterCategory($validated['category_id'] ?? null)
            ->filterDateRange($validated['date_from'] ?? null, $validated['date_to'] ?? null);

        // Sorting
        $sortColumn = $validated['sort'] ?? 'created_at';
        $sortDirection = $validated['direction'] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $validated['per_page'] ?? 25;
        $tickets = $query->paginate($perPage)->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'statuses' => TicketStatus::cases(),
            'priorities' => Priority::cases(),
            'categories' => TicketCategory::active()->roots()->orderBy('sort_order')->get(),
            'filters' => $validated,
        ]);
    }

    /**
     * Show ticket creation form.
     */
    public function create()
    {
        Gate::authorize('create', Ticket::class);

        return view('tickets.create', [
            'categories' => TicketCategory::active()->roots()->with('children')->orderBy('sort_order')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
            'assets' => Asset::where('status', 'active')->orderBy('asset_tag')->get(),
            'impacts' => Impact::cases(),
            'urgencies' => Urgency::cases(),
            'maxFileSize' => TicketService::maxFileSizeKB(),
            'allowedTypes' => TicketService::allowedExtensions(),
        ]);
    }

    /**
     * Store a new ticket.
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->createTicket(
            $request->user(),
            $request->validated(),
            $request->file('attachments', []),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil dibuat.");
    }

    /**
     * Show ticket detail page.
     */
    public function show(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load([
            'requester.department',
            'assignee',
            'category.parent',
            'location',
            'asset',
            'attachments.uploader',
            'histories.actor',
            'approvals.approver',
        ]);

        // Load comments based on user permissions
        $canViewInternal = Gate::allows('viewInternalNotes', $ticket);
        $comments = $ticket->comments()
            ->with('user')
            ->when(! $canViewInternal, function ($query) {
                $query->where('visibility', CommentVisibility::Public);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // Determine available actions for current user using WorkflowService
        $availableTransitions = $this->workflowService->getAvailableTransitions($ticket, $request->user());

        // Get agents for assignment
        $agents = null;
        if (Gate::allows('assign', $ticket)) {
            $agents = User::active()
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['it_agent', 'it_supervisor']);
                })
                ->orderBy('name')
                ->get();
        }

        $latestApproval = $ticket->latestApproval;
        $canDecideApproval = $latestApproval && $latestApproval->isPending() && (
            $request->user()->hasPermission('approvals.decide') ||
            $latestApproval->approver_id === $request->user()->id
        );

        return view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $comments,
            'canViewInternal' => $canViewInternal,
            'canComment' => Gate::allows('commentPublic', $ticket),
            'canCommentInternal' => Gate::allows('commentInternal', $ticket),
            'canAssign' => Gate::allows('assign', $ticket),
            'canUpload' => Gate::allows('uploadAttachment', $ticket),
            'canChangePriority' => Gate::allows('changePriority', $ticket),
            'availableTransitions' => $availableTransitions,
            'agents' => $agents,
            'pendingReasons' => PendingReason::cases(),
            'latestApproval' => $latestApproval,
            'canDecideApproval' => $canDecideApproval,
        ]);
    }

    /**
     * Assign ticket to a technician.
     */
    public function assign(AssignTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);

        $assignee = User::findOrFail($request->validated('assignee_id'));

        $this->ticketService->assignTicket(
            $ticket,
            $assignee,
            $request->user(),
            $request->validated('reason'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket berhasil ditugaskan kepada {$assignee->name}.");
    }

    /**
     * Transition ticket status.
     */
    public function transition(TransitionTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('transition', $ticket);

        $newStatus = TicketStatus::from($request->validated('status'));

        $this->workflowService->transition(
            $ticket,
            $newStatus,
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Status tiket berhasil diubah menjadi {$newStatus->label()}.");
    }

    /**
     * Add comment to ticket.
     */
    public function comment(StoreCommentRequest $request, Ticket $ticket)
    {
        $visibility = $request->validated('visibility');

        // Check permission based on visibility
        if ($visibility === CommentVisibility::Internal->value) {
            Gate::authorize('commentInternal', $ticket);
        } else {
            Gate::authorize('commentPublic', $ticket);
        }

        $this->ticketService->addComment(
            $ticket,
            $request->user(),
            $request->validated('body'),
            $visibility,
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Upload attachment to ticket.
     */
    public function uploadAttachment(UploadAttachmentRequest $request, Ticket $ticket)
    {
        Gate::authorize('uploadAttachment', $ticket);

        $this->ticketService->storeAttachment(
            $ticket,
            $request->user(),
            $request->file('attachment'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Lampiran berhasil diunggah.');
    }

    /**
     * Download attachment with authorization check.
     */
    public function downloadAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        Gate::authorize('downloadAttachment', $ticket);

        // Verify attachment belongs to this ticket
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        $path = Storage::disk($attachment->storage_disk)->path($attachment->storage_key);

        if (! file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path, $attachment->original_filename, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    /**
     * Submit CSAT rating for a resolved or closed ticket.
     */
    public function rate(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        if (! $ticket->canBeRatedBy($request->user())) {
            abort(403, 'Anda tidak diizinkan memberikan penilaian pada tiket ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih bintang kepuasan (1-5).',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
        ]);

        $ticket->update([
            'rating' => $validated['rating'],
            'rating_comment' => $validated['rating_comment'] ?? null,
            'rated_at' => now(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Terima kasih atas penilaian dan masukan Anda untuk peningkatan layanan IT IKI.');
    }
}
