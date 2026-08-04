<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgendaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:rapat_internal,rapat_eksternal,kunjungan_dinas,seremoni,lainnya',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'lokasi' => 'nullable|string|max:150',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'peserta_ids' => 'required|array|min:1',
            'peserta_ids.*' => 'exists:users,id',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_selesai.after' => 'Tanggal selesai harus lebih besar dari tanggal mulai.',
            'dokumen_pendukung.max' => 'Ukuran dokumen maksimal 5MB.',
        ];
    }
}