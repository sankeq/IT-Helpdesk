<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Models\Department;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display reporting dashboard with multi-dimensional filters and KPIs.
     */
    public function index(Request $request)
    {
        if (! $request->user()->hasPermission('reports.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat laporan dan analitik.');
        }

        $filters = $this->extractFilters($request);
        $baseQuery = $this->buildFilteredQuery($filters);

        // ─── 1. Core KPIs Calculation ───
        $totalTickets = (clone $baseQuery)->count();

        $statusCounts = (clone $baseQuery)
            ->selectRaw("
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open_count,
                COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned_count,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count
            ")
            ->first();

        $resolvedOrClosed = ($statusCounts->resolved_count ?? 0) + ($statusCounts->closed_count ?? 0);
        $resolutionRate = $totalTickets > 0 ? round(($resolvedOrClosed / $totalTickets) * 100, 1) : 0;
        $backlogCount = ($statusCounts->open_count ?? 0) + ($statusCounts->assigned_count ?? 0) + ($statusCounts->in_progress_count ?? 0) + ($statusCounts->pending_count ?? 0);

        // SLA Compliance
        $slaResolutionQuery = (clone $baseQuery)->whereNotNull('resolution_due_at');
        $totalWithSla = (clone $slaResolutionQuery)->count();
        $slaCompliantCount = (clone $slaResolutionQuery)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('resolved_at')
                        ->whereColumn('resolved_at', '<=', 'resolution_due_at');
                })->orWhere(function ($sub) {
                    $sub->whereNull('resolved_at')
                        ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value, TicketStatus::Cancelled->value])
                        ->where('resolution_due_at', '>=', now());
                });
            })
            ->count();
        $slaComplianceRate = $totalWithSla > 0 ? round(($slaCompliantCount / $totalWithSla) * 100, 1) : 100;

        // SLA First Response Compliance
        $slaResponseQuery = (clone $baseQuery)->whereNotNull('first_response_due_at');
        $totalWithResponseSla = (clone $slaResponseQuery)->count();
        $responseCompliantCount = (clone $slaResponseQuery)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('first_responded_at')
                        ->whereColumn('first_responded_at', '<=', 'first_response_due_at');
                })->orWhere(function ($sub) {
                    $sub->whereNull('first_responded_at')
                        ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value, TicketStatus::Cancelled->value])
                        ->where('first_response_due_at', '>=', now());
                });
            })
            ->count();
        $firstResponseComplianceRate = $totalWithResponseSla > 0 ? round(($responseCompliantCount / $totalWithResponseSla) * 100, 1) : 100;

        // Reopen Rate
        $reopenedCount = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status', TicketStatus::Reopened->value)
                    ->orWhereHas('histories', fn ($h) => $h->where('event_type', 'reopened'));
            })
            ->count();
        $reopenRate = $totalTickets > 0 ? round(($reopenedCount / $totalTickets) * 100, 1) : 0;

        // CSAT Average
        $csatStats = (clone $baseQuery)
            ->whereNotNull('rating')
            ->selectRaw('COUNT(*) as total_ratings, AVG(rating) as avg_rating')
            ->first();
        $avgCsat = $csatStats->avg_rating ? round((float) $csatStats->avg_rating, 1) : null;
        $totalRatings = (int) ($csatStats->total_ratings ?? 0);

        // Average Resolution Duration (in hours)
        $resolvedTickets = (clone $baseQuery)
            ->whereNotNull('resolved_at')
            ->select('created_at', 'resolved_at')
            ->get();
        $avgResolutionHours = 0;
        if ($resolvedTickets->isNotEmpty()) {
            $totalHours = $resolvedTickets->sum(fn ($t) => max(0, $t->created_at->diffInMinutes($t->resolved_at) / 60));
            $avgResolutionHours = round($totalHours / $resolvedTickets->count(), 1);
        }

        // ─── 2. Analytical Breakdowns ───

        // Breakdown by Category
        $byCategory = (clone $baseQuery)
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) use ($totalTickets) {
                return [
                    'name' => $item->category?->name ?? 'Lainnya',
                    'count' => $item->count,
                    'percentage' => $totalTickets > 0 ? round(($item->count / $totalTickets) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Breakdown by Priority
        $byPriority = (clone $baseQuery)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->priority->value => $item->count]);

        // Breakdown by Location
        $byLocation = (clone $baseQuery)
            ->selectRaw('location_id, COUNT(*) as count')
            ->groupBy('location_id')
            ->with('location')
            ->get()
            ->map(function ($item) use ($totalTickets) {
                return [
                    'name' => $item->location?->name ?? 'Tidak Ditentukan',
                    'count' => $item->count,
                    'percentage' => $totalTickets > 0 ? round(($item->count / $totalTickets) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Breakdown by Agent Workload & Performance
        $agents = User::whereHas('roles', fn ($q) => $q->where('name', 'it_agent')->orWhere('name', 'it_supervisor'))
            ->where('is_active', true)
            ->get();

        $agentWorkloads = $agents->map(function ($agent) use ($filters) {
            $agentQuery = (clone $this->buildFilteredQuery($filters))->where('assignee_id', $agent->id);
            $totalAssigned = (clone $agentQuery)->count();
            $resolvedCount = (clone $agentQuery)->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count();
            $breachedCount = (clone $agentQuery)->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('resolved_at')->whereColumn('resolved_at', '>', 'resolution_due_at');
                })->orWhere(function ($sub) {
                    $sub->whereNull('resolved_at')
                        ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value, TicketStatus::Cancelled->value])
                        ->where('resolution_due_at', '<', now());
                });
            })->count();
            $agentAvgCsat = (clone $agentQuery)->whereNotNull('rating')->avg('rating');

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'total_assigned' => $totalAssigned,
                'resolved_count' => $resolvedCount,
                'breached_count' => $breachedCount,
                'csat' => $agentAvgCsat ? round((float) $agentAvgCsat, 1) : null,
            ];
        })->sortByDesc('total_assigned')->values();

        // ─── 3. Paginated Filtered Tickets List ───
        $tickets = (clone $baseQuery)
            ->with(['requester.department', 'assignee', 'category', 'location'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Master lists for filter dropdowns
        $categories = TicketCategory::where('is_active', true)->whereNotNull('parent_id')->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', [
            'filters' => $filters,
            'kpis' => [
                'total' => $totalTickets,
                'resolution_rate' => $resolutionRate,
                'backlog' => $backlogCount,
                'sla_compliance_rate' => $slaComplianceRate,
                'first_response_compliance_rate' => $firstResponseComplianceRate,
                'reopen_rate' => $reopenRate,
                'avg_csat' => $avgCsat,
                'total_ratings' => $totalRatings,
                'avg_resolution_hours' => $avgResolutionHours,
            ],
            'byCategory' => $byCategory,
            'byPriority' => $byPriority,
            'byLocation' => $byLocation,
            'agentWorkloads' => $agentWorkloads,
            'tickets' => $tickets,
            'categories' => $categories,
            'departments' => $departments,
            'locations' => $locations,
            'agents' => $agents,
            'statuses' => TicketStatus::cases(),
            'priorities' => Priority::cases(),
        ]);
    }

    /**
     * Export filtered tickets to CSV with formula injection protection (CWE-1236).
     */
    public function export(Request $request): StreamedResponse
    {
        if (! $request->user()->hasPermission('reports.export')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor data laporan.');
        }

        $filters = $this->extractFilters($request);
        $query = $this->buildFilteredQuery($filters)
            ->with(['requester.department', 'assignee', 'category', 'location'])
            ->orderBy('created_at', 'desc');

        $filename = 'laporan-tiket-iki-'.now()->timezone('Asia/Makassar')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'No. Tiket',
                'Subjek',
                'Kategori',
                'Pelapor',
                'Departemen',
                'Lokasi',
                'Teknisi',
                'Prioritas',
                'Status',
                'Target Respon (WITA)',
                'Realisasi Respon (WITA)',
                'Target Resolusi (WITA)',
                'Realisasi Resolusi (WITA)',
                'Durasi Jeda (Menit)',
                'Rating CSAT (1-5)',
                'Feedback CSAT',
                'Waktu Dibuat (WITA)',
            ]);

            // Stream tickets chunk by chunk for minimal memory footprint
            $query->chunk(200, function ($tickets) use ($handle) {
                foreach ($tickets as $ticket) {
                    $firstResponseDue = $ticket->first_response_due_at ? $ticket->first_response_due_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-';
                    $firstRespondedAt = $ticket->first_responded_at ? $ticket->first_responded_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-';
                    $resolutionDue = $ticket->resolution_due_at ? $ticket->resolution_due_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-';
                    $resolvedAt = $ticket->resolved_at ? $ticket->resolved_at->timezone('Asia/Makassar')->format('d/m/Y H:i') : '-';
                    $createdAt = $ticket->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i');

                    fputcsv($handle, [
                        $this->sanitizeCsvField($ticket->ticket_number),
                        $this->sanitizeCsvField($ticket->subject),
                        $this->sanitizeCsvField($ticket->category?->name ?? '-'),
                        $this->sanitizeCsvField($ticket->requester?->name ?? '-'),
                        $this->sanitizeCsvField($ticket->requester?->department?->name ?? '-'),
                        $this->sanitizeCsvField($ticket->location?->name ?? '-'),
                        $this->sanitizeCsvField($ticket->assignee?->name ?? 'Belum Ditugaskan'),
                        $ticket->priority->value,
                        $ticket->status->label(),
                        $firstResponseDue,
                        $firstRespondedAt,
                        $resolutionDue,
                        $resolvedAt,
                        round($ticket->total_paused_seconds / 60),
                        $ticket->rating ?? '-',
                        $this->sanitizeCsvField($ticket->rating_comment ?? '-'),
                        $createdAt,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Sanitize string against CSV Formula Injection (OWASP CWE-1236).
     * Prepends single quote if field starts with formula character (=, +, -, @, tab, cr).
     */
    public function sanitizeCsvField(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $dangerousPrefixes = ['=', '+', '-', '@', "\t", "\r"];
        $firstChar = substr($value, 0, 1);

        if (in_array($firstChar, $dangerousPrefixes, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Extract and sanitize request filter parameters.
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'category_id' => $request->input('category_id'),
            'department_id' => $request->input('department_id'),
            'location_id' => $request->input('location_id'),
            'assignee_id' => $request->input('assignee_id'),
        ];
    }

    /**
     * Build filtered Ticket query based on validated criteria.
     */
    protected function buildFilteredQuery(array $filters): Builder
    {
        $query = Ticket::query();

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'], 'Asia/Makassar')->startOfDay()->setTimezone('UTC'));
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'], 'Asia/Makassar')->endOfDay()->setTimezone('UTC'));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('requester', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        return $query;
    }
}
