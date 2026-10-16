<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $targetType, // 'first_response' or 'resolution'
        public int $percentage = 75
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
            ->subject("[Peringatan SLA {$this->percentage}%] {$this->ticket->ticket_number}: {$this->ticket->subject}")
            ->line("Tiket {$this->ticket->ticket_number} telah mencapai {$this->percentage}% batas waktu {$targetLabel}.")
            ->line("Batas waktu: {$dueAt}")
            ->action('Buka Tiket', route('tickets.show', $this->ticket))
            ->line('Mohon segera tindak lanjuti untuk mencegah pelanggaran SLA.');
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
            'percentage' => $this->percentage,
            'due_at' => $dueAt,
            'message' => "Peringatan SLA {$this->percentage}% untuk target {$targetLabel} pada tiket {$this->ticket->ticket_number}.",
            'url' => route('tickets.show', $this->ticket),
            'type' => 'sla_warning',
        ];
    }
}
