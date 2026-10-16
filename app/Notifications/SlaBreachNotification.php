<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $targetType // 'first_response' or 'resolution'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $targetLabel = $this->targetType === 'first_response' ? 'Respon Pertama' : 'Penyelesaian';
        $dueAt = $this->targetType === 'first_response'
            ? $this->ticket->first_response_due_at?->toIso8601String()
            : $this->ticket->resolution_due_at?->toIso8601String();

        return (new MailMessage)
            ->subject("[PELANGGARAN SLA] {$this->ticket->ticket_number}: {$this->ticket->subject}")
            ->error()
            ->line("PERINGATAN: Tiket {$this->ticket->ticket_number} telah melanggar batas waktu SLA ({$targetLabel}).")
            ->line("Batas waktu terlampaui: {$dueAt}")
            ->action('Eskalasi & Tangani Segera', route('tickets.show', $this->ticket))
            ->line('Tiket ini memerlukan perhatian mendesak dari tim IT Support.');
    }

    public function toArray(object $notifiable): array
    {
        $targetLabel = $this->targetType === 'first_response' ? 'Respon Pertama' : 'Penyelesaian';
        $dueAt = $this->targetType === 'first_response'
            ? $this->ticket->first_response_due_at?->toIso8601String()
            : $this->ticket->resolution_due_at?->toIso8601String();

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'target_type' => $this->targetType,
            'due_at' => $dueAt,
            'message' => "Pelanggaran SLA ({$targetLabel}) terjadi pada tiket {$this->ticket->ticket_number}.",
            'url' => route('tickets.show', $this->ticket),
            'type' => 'sla_breach',
        ];
    }
}
