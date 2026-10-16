<?php

namespace App\Enums;

enum HistoryEventType: string
{
    case TicketCreated = 'ticket_created';
    case StatusChanged = 'status_changed';
    case PriorityChanged = 'priority_changed';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case CommentAdded = 'comment_added';
    case AttachmentAdded = 'attachment_added';
    case AttachmentRemoved = 'attachment_removed';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Cancelled = 'cancelled';
    case ApprovalRequested = 'approval_requested';
    case ApprovalDecided = 'approval_decided';

    public function label(): string
    {
        return match ($this) {
            self::TicketCreated => 'Tiket dibuat',
            self::StatusChanged => 'Status diubah',
            self::PriorityChanged => 'Prioritas diubah',
            self::Assigned => 'Ditugaskan',
            self::Reassigned => 'Dialihkan',
            self::CommentAdded => 'Komentar ditambahkan',
            self::AttachmentAdded => 'Lampiran ditambahkan',
            self::AttachmentRemoved => 'Lampiran dihapus',
            self::Resolved => 'Diselesaikan',
            self::Closed => 'Ditutup',
            self::Reopened => 'Dibuka kembali',
            self::Cancelled => 'Dibatalkan',
            self::ApprovalRequested => 'Persetujuan diajukan',
            self::ApprovalDecided => 'Persetujuan diputuskan',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TicketCreated => 'plus-circle',
            self::StatusChanged => 'arrow-path',
            self::PriorityChanged => 'flag',
            self::Assigned, self::Reassigned => 'user',
            self::CommentAdded => 'chat-bubble-left',
            self::AttachmentAdded => 'paper-clip',
            self::AttachmentRemoved => 'trash',
            self::Resolved => 'check-circle',
            self::Closed => 'lock-closed',
            self::Reopened => 'lock-open',
            self::Cancelled => 'x-circle',
            self::ApprovalRequested => 'clock',
            self::ApprovalDecided => 'shield-check',
        };
    }
}
