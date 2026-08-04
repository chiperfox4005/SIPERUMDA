<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeminjamanRuanganRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ruangan_id' => 'required|exists:ruangans,id',
            'agenda_id' => 'nullable|exists:agendas,id',
            'tanggal_pemakaian' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'keperluan' => 'required|string',
            'jumlah_peserta' => 'required|integer|min:1',
        ];
    }
}