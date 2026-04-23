@extends('layouts.partial.layouts')

@section('page-title', 'Input Surat Pesanan Pembelian ke Supplier | Digitrans')
@section('section-row')
    <div class="container mt-4">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <form action="{{ route('administrasi.spp.store') }}" method="POST">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    Input Surat Pesanan Pembelian (SPP) ke Supplier
                </div>
                <div class="card-body mt-3">
                    {{-- DATA PELANGGAN --}}
                    <h6 class="fw-bold">Data Supplier</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pilih Supplier</label>
                            <select name="supplier_id" id="supplierSelect" class="form-select" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id }}" data-alamat="{{ $s->alamat }}">
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat Supplier</label>
                            <input type="text" id="alamatSuplier" class="form-control" readonly>
                        </div>
                    </div>

                    {{-- DATA SPP --}}
                    <h6 class="fw-bold mt-4">Informasi SPP</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nomor SPP<span class="text-danger">*</span></label>
                            <input type="text" name="nomor_pesanan_pembelian" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal SPP<span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pesanan_pembelian" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rencana Tanggal Kirim<span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kirim_pesanan_pembelian" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Bagian Pembelian<span class="text-danger">*</span></label>
                            <input type="text" name="nama_bagian_pembelian" class="form-control" required>
                        </div>
                    </div>

                    {{-- DETAIL BARANG --}}
                    <h6 class="fw-bold">Detail Barang<span class="text-danger">*</span></h6>

                    <table class="table table-bordered align-middle text-center" id="table-detail">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Nama Barang</th>
                                <th width="10%">Stok</th>
                                <th width="15%">Kuantitas</th>
                                <th width="15%">Harga</th>
                                <th width="20%">Total</th>
                                <th width="5%">#</th>
                            </tr>
                        </thead>

                        <tbody id="tbody-detail">
                            <tr>
                                <td class="row-index text-center">1</td>
                                <td>
                                    <select name="detail[0][barang_id]" class="form-select barang-select" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach ($barang as $b)
                                            <option value="{{ $b->id }}" data-stok="{{ $b->getSaldoAkhir() }}"
                                                data-harga="{{ $b->harga_beli_per_unit }}" data-nama="{{ $b->nama }}">
                                                {{ $b->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="detail[0][nama_barang]" class="nama-barang">
                                </td>
                                <td><input type="text" class="form-control stok" readonly></td>
                                <td><input type="text" name="detail[0][kuantitas]" class="form-control qty" required>
                                </td>
                                <td><input type="text" name="detail[0][harga]" class="form-control harga" required></td>
                                <td><input type="text" name="detail[0][total]" class="form-control total" readonly></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-success btn-sm" id="add-row">
                        + Tambah Barang
                    </button>
                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('administrasi.spp.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <input type="hidden" name="jenis" value="transaksi_keluar">
                    <button type="submit" class="btn btn-primary">
                        Simpan SPP
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        // Inisialisasi Select2
        $(document).ready(function() {
            $('.barang-select').select2({
                placeholder: '-- Pilih Barang --',
                allowClear: true,
                width: '100%'
            });
        });

        // Fungsi format angka menjadi format rupiah (tanpa Rp)
        function formatRupiah(angka) {
            let numberString = angka.replace(/[^,\d]/g, '').toString();
            let split = numberString.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Fungsi ambil angka numerik dari input rupiah
        function getNumericValue(value) {
            return parseInt(value.replace(/\./g, '').replace(/,/g, '')) || 0;
        }

        let index = 1;

        // Tambah baris
        document.getElementById('add-row').addEventListener('click', function() {
            let tbody = document.getElementById('tbody-detail');
            let row = document.createElement('tr');

            row.innerHTML = `
                <td class="row-index text-center">${index + 1}</td>
                <td>
                    <select name="detail[${index}][barang_id]" class="form-select barang-select" required>
                        <option value="">-- Pilih Barang --</option>
                        @foreach ($barang as $b)
                            <option value="{{ $b->id }}" data-stok="{{ $b->getSaldoAkhir() }}"
                                data-harga="{{ $b->harga_beli_per_unit }}" data-nama="{{ $b->nama }}">
                                {{ $b->nama }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="detail[${index}][nama_barang]" class="nama-barang">
                </td>
                <td><input type="text" class="form-control stok" readonly></td>
                <td><input type="text" name="detail[${index}][kuantitas]" class="form-control qty" required></td>
                <td><input type="text" name="detail[${index}][harga]" class="form-control harga" required></td>
                <td><input type="text" name="detail[${index}][total]" class="form-control total" readonly required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
            `;

            tbody.appendChild(row);

            // Re-inisialisasi Select2 untuk baris baru
            $(`.barang-select[name="detail[${index}][barang_id]"]`).select2({
                placeholder: '-- Pilih Barang --',
                allowClear: true,
                width: '100%'
            });

            index++;
        });

        // Event listener untuk perubahan barang
        $(document).on('change', '.barang-select', function() {
            let row = $(this).closest('tr');
            let selectedOption = $(this).find('option:selected');

            let stok = selectedOption.data('stok') || 0;
            let harga = selectedOption.data('harga') || 0;
            let nama = selectedOption.data('nama') || '';

            row.find('.stok').val(formatRupiah(stok.toString()));
            row.find('.harga').val(formatRupiah(harga.toString()));
            row.find('.nama-barang').val(nama);

            // Trigger input event to recalculate total
            row.find('.qty').trigger('input');
        });

        // Format & Hitung Total
        $(document).on('input', '.harga, .qty', function() {
            let val = $(this).val();
            $(this).val(formatRupiah(val));

            let row = $(this).closest('tr');
            let harga = getNumericValue(row.find('.harga').val());
            let qty = getNumericValue(row.find('.qty').val());

            let total = harga * qty;
            row.find('.total').val(total ? formatRupiah(total.toString()) : '');
        });

        // Hapus baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Auto set alamat dari supplier
        document.getElementById('supplierSelect').addEventListener('change', function() {
            let alamat = this.options[this.selectedIndex].dataset.alamat ?? "";
            document.getElementById('alamatSuplier').value = alamat;
        });
    </script>
@endpush
