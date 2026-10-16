<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $ticketQuery = Ticket::visibleTo($user);

        $counts = (clone $ticketQuery)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'open' THEN 1 END) as open,
                COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved,
                COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed
            ")
            ->first();

        $stats = [
            'total' => (int) ($counts->total ?? 0),
            'open' => (int) ($counts->open ?? 0),
            'assigned' => (int) ($counts->assigned ?? 0),
            'in_progress' => (int) ($counts->in_progress ?? 0),
            'pending' => (int) ($counts->pending ?? 0),
            'resolved' => (int) ($counts->resolved ?? 0),
            'closed' => (int) ($counts->closed ?? 0),
        ];

        $recentTickets = Ticket::with(['requester', 'assignee', 'category'])
            ->visibleTo($user)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ─── Role-Specific Tailored Data ───
        $roleData = [];

        $isAgent = $user->hasPermission('tickets.update') && ! $user->hasPermission('tickets.view_all');
        $isSupervisorOrAdmin = $user->hasPermission('tickets.view_all') || $user->hasPermission('reports.view');
        $isEmployee = ! $isAgent && ! $isSupervisorOrAdmin;

        if ($isAgent) {
            // My active assigned tickets
            $roleData['myAssignedCount'] = Ticket::where('assignee_id', $user->id)
                ->whereIn('status', [TicketStatus::Assigned, TicketStatus::InProgress, TicketStatus::Pending])
                ->count();

            // Unassigned queue
            $roleData['unassignedTickets'] = Ticket::whereNull('assignee_id')
                ->where('status', TicketStatus::Open)
                ->with(['requester', 'category', 'location'])
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get();
            $roleData['unassignedCount'] = Ticket::whereNull('assignee_id')->where('status', TicketStatus::Open)->count();

            // Urgent attention queue (breached or near breach)
            $roleData['urgentTickets'] = Ticket::where('assignee_id', $user->id)
                ->whereIn('status', [TicketStatus::Assigned, TicketStatus::InProgress])
                ->where('is_sla_paused', false)
                ->where(function ($q) {
                    $q->where('resolution_due_at', '<=', now()->addHours(2))
                        ->orWhere(function ($sub) {
                            $sub->whereNull('first_responded_at')
                                ->where('first_response_due_at', '<=', now()->addMinutes(30));
                        });
                })
                ->with(['category', 'requester'])
                ->orderBy('resolution_due_at', 'asc')
                ->limit(5)
                ->get();
        }

        if ($isSupervisorOrAdmin) {
            // SLA Compliance Rate
            $totalWithSla = Ticket::whereNotNull('resolution_due_at')->count();
            $slaCompliantCount = Ticket::whereNotNull('resolution_due_at')
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNotNull('resolved_at')->whereColumn('resolved_at', '<=', 'resolution_due_at');
                    })->orWhere(function ($sub) {
                        $sub->whereNull('resolved_at')
                            ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value, TicketStatus::Cancelled->value])
                            ->where('resolution_due_at', '>=', now());
                    });
                })
                ->count();
            $roleData['slaComplianceRate'] = $totalWithSla > 0 ? round(($slaCompliantCount / $totalWithSla) * 100, 1) : 100;

            // CSAT Average
            $csatAverage = Ticket::whereNotNull('rating')->avg('rating');
            $roleData['avgCsat'] = $csatAverage ? round((float) $csatAverage, 1) : null;
            $roleData['totalRatings'] = Ticket::whereNotNull('rating')->count();

            // Active Backlog
            $roleData['backlogCount'] = Ticket::whereIn('status', [
                TicketStatus::Open,
                TicketStatus::Assigned,
                TicketStatus::InProgress,
                TicketStatus::Pending,
            ])->count();

            // Agent Workloads
            $roleData['agentWorkloads'] = User::whereHas('roles', fn ($q) => $q->where('name', 'it_agent'))
                ->where('is_active', true)
                ->withCount([
                    'ticketsAssigned as active_tickets_count' => fn ($q) => $q->whereIn('status', [
                        TicketStatus::Assigned,
                        TicketStatus::InProgress,
                        TicketStatus::Pending,
                    ]),
                ])
                ->orderByDesc('active_tickets_count')
                ->limit(5)
                ->get();
        }

        if ($isEmployee) {
            // Tickets needing rating
            $roleData['pendingRatingTickets'] = Ticket::where('requester_id', $user->id)
                ->whereIn('status', [TicketStatus::Resolved, TicketStatus::Closed])
                ->whereNull('rating')
                ->with('category')
                ->limit(3)
                ->get();
        }

        return view('dashboard', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'roleData' => $roleData,
            'isAgent' => $isAgent,
            'isSupervisorOrAdmin' => $isSupervisorOrAdmin,
            'isEmployee' => $isEmployee,
        ]);
    }
}
