<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public User $assigner
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Tiket Ditugaskan] {$this->ticket->ticket_number}: {$this->ticket->subject}")
            ->line("Tiket {$this->ticket->ticket_number} telah ditugaskan kepada Anda oleh {$this->assigner->name}.")
            ->line("Prioritas: {$this->ticket->priority->label()}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket))
            ->line('Terima kasih atas dedikasi Anda.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'priority' => $this->ticket->priority->value,
            'assigned_by' => $this->assigner->name,
            'message' => "Tiket {$this->ticket->ticket_number} ditugaskan kepada Anda oleh {$this->assigner->name}.",
            'url' => route('tickets.show', $this->ticket),
            'type' => 'ticket_assigned',
        ];
    }
}
