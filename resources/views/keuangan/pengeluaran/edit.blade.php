@extends('layouts.partial.layouts')
@section('page-title', 'Edit Pengeluaran Kas Perusahaan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Edit Pengeluaran Kas Perusahaan')
@section('section-row')
    <div class="card shadow-sm">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger text-center mx-auto w-75">
                    <h6 class="fw-bold mb-2">Terjadi Kesalahan:</h6>
                    <ul class="mb-0 text-start d-inline-block">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @php
                $currentJenisPengeluaran = old('jenis_pengeluaran', $jenisPengeluaran);
                $currentJenisKeperluan = old('jenis_keperluan', $jenisKeperluan);
                $currentKreditur = old('nama_kreditur', $namaKreditur);
                $currentHutangId = old('hutang_id', $selectedHutangId);
            @endphp

            <form action="{{ route('keuangan.pengeluaran.update', $entry->id) }}" method="POST" id="pengeluaran-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="uraian_pengeluaran" class="form-label">Uraian Pengeluaran Kas Perusahaan<span class="text-danger">*</span></label>
                    <input type="text" name="uraian_pengeluaran" id="uraian_pengeluaran"
                        class="form-control @error('uraian_pengeluaran') is-invalid @enderror"
                        value="{{ old('uraian_pengeluaran', $uraianPengeluaran) }}" required>
                    @error('uraian_pengeluaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal Transaksi<span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal"
                        class="form-control @error('tanggal') is-invalid @enderror"
                        value="{{ old('tanggal', $entry->date?->format('Y-m-d')) }}" required>
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_pengeluaran" class="form-label">Jenis Pengeluaran Kas Perusahaan<span class="text-danger">*</span></label>
                    <select name="jenis_pengeluaran" id="jenis_pengeluaran"
                        class="form-select @error('jenis_pengeluaran') is-invalid @enderror" required>
                        <option value="tunai" {{ $currentJenisPengeluaran === 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="kredit" {{ $currentJenisPengeluaran === 'kredit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                    @error('jenis_pengeluaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Keperluan<span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jenis_keperluan" id="membeli_barang"
                            value="membeli_barang" {{ $currentJenisKeperluan === 'membeli_barang' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="membeli_barang" id="label_membeli_barang">Membeli Barang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jenis_keperluan" id="membayar_hutang"
                            value="membayar_hutang" {{ $currentJenisKeperluan === 'membayar_hutang' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="membayar_hutang" id="label_membayar_hutang">Membayar Hutang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jenis_keperluan" id="lain_lain"
                            value="lain_lain" {{ $currentJenisKeperluan === 'lain_lain' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="lain_lain" id="label_lain_lain">Lain-lain (Diluar Kas Kecil)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="jenis_keperluan" id="kas_kecil"
                            value="kas_kecil" {{ $currentJenisKeperluan === 'kas_kecil' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="kas_kecil" id="label_kas_kecil">Pengisian Kas Kecil</label>
                    </div>
                </div>

                <div id="kreditur-section" class="{{ $currentJenisPengeluaran === 'kredit' ? '' : 'd-none' }} mb-3">
                    <hr>
                    <label for="nama_kreditur" class="form-label h5 mb-3">Detail Kreditur<span class="text-danger">*</span></label>
                    <select name="nama_kreditur" id="nama_kreditur" class="form-select"
                        {{ $currentJenisPengeluaran === 'kredit' ? 'required' : 'disabled' }}>
                        <option value="" disabled {{ $currentKreditur ? '' : 'selected' }}>-- Pilih Kreditur --</option>
                        @isset($kreditur)
                            @foreach ($kreditur as $supplier)
                                <option value="{{ $supplier->id }}" {{ $currentKreditur == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->nama }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div id="hutang-aktif-section" class="{{ $currentJenisKeperluan === 'membayar_hutang' ? '' : 'd-none' }} mb-3">
                    <label for="hutang_id" class="form-label">Pilih Hutang Aktif<span class="text-danger">*</span></label>
                    <select name="hutang_id" id="hutang_id" class="form-select @error('hutang_id') is-invalid @enderror">
                        <option value="" disabled {{ $currentHutangId ? '' : 'selected' }}>-- Pilih Hutang --</option>
                        @foreach ($listHutang as $hutang)
                            <option value="{{ $hutang->id }}" {{ $currentHutangId == $hutang->id ? 'selected' : '' }}>
                                {{ $hutang->uraian }} - {{ $hutang->pelanggan->nama ?? '-' }} - Rp {{ number_format($hutang->saldo, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('hutang_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="barang-section" class="{{ $currentJenisKeperluan === 'membeli_barang' ? '' : 'd-none' }} mb-3">
                    <label class="form-label">Barang Dibeli<span class="text-danger">*</span></label>
                    <div id="barang-list">
                        @if ($currentJenisKeperluan === 'membeli_barang' && $barangDibeli->isNotEmpty())
                            @foreach ($barangDibeli as $idx => $itemGudang)
                                @php
                                    $hargaBeli = $itemGudang->barang->harga_beli_per_unit ?? 0;
                                    $qty = (int) $itemGudang->diterima;
                                @endphp
                                <div class="row mb-2 align-items-end" data-index="{{ $idx }}">
                                    <div class="col-md-5">
                                        <select name="barang_dibeli[{{ $idx }}]" class="form-control barang-select" required>
                                            <option value="" disabled>-- Pilih Barang --</option>
                                            @foreach ($barang as $item)
                                                <option value="{{ $item->id }}" data-harga="{{ $item->harga_beli_per_unit }}"
                                                    {{ $itemGudang->barang_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->nama }} - Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }} - Saldo: {{ $item->getSaldoAkhir() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="jumlah_barang_dibeli[{{ $idx }}]" class="form-control jumlah-input" min="1" value="{{ $qty }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control subtotal-input" readonly value="{{ $hargaBeli * $qty }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-sm hapus-barang">x</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-2" id="tambah-barang">
                        <i class="bi bi-plus"></i> Tambah Barang
                    </button>
                </div>

                <div id="jumlah_manual_container" class="mb-3 {{ $currentJenisKeperluan !== 'membeli_barang' ? '' : 'd-none' }}">
                    <label for="jumlah_manual" class="form-label">Isi Jumlah Pengeluaran Kas Perusahaan Disini<span class="text-danger">*</span></label>
                    <input type="text" id="jumlah_manual" name="jumlah_manual" class="form-control"
                        value="{{ old('jumlah_manual', $jumlahManual) }}">
                </div>

                <div class="mb-3">
                    <label for="jumlah_pengeluaran" class="form-label">Total Pengeluaran Kas Perusahaan (Otomatis)<span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" id="jumlah_pengeluaran" class="form-control"
                        value="{{ old('jumlah', $totalKeluar) }}" readonly required>
                </div>

                <div class="mb-3">
                    <label for="potongan_pembelian" class="form-label">Potongan (Mengurangi Pengeluaran Kas Perusahaan)</label>
                    <input type="text" name="potongan_pembelian" id="potongan_pembelian" class="form-control"
                        value="{{ old('potongan_pembelian', $potonganPembelian) }}">
                </div>
                <div class="mb-3">
                    <label for="biaya_lain" class="form-label">Biaya Lain (Menambah Pengeluaran Kas Perusahaan)</label>
                    <input type="text" name="biaya_lain" id="biaya_lain" class="form-control"
                        value="{{ old('biaya_lain', $biayaLain) }}">
                </div>
                <div class="mb-3">
                    <label for="admin_bank" class="form-label">Admin Bank (Menambah Pengeluaran Kas Perusahaan)</label>
                    <input type="text" name="admin_bank" id="admin_bank" class="form-control"
                        value="{{ old('admin_bank', $adminBank) }}">
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('keuangan.pengeluaran.list') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" id="btn-submit">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('pengeluaran-form');
            const jenisPengeluaran = document.getElementById('jenis_pengeluaran');
            const krediturSection = document.getElementById('kreditur-section');
            const namaKreditur = document.getElementById('nama_kreditur');

            const jenisKeperluanRadios = document.querySelectorAll('input[name="jenis_keperluan"]');
            const barangSection = document.getElementById('barang-section');
            const barangList = document.getElementById('barang-list');
            const tambahBarangBtn = document.getElementById('tambah-barang');

            const jumlahManualContainer = document.getElementById('jumlah_manual_container');
            const jumlahManualInput = document.getElementById('jumlah_manual');
            const jumlahPengeluaran = document.getElementById('jumlah_pengeluaran');

            const potonganInput = document.getElementById('potongan_pembelian');
            const biayaLainInput = document.getElementById('biaya_lain');
            const bungaBankInput = document.getElementById('admin_bank');
            const hutangAktifSection = document.getElementById('hutang-aktif-section');

            let barangIndex = barangList.querySelectorAll('[data-index]').length;

            const barangTemplate = `
                <div class="row mb-2 align-items-end" data-index="{index}">
                    <div class="col-md-5">
                        <select name="barang_dibeli[{index}]" class="form-control barang-select" required>
                            <option value="" disabled selected>-- Pilih Barang --</option>
                            @foreach ($barang as $item)
                                <option value="{{ $item->id }}" data-harga="{{ $item->harga_beli_per_unit }}">
                                    {{ $item->nama }} - Rp {{ number_format($item->harga_beli_per_unit, 0, ',', '.') }} - Saldo: {{ $item->getSaldoAkhir() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="jumlah_barang_dibeli[{index}]" class="form-control jumlah-input" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control subtotal-input" readonly value="0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm hapus-barang">x</button>
                    </div>
                </div>
            `;

            function parseRupiah(value) {
                if (!value) return 0;
                return parseFloat(value.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
            }

            function toggleKrediturSection() {
                if (jenisPengeluaran.value === 'kredit') {
                    krediturSection.classList.remove('d-none');
                    namaKreditur.removeAttribute('disabled');
                    namaKreditur.setAttribute('required', 'required');
                } else {
                    krediturSection.classList.add('d-none');
                    namaKreditur.setAttribute('disabled', 'disabled');
                    namaKreditur.removeAttribute('required');
                }
            }

            function toggleSections() {
                const selected = document.querySelector('input[name="jenis_keperluan"]:checked')?.value;
                barangSection.classList.add('d-none');
                hutangAktifSection.classList.add('d-none');
                jumlahManualContainer.classList.add('d-none');

                if (selected === 'membeli_barang') {
                    barangSection.classList.remove('d-none');
                    barangSection.querySelectorAll('select, input').forEach(el => el.disabled = false);
                    if (barangList.children.length === 0) {
                        addBarangRow();
                    }
                } else {
                    barangSection.querySelectorAll('select, input').forEach(el => el.disabled = true);
                    if (selected === 'membayar_hutang') {
                        hutangAktifSection.classList.remove('d-none');
                    }
                    if (selected) {
                        jumlahManualContainer.classList.remove('d-none');
                    }
                }

                updateTotal();
            }

            function addBarangRow() {
                const rowHtml = barangTemplate.replace(/{index}/g, barangIndex++);
                barangList.insertAdjacentHTML('beforeend', rowHtml);
            }

            function updateTotal() {
                const selected = document.querySelector('input[name="jenis_keperluan"]:checked')?.value;
                let baseTotal = 0;

                if (selected === 'membeli_barang') {
                    barangList.querySelectorAll('.row').forEach(row => {
                        const select = row.querySelector('.barang-select');
                        const jumlah = parseFloat(row.querySelector('.jumlah-input').value) || 0;
                        const harga = parseFloat(select?.selectedOptions[0]?.dataset?.harga) || 0;
                        const subtotal = harga * jumlah;
                        row.querySelector('.subtotal-input').value = subtotal;
                        baseTotal += subtotal;
                    });
                } else {
                    baseTotal = parseRupiah(jumlahManualInput.value);
                }

                const potongan = parseRupiah(potonganInput.value);
                const biayaLain = parseRupiah(biayaLainInput.value);
                const adminBank = parseRupiah(bungaBankInput.value);

                jumlahPengeluaran.value = Math.max(baseTotal - potongan + biayaLain + adminBank, 0);
            }

            jenisPengeluaran.addEventListener('change', toggleKrediturSection);
            jenisKeperluanRadios.forEach(radio => radio.addEventListener('change', toggleSections));
            tambahBarangBtn.addEventListener('click', addBarangRow);

            barangList.addEventListener('change', updateTotal);
            barangList.addEventListener('input', updateTotal);
            barangList.addEventListener('click', function(e) {
                if (e.target.classList.contains('hapus-barang')) {
                    e.target.closest('.row').remove();
                    updateTotal();
                }
            });

            [jumlahManualInput, potonganInput, biayaLainInput, bungaBankInput].forEach(input => {
                input.addEventListener('input', updateTotal);
            });

            form.addEventListener('submit', function() {
                [jumlahManualInput, potonganInput, biayaLainInput, bungaBankInput].forEach(input => {
                    input.value = parseRupiah(input.value);
                });
            });

            toggleKrediturSection();
            toggleSections();
        });
    </script>
@endpush
