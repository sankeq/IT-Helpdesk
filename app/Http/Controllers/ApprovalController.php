<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalStatus;
use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalService $approvalService,
    ) {}

    /**
     * Display list of pending and recent approvals.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ensure user is authorized to view approvals
        if (! $user->hasPermission('approvals.decide') && ! $user->hasPermission('tickets.view_all')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka modul persetujuan.');
        }

        $pendingApprovals = $this->approvalService->getPendingApprovals($user);

        // Fetch recent decided approvals for audit & transparency
        $decidedApprovals = Approval::with(['ticket.category', 'ticket.requester.department', 'requester', 'approver'])
            ->whereIn('status', [ApprovalStatus::Approved, ApprovalStatus::Rejected])
            ->latest('decided_at')
            ->take(10)
            ->get();

        return view('approvals.index', compact('pendingApprovals', 'decidedApprovals'));
    }

    /**
     * Submit an approval decision (Approve or Reject).
     */
    public function decide(Request $request, Approval $approval): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('approvals.decide') && $approval->approver_id !== $user->id) {
            abort(403, 'Anda tidak memiliki wewenang untuk memutuskan persetujuan ini.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $decision = ApprovalStatus::from($validated['decision']);

        if ($decision === ApprovalStatus::Rejected && empty(trim((string) ($validated['reason'] ?? '')))) {
            return back()->withErrors(['reason' => 'Alasan penolakan wajib diisi.'])->withInput();
        }

        $this->approvalService->decide($approval, $user, $decision, $validated['reason'] ?? null);

        $actionText = $decision === ApprovalStatus::Approved ? 'disetujui' : 'ditolak';

        return back()->with('success', "Permintaan persetujuan untuk tiket {$approval->ticket->ticket_number} berhasil {$actionText}.");
    }
}
