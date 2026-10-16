<?php

namespace App\Http\Requests;

use App\Enums\PendingReason;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in TicketWorkflowService
    }

    public function rules(): array
    {
        $rules = [
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];

        $targetStatus = $this->input('status');

        if ($targetStatus === TicketStatus::Pending->value) {
            $rules['pending_reason'] = ['required', Rule::enum(PendingReason::class)];
            $rules['pending_note'] = ['nullable', 'string', 'max:2000'];
        }

        if ($targetStatus === TicketStatus::Resolved->value) {
            $rules['resolution_summary'] = ['required', 'string', 'max:5000'];
        }

        if (in_array($targetStatus, [TicketStatus::Reopened->value, TicketStatus::Cancelled->value])) {
            $rules['reason'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status tujuan wajib dipilih.',
            'pending_reason.required' => 'Alasan pending wajib diisi.',
            'pending_note.required' => 'Catatan pending wajib diisi untuk alasan "Lainnya".',
            'resolution_summary.required' => 'Catatan penyelesaian wajib diisi.',
            'reason.required' => 'Alasan wajib diisi.',
        ];
    }
}
