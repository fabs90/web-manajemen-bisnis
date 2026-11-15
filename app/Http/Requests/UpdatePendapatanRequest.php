<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\JournalItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePendapatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = fn ($val) => $val === null || $val === ''
            ? null
            : (int) round((float) str_replace(['Rp ', '.'], '', (string) $val));

        $this->merge([
            'jumlah' => $this->filled('jumlah') ? $clean($this->jumlah) : 0,
            'jumlah_kredit' => $this->filled('jumlah_kredit') ? $clean($this->jumlah_kredit) : null,
            'biaya_lain' => $this->filled('biaya_lain') ? $clean($this->biaya_lain) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jenis = $this->input('jenis_pendapatan');

        return [
            'uraian_pendapatan' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jenis_pendapatan' => [
                'required',
                Rule::in(['lain_lain', 'kredit']),
            ],
            'jumlah' => ['required', 'integer', 'gte:0'],
            'jumlah_kredit' => ['nullable', 'numeric', 'min:0'],
            'biaya_lain' => ['nullable', 'integer', 'min:0'],
            'nama_pelanggan' => [
                Rule::requiredIf(fn () => $jenis === 'kredit'),
                'nullable',
                'integer',
                'exists:pelanggan,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'uraian_pendapatan.required' => 'Uraian penerimaan wajib diisi.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'jenis_pendapatan.required' => 'Jenis penerimaan wajib dipilih.',
            'jenis_pendapatan.in' => 'Jenis penerimaan tidak valid.',
            'jumlah.required' => 'Jumlah penerimaan wajib diisi.',
            'jumlah.gte' => 'Jumlah penerimaan tidak boleh negatif.',
            'nama_pelanggan.required_if' => 'Debitur wajib dipilih untuk pelunasan piutang.',
            'nama_pelanggan.exists' => 'Debitur tidak ditemukan.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $jenis = $this->input('jenis_pendapatan');
            $userId = auth()->id();
            $entryId = (int) $this->route('id');

            $accounts = Account::where('user_id', $userId)->get()->keyBy('code');
            $piutangId = $accounts->has('1104') ? $accounts['1104']->id : null;

            if ($jenis === 'kredit' && $this->filled('nama_pelanggan') && $piutangId) {
                $balance = JournalItem::where('user_id', $userId)
                    ->where('account_id', $piutangId)
                    ->where('sub_ledger_id', $this->nama_pelanggan)
                    ->where('journal_entry_id', '!=', $entryId)
                    ->selectRaw('SUM(debit - credit) as saldo')
                    ->value('saldo');

                if ($balance === null || $balance <= 0) {
                    $validator->errors()->add('nama_pelanggan', 'Pelanggan ini tidak memiliki piutang aktif yang bisa dilunasi.');
                } else {
                    $kreditAmount = (float) $this->jumlah + (float) ($this->jumlah_kredit ?? 0);
                    if ($kreditAmount > (float) $balance) {
                        $validator->errors()->add(
                            'jumlah_kredit',
                            'Jumlah pelunasan (Rp '.number_format($kreditAmount, 0, ',', '.').') melebihi total piutang pelanggan (Rp '.number_format((float) $balance, 0, ',', '.').').'
                        );
                    }
                }
            }
        });
    }
}
