<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

/**
 * Authorization policy for ticket operations.
 * Implements ROLE_PERMISSION_MATRIX.md rules.
 */
class TicketPolicy
{
    /**
     * Determine if the user can view the tickets list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tickets.view_own')
            || $user->hasPermission('tickets.view_assigned')
            || $user->hasPermission('tickets.view_all');
    }

    /**
     * Determine if the user can view a specific ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasPermission('tickets.view_all')) {
            return true;
        }

        if ($user->hasPermission('tickets.view_assigned') && $ticket->isAssignedTo($user)) {
            return true;
        }

        return $ticket->isOwnedBy($user);
    }

    /**
     * Determine if the user can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('tickets.create');
    }

    /**
     * Determine if the user can update a ticket's fields.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed || $ticket->status === TicketStatus::Cancelled) {
            return false;
        }

        if ($user->hasPermission('tickets.view_all')) {
            return true;
        }

        return $ticket->isAssignedTo($user);
    }

    /**
     * Determine if the user can assign/reassign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('tickets.assign');
    }

    /**
     * Determine if the user can transition the ticket status.
     */
    public function transition(User $user, Ticket $ticket): bool
    {
        // At minimum, the user must be the requester, assignee, or have elevated permissions
        if ($ticket->isOwnedBy($user) || $ticket->isAssignedTo($user)) {
            return true;
        }

        return $user->hasPermission('tickets.assign')
            || $user->hasPermission('tickets.resolve')
            || $user->hasPermission('tickets.close')
            || $user->hasPermission('tickets.reopen');
    }

    /**
     * Determine if the user can add a public comment.
     */
    public function commentPublic(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed || $ticket->status === TicketStatus::Cancelled) {
            return false;
        }

        if ($ticket->isOwnedBy($user) && $user->hasPermission('tickets.comment_public')) {
            return true;
        }

        if ($ticket->isAssignedTo($user)) {
            return true;
        }

        return $user->hasPermission('tickets.view_all');
    }

    /**
     * Determine if the user can add an internal note.
     */
    public function commentInternal(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('tickets.comment_internal');
    }

    /**
     * Determine if the user can view internal notes.
     */
    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('tickets.comment_internal');
    }

    /**
     * Determine if the user can upload attachments.
     */
    public function uploadAttachment(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === TicketStatus::Closed || $ticket->status === TicketStatus::Cancelled) {
            return false;
        }

        return $ticket->isOwnedBy($user) || $ticket->isAssignedTo($user)
            || $user->hasPermission('tickets.view_all');
    }

    /**
     * Determine if the user can download attachments.
     */
    public function downloadAttachment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    /**
     * Determine if the user can change ticket priority.
     */
    public function changePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('tickets.change_priority');
    }
}
