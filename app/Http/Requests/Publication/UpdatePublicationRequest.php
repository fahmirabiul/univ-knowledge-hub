<?php

declare(strict_types=1);

namespace App\Http\Requests\Publication;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'doi' => ['nullable', 'string', 'max:255'],
            'isbn_issn' => ['nullable', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:500'],
            'abstract' => ['required', 'string'],
            'journal_name' => ['required', 'string', 'max:255'],
            'publication_year' => ['required', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            'volume' => ['nullable', 'string', 'max:50'],
            'issue' => ['nullable', 'string', 'max:50'],
            'pages' => ['nullable', 'string', 'max:50'],
            'is_public' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB (optional on update)
            'authors' => ['nullable', 'array'],
            'authors.*.user_id' => ['nullable', 'exists:users,id'],
            'authors.*.author_name' => ['required_with:authors', 'string', 'max:255'],
            'authors.*.author_email' => ['nullable', 'email', 'max:255'],
            'authors.*.affiliation' => ['nullable', 'string', 'max:255'],
            'authors.*.author_role' => ['nullable', 'string', 'in:penulis_pertama,pertama_korespondensi,korespondensi,pendamping'],
            'authors.*.author_order' => ['nullable', 'integer', 'min:1'],
            'authors.*.is_corresponding' => ['nullable', 'boolean'],
            'authors.*.is_external' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul publikasi ilmiah wajib diisi.',
            'abstract.required' => 'Abstrak publikasi wajib diisi.',
            'journal_name.required' => 'Nama jurnal atau penerbit wajib diisi.',
            'publication_year.required' => 'Tahun publikasi wajib diisi.',
            'file.mimes' => 'Format file dokumen harus berupa PDF.',
            'file.max' => 'Ukuran file dokumen PDF maksimal 10MB.',
            'category_id.exists' => 'Kategori bidang riset yang dipilih tidak valid.',
        ];
    }
}
