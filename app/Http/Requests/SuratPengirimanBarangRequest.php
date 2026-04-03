<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SuratPengirimanBarangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $butuhKonfirmasi = in_array($this->status_pengiriman, ['diterima', 'dikembalikan']);

        return [
            // ── SPB Info ────────────────────────────────────────────────
            'spp_id' => ['required', 'exists:pesanan_pembelian,id'],
            'nomor_pengiriman_barang' => ['required', 'string'],
            'tanggal_pengiriman' => ['required', 'date'],
            'status_pengiriman' => ['required', 'in:diproses,dikirim,diterima,dibatalkan,dikembalikan'],
            'jenis_pengiriman' => ['required', 'string', 'max:100'],
            'nama_pengirim' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],

            // ── Konfirmasi (wajib saat diterima / dikembalikan) ─────────
            'tanggal_terima' => $butuhKonfirmasi ? ['required', 'date'] : ['nullable', 'date'],
            'keadaan' => $butuhKonfirmasi ? ['required', 'in:baik,rusak_ringan,rusak_berat'] : ['nullable', 'string'],
            'nama_penerima' => $butuhKonfirmasi ? ['required', 'string', 'max:100'] : ['nullable', 'string'],

            // ── Items ────────────────────────────────────────────────────
            'items' => ['required', 'array', 'min:1'],
            'items.*.spp_detail_id' => ['required', 'exists:pesanan_pembelian_detail,id'],
            'items.*.jumlah_dikirim' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            // ── SPB Info ────────────────────────────────────────────────
            'spp_id.required' => 'Pesanan Pembelian (SPP) wajib dipilih.',
            'spp_id.exists' => 'SPP yang dipilih tidak valid.',
            'nomor_pengiriman_barang.required' => 'Nomor SPB wajib diisi.',
            'nomor_pengiriman_barang.unique' => 'Nomor SPB sudah digunakan.',
            'tanggal_pengiriman.required' => 'Tanggal pengiriman wajib diisi.',
            'tanggal_pengiriman.date' => 'Format tanggal pengiriman tidak valid.',
            'status_pengiriman.required' => 'Status pengiriman wajib dipilih.',
            'status_pengiriman.in' => 'Status pengiriman tidak valid.',
            'jenis_pengiriman.required' => 'Jenis pengiriman wajib diisi.',

            // ── Konfirmasi ───────────────────────────────────────────────
            'tanggal_terima.required' => 'Tanggal terima wajib diisi.',
            'tanggal_terima.date' => 'Format tanggal terima tidak valid.',
            'keadaan.required' => 'Keadaan barang wajib dipilih.',
            'keadaan.in' => 'Keadaan barang tidak valid.',
            'nama_penerima.required' => 'Nama penerima wajib diisi.',

            // ── Items ────────────────────────────────────────────────────
            'items.required' => 'Minimal satu barang harus ada.',
            'items.min' => 'Minimal satu barang harus ada.',
            'items.*.spp_detail_id.required' => 'Detail barang tidak valid.',
            'items.*.spp_detail_id.exists' => 'Detail barang tidak ditemukan.',
            'items.*.jumlah_dikirim.required' => 'Jumlah dikirim wajib diisi.',
            'items.*.jumlah_dikirim.integer' => 'Jumlah dikirim harus berupa angka.',
            'items.*.jumlah_dikirim.min' => 'Jumlah dikirim tidak boleh negatif.',
        ];
    }

    public function attributes(): array
    {
        return [
            'spp_id' => 'Pesanan Pembelian',
            'nomor_pengiriman_barang' => 'Nomor SPB',
            'tanggal_pengiriman' => 'Tanggal Pengiriman',
            'status_pengiriman' => 'Status Pengiriman',
            'jenis_pengiriman' => 'Jenis Pengiriman',
            'nama_pengirim' => 'Nama Pengirim',
            'tanggal_terima' => 'Tanggal Terima',
            'keadaan' => 'Keadaan Barang',
            'nama_penerima' => 'Nama Penerima',
            'keterangan' => 'Keterangan',
        ];
    }
}