@extends('layouts.partial.layouts')

@section('page-title', 'Input Surat Pesanan Pembelian dari Pelanggan | TRANSDIGITAL')
@section('section-row')
    <div class="container mt-4">
        {{-- DAFTAR SPP PELANGGAN --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                <span>Daftar Surat Pesanan Pembelian (SPP)</span>

                <div>
                    <a href="{{ route('administrasi.spb.index') }}" class="btn btn-light btn-sm fw-bold me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-light btn-sm fw-bold" id="btn-toggle-form">
                        <i class="bi bi-plus-circle"></i> Buat SPP Baru
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="spp-penjualan-table" class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nomor Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tanggal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sppList as $index => $spp)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $spp->nomor_pesanan_penjualan }}</td>
                                    <td>{{ $spp->pelanggan->nama ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($spp->tanggal_pesanan_penjualan)->format('d-m-Y') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('administrasi.spb.spp-pelanggan.generatePdf', $spp->id) }}"
                                                class="btn btn-warning btn-sm" target="_blank" title="Unduh PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                            <form action="{{ route('administrasi.spb.spp-pelanggan.destroy', $spp->id) }}"
                                                method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <form action="{{ route('administrasi.spb.spp-pelanggan.store') }}" method="POST" enctype="multipart/form-data"
            id="spp-pelanggan-form" style="display: none;">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    Input Surat Pesanan Pembelian dari Pelanggan
                </div>
                <div class="card-body mt-3">
                    {{-- DATA PELANGGAN --}}
                    <h6 class="fw-bold">Data Pelanggan</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Pilih Pelanggan</label>
                            <select name="pelanggan_id" id="pelangganSelect" class="form-select" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach ($pelanggan as $p)
                                    <option value="{{ $p->id }}" data-alamat="{{ $p->alamat }}">
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat Pelanggan</label>
                            <input type="text" name="alamat_pelanggan" id="alamatPelanggan" class="form-control"
                                readonly>
                        </div>
                    </div>

                    {{-- DATA SPP --}}
                    <h6 class="fw-bold mt-4">Informasi Pesanan</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nomor Pesanan<span class="text-danger">*</span></label>
                            <input type="text" name="nomor_pesanan_pembelian" class="form-control"
                                placeholder="Contoh: PO-001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Pesanan<span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pesanan_pembelian" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Permintaan Tanggal Kirim<span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kirim_pesanan_pembelian" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Pihak Pemesan<span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control"
                                placeholder="Nama orang yang memesan" required>
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
                                            <option value="{{ $b->id }}"
                                                data-stok="{{ number_format($b->getSaldoAkhir(), 0, '.', '') }}"
                                                data-harga="{{ number_format($b->harga_jual_per_unit, 0, '.', '') }}"
                                                data-nama="{{ $b->nama }}">
                                                {{ $b->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="detail[0][nama_barang]" class="nama-barang">
                                </td>
                                <td><input type="text" class="form-control stok" readonly></td>
                                <td><input type="text" name="detail[0][kuantitas]" class="form-control qty" required>
                                </td>
                                <td><input type="text" name="detail[0][harga]" class="form-control harga" required>
                                </td>
                                <td><input type="text" name="detail[0][total]" class="form-control total" readonly>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-success btn-sm" id="add-row">
                        + Tambah Barang
                    </button>
                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('administrasi.spb.create') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke Buat SPB
                    </a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        Simpan Pesanan Pelanggan
                    </button>
                </div>

            </div>
        </form>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#spp-penjualan-table').DataTable();
        });

        // Toggle Form
        $('#btn-toggle-form').click(function() {
            let btn = $(this);
            $('#spp-pelanggan-form').slideToggle(300, function() {
                if ($(this).is(':visible')) {
                    btn.html('<i class="bi bi-x-circle"></i> Batal Buat SPP');
                    btn.removeClass('btn-light').addClass('btn-danger');
                } else {
                    btn.html('<i class="bi bi-plus-circle"></i> Buat SPP Baru');
                    btn.removeClass('btn-danger').addClass('btn-light');
                }
            });
        });

        // Delete Confirmation
        $(document).on('click', '.btn-delete', function() {
            let form = $(this).closest('form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data pesanan ini akan dihapus permanen beserta jurnal dan kartu gudang terkait!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Inisialisasi Select2
        $(document).ready(function() {
            $('.barang-select').select2({
                placeholder: '-- Pilih Barang --',
                allowClear: true,
                width: '100%'
            });
        });

        // Loading Button
        $('#submit-btn').click(function() {
            $(this).prop('disabled', true).html(
                '<i class="bi bi-spinner spinner-border spinner-border-sm"></i> Loading...');
            $('#spp-pelanggan-form').submit();
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
                            <option value="{{ $b->id }}"
                                data-stok="{{ number_format($b->getSaldoAkhir(), 0, '.', '') }}"
                                data-harga="{{ number_format($b->harga_jual_per_unit, 0, '.', '') }}"
                                data-nama="{{ $b->nama }}">
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
            row.find('.total').val(total ? formatRupiah(total.toString()) : '0');
        });

        // Hapus baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Auto set alamat dari pelanggan
        document.getElementById('pelangganSelect').addEventListener('change', function() {
            let alamat = this.options[this.selectedIndex].dataset.alamat ?? "";
            document.getElementById('alamatPelanggan').value = alamat;
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: "{{ session('success') }}",
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Oops! Ada kesalahan:',
                html: '<ul class="text-start">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            });
        @endif
    </script>
@endpush
