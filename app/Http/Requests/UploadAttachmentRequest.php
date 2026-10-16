<?php

namespace App\Http\Requests;

use App\Services\TicketService;
use Illuminate\Foundation\Http\FormRequest;

class UploadAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = TicketService::maxFileSizeKB();
        $allowedTypes = implode(',', TicketService::allowedExtensions());

        return [
            'attachment' => ['required', 'file', "max:{$maxSize}", "mimes:{$allowedTypes}"],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.required' => 'File lampiran wajib dipilih.',
            'attachment.max' => 'Ukuran file terlalu besar.',
            'attachment.mimes' => 'Tipe file tidak diizinkan.',
        ];
    }
}
