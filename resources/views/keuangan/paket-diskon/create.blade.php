@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Paket Diskon | TRANSDIGITAL')
@section('section-heading', 'Tambah Paket Diskon Penjualan')
@section('section-row')
    <div class="card shadow-sm">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('keuangan.paket-diskon.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Paket Diskon <span class="text-danger">*</span></label>
                        <input type="text" name="nama_paket" class="form-control" value="{{ old('nama_paket') }}"
                            required placeholder="Contoh: Promo Lebaran">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Target Produk <span class="text-muted">(Opsional)</span></label>
                        <select name="barang_id" class="form-select">
                            <option value="">-- Berlaku untuk semua produk (Global) --</option>
                            @foreach ($barang as $b)
                                <option value="{{ $b->id }}" {{ old('barang_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->nama }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih barang jika diskon hanya untuk 1 produk khusus.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Diskon <span class="text-danger">*</span></label>
                        <select name="jenis_diskon" id="jenis_diskon" class="form-select" required>
                            <option value="persentase" {{ old('jenis_diskon') == 'persentase' ? 'selected' : '' }}>
                                Persentase (%)</option>
                            <option value="nominal" {{ old('jenis_diskon') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nilai Diskon <span class="text-danger">*</span></label>
                        <input type="text" name="nilai_diskon" id="nilai_diskon" class="form-control"
                            value="{{ old('nilai_diskon') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Minimal Pembelian (Rp) <span class="text-muted">(Opsional)</span></label>
                        <input type="text" name="minimal_pembelian" id="minimal_pembelian" class="form-control rupiah"
                            value="{{ old('minimal_pembelian', 0) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Status Aktif</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktifkan paket diskon ini</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <a href="{{ route('keuangan.paket-diskon.index') }}" class="btn btn-secondary me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Paket Diskon</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisDiskon = document.getElementById('jenis_diskon');
            const nilaiDiskon = document.getElementById('nilai_diskon');
            const rupiahInputs = document.querySelectorAll('.rupiah');

            function parseRupiah(value) {
                if (!value) return 0;
                return parseFloat(value.toString().replace(/[^,\d]/g, '').replace(/,/g, '.')) || 0;
            }

            function formatRupiah(angka) {
                if (isNaN(angka) || angka === 0) return '';
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function applyFormat() {
                if (jenisDiskon.value === 'nominal') {
                    let angka = parseRupiah(nilaiDiskon.value);
                    nilaiDiskon.value = angka === 0 ? '' : formatRupiah(angka);
                }
            }

            jenisDiskon.addEventListener('change', function() {
                nilaiDiskon.value = '';
            });

            nilaiDiskon.addEventListener('input', function(e) {
                if (jenisDiskon.value === 'nominal') {
                    let angka = parseRupiah(e.target.value);
                    e.target.value = angka === 0 ? '' : formatRupiah(angka);
                }
            });

            rupiahInputs.forEach(input => {
                input.addEventListener('input', (e) => {
                    let angka = parseRupiah(e.target.value);
                    e.target.value = angka === 0 ? '' : formatRupiah(angka);
                });

                if (input.value) {
                    let angka = parseRupiah(input.value);
                    input.value = angka === 0 ? '' : formatRupiah(angka);
                }
            });

            applyFormat();
        });
    </script>
@endpush
