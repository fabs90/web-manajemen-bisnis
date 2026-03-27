<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgendaRapatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ================= HEADER =================
            'nomor_surat_rapat' => 'required|string|max:255',
            'judul_rapat' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'nama_kota' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'waktu' => 'required|date_format:H:i',
            'pemimpin_rapat' => 'required|string|max:255',
            'notulis' => 'required|string|max:255',

            // ================= PESERTA =================
            'peserta_nama' => 'required|array|min:1',
            'peserta_nama.*' => 'required|string|max:255',

            'peserta_jabatan' => 'required|array|min:1',
            'peserta_jabatan.*' => 'required|string|max:255',

            'peserta_ttd' => 'nullable|array',
            'peserta_ttd.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // ================= AGENDA =================
            'agenda_rapat' => 'required|string',

            // ================= PEMBAHASAN =================
            'pembahasan_agenda' => 'required|array|min:1',
            'pembahasan_agenda.*' => 'required|string|max:255',

            'pembahasan_pembicara' => 'required|array|min:1',
            'pembahasan_pembicara.*' => 'required|string|max:255',

            'pembahasan_isi' => 'required|array|min:1',
            'pembahasan_isi.*' => 'required|string',

            // ================= KEPUTUSAN =================
            'keputusan_rapat' => 'required|string',

            // ================= TINDAK LANJUT =================
            'tindak_tindakan' => 'nullable|array',
            'tindak_tindakan.*' => 'nullable|string|max:255|required_with:tindak_pelaksana.*,tindak_target.*,tindak_status.*',

            'tindak_pelaksana' => 'nullable|array',
            'tindak_pelaksana.*' => 'nullable|string|max:255',

            'tindak_target' => 'nullable|array',
            'tindak_target.*' => 'nullable|date',

            'tindak_status' => 'nullable|array',
            'tindak_status.*' => 'nullable|string|max:255',

            // ================= RAPAT BERIKUTNYA =================
            'agenda_rapat_berikutnya' => 'nullable|string|max:255|required_with:tanggal_rapat_berikutnya,waktu_rapat_berikutnya',
            'tanggal_rapat_berikutnya' => 'nullable|date|required_with:agenda_rapat_berikutnya',
            'waktu_rapat_berikutnya' => 'nullable|date_format:H:i|required_with:agenda_rapat_berikutnya',
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor_surat_rapat' => 'nomor surat rapat',
            'judul_rapat' => 'judul rapat',
            'tempat' => 'tempat',
            'nama_kota' => 'nama kota',
            'tanggal' => 'tanggal',
            'waktu' => 'waktu',
            'pemimpin_rapat' => 'pemimpin rapat',
            'notulis' => 'notulis',

            'peserta_nama.*' => 'nama peserta',
            'peserta_jabatan.*' => 'jabatan peserta',
            'peserta_ttd.*' => 'tanda tangan peserta',

            'pembahasan_agenda.*' => 'agenda pembahasan',
            'pembahasan_pembicara.*' => 'pembicara',
            'pembahasan_isi.*' => 'isi pembahasan',

            'tindak_tindakan.*' => 'tindakan',
            'tindak_pelaksana.*' => 'pelaksana',
            'tindak_target.*' => 'target selesai',
            'tindak_status.*' => 'status',

            'agenda_rapat_berikutnya' => 'agenda rapat berikutnya',
            'tanggal_rapat_berikutnya' => 'tanggal rapat berikutnya',
            'waktu_rapat_berikutnya' => 'waktu rapat berikutnya',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_with' => ':attribute wajib diisi jika field terkait diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'date_format' => ':attribute harus dalam format yang valid.',
            'array' => ':attribute harus berupa array.',
            'min' => ':attribute minimal memiliki :min data.',
            'image' => ':attribute harus berupa file gambar.',
            'mimes' => ':attribute harus berformat: :values.',
        ];
    }
}
