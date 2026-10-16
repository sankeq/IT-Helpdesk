<?php

namespace App\Http\Requests;

use App\Enums\CommentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policy
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', Rule::enum(CommentVisibility::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Isi komentar wajib diisi.',
            'body.max' => 'Komentar maksimal 5000 karakter.',
            'visibility.required' => 'Tipe komentar wajib dipilih.',
        ];
    }
}
