<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('tickets.assign');
    }

    public function rules(): array
    {
        return [
            'assignee_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignee_id.required' => 'Teknisi yang ditugaskan wajib dipilih.',
            'assignee_id.exists' => 'Teknisi yang dipilih tidak valid.',
        ];
    }
}
