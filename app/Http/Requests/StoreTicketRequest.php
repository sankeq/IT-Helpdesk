<?php

namespace App\Http\Requests;

use App\Enums\Impact;
use App\Enums\Urgency;
use App\Services\TicketService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('tickets.create');
    }

    public function rules(): array
    {
        $maxSize = TicketService::maxFileSizeKB();
        $allowedTypes = implode(',', TicketService::allowedExtensions());

        return [
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'impact' => ['required', Rule::enum(Impact::class)],
            'urgency' => ['required', Rule::enum(Urgency::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => [
                'file',
                "max:{$maxSize}",
                "mimes:{$allowedTypes}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Judul tiket wajib diisi.',
            'subject.max' => 'Judul tiket maksimal 150 karakter.',
            'description.required' => 'Deskripsi masalah wajib diisi.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            'category_id.required' => 'Kategori layanan wajib dipilih.',
            'category_id.exists' => 'Kategori layanan tidak valid.',
            'impact.required' => 'Tingkat dampak wajib dipilih.',
            'urgency.required' => 'Tingkat urgensi wajib dipilih.',
            'attachments.max' => 'Maksimal 5 lampiran.',
            'attachments.*.max' => 'Ukuran file maksimal '.(TicketService::maxFileSizeKB() / 1024).' MB.',
            'attachments.*.mimes' => 'Tipe file tidak diizinkan.',
        ];
    }
}
