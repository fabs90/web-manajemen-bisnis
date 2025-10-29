@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Aset dan Hutang')
@section('section-heading', 'Form Tambah Aset & Hutang')
@section('section-row')
    <p>
        Silakan isi form di bawah untuk menambahkan data aset dan hutang perusahaan <b>{{ $user }}</b>.
    </p>
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan pada input:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- Form --}}
    <form action="{{ route('aset-hutang.store') }}" method="POST">
        @csrf
        {{-- Aset --}}
        <legend>Aset Lancar</legend>
        <div class="mb-3">
            <label for="kas" class="form-label">Kas/Bank<span class="text-danger">*</span></label>
            <input type="text" class="form-control rupiah @error('kas') is-invalid @enderror" id="kas" name="kas"
                placeholder="Rp 0" value="{{ old('kas') }}" autocomplete="off">
            @error('kas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="uraian_kas" class="form-label">Uraian Kas<span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('uraian_kas') is-invalid @enderror" id="uraian_kas"
                name="uraian_kas" placeholder="Contoh: Saldo awal" value="{{ old('uraian_kas') }}" autocomplete="off">
            @error('uraian_kas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Piutang --}}
        <div id="piutang-section">
            <hr>
            <legend>Piutang Dagang <span class="text-danger">*</span></legend>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="tidak_ada_piutang" name="tidak_ada_piutang"
                    value="1">
                <label class="form-check-label" for="tidak_ada_piutang">
                    Tidak ada piutang sama sekali
                </label>
            </div>

            <p>Tidak ada nama debitur? <a href="{{ route('debitur-kreditur.create') }}" target="_blank">Tambah Data Debitur
                    Disini</a>
            </p>
            <div id="piutang-wrapper">
                <div class="piutang-item border rounded p-3 mb-3">
                    <div class="mb-2">
                        <label class="form-label">Pilih Nama Debitur</label>
                        <select name="piutang[0][nama]" class="form-select @error('piutang.0.nama') is-invalid @enderror">
                            <option value="" disabled selected>--Pilih Debitur--</option>
                            @foreach ($debitur as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('piutang.0.nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Jumlah Piutang</label>
                        <input type="text" name="piutang[0][jumlah]"
                            class="form-control rupiah  @error('piutang.0.jumlah') is-invalid @enderror" placeholder="Rp 0"
                            autocomplete="off">
                        @error('piutang.0.jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Uraian</label>
                        <input type="text" name="piutang[0][uraian]"
                            class="form-control @error('piutang.0.uraian') is-invalid @enderror" placeholder="Uraian">
                        @error('piutang.0.uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jatuh Tempo</label>
                        <input type="date" name="piutang[0][jatuh_tempo_piutang]"
                            class="form-control  @error('piutang.0.jatuh_tempo_piutang') is-invalid @enderror">
                        @error('piutang.0.jatuh_tempo_piutang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-piutang">Hapus</button>
                </div>
            </div>
            <button type="button" id="add-piutang" class="btn btn-success mb-4">+ Tambah Piutang</button>
        </div>
        {{-- Hutang --}}
        <div id="hutang-section">
            <hr>
            <legend>Hutang Dagang <span class="text-danger">*</span></legend>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="tidak_ada_hutang" name="tidak_ada_hutang"
                    value="1">
                <label class="form-check-label" for="tidak_ada_hutang">
                    Tidak ada hutang sama sekali
                </label>
            </div>
            <p>Tidak ada nama kreditur? <a href="{{ route('debitur-kreditur.create') }}" target="_blank">Tambah Data Kreditur
                    Disini</a>
            </p>
            <div id="hutang-wrapper">
                <div class="hutang-item border rounded p-3 mb-3">
                    <div class="mb-2">
                        <label class="form-label">Pilih Nama Kreditur</label>
                        <select name="hutang[0][nama]" class="form-select @error('hutang.0.nama') is-invalid @enderror">
                            <option value="" disabled selected>--Pilih Kreditur--</option>
                            @foreach ($kreditur as $d)
                                <option value="{{ $d->id }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('hutang.0.nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jumlah Hutang</label>
                        <input type="text" name="hutang[0][jumlah]"
                            class="form-control rupiah @error('hutang.0.jumlah') is-invalid @enderror" placeholder="Rp 0"
                            autocomplete="off">
                        @error('hutang.0.jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Uraian</label>
                        <input type="text" name="hutang[0][uraian]"
                            class="form-control @error('hutang.0.uraian') is-invalid @enderror" placeholder="Uraian">
                        @error('hutang.0.uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jatuh Tempo</label>
                        <input type="date" name="hutang[0][jatuh_tempo_hutang]"
                            class="form-control @error('hutang.0.jatuh_tempo_hutang') is-invalid @enderror">
                        @error('hutang.0.jatuh_tempo_hutang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-hutang">Hapus</button>
                </div>
            </div>
            <button type="button" id="add-hutang" class="btn btn-success mb-4">+ Tambah Hutang</button>
        </div>

        {{-- Persediaan Barang Dagangan --}}
        <hr>
        <legend>Persediaan Barang Dagangan<span class="text-danger">*</span></legend>

        <div class="mb-3">
            <p>
                Tidak ada nama barang?
                <a href="{{ route('barang.create') }}" target="_blank">Tambah Data Barang Disini</a>
            </p>
            <select id="barang_select" name="barang_ids[]" class="form-select @error('barang_ids') is-invalid @enderror"
                multiple>
                @foreach ($barang as $b)
                    <option value="{{ $b->id }}" {{ in_array($b->id, old('barang_ids', [])) ? 'selected' : '' }}>
                        {{ $b->nama }}
                    </option>
                @endforeach
            </select>

            @error('barang_ids')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            <small class="text-muted">Pilih satu atau lebih barang untuk menghitung total nilai persediaan.</small>
        </div>


        <table class="table table-bordered" id="tabel-persediaan" style="display:none;">
            <thead class="table-light">
                <tr>
                    <th>Nama Barang</th>
                    <th>Harga per Kemas</th>
                    <th>Saldo per Kemas</th>
                    <th>Total Nilai</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total Keseluruhan</th>
                    <th id="grand-total">Rp 0</th>
                </tr>
            </tfoot>
        </table>

        {{-- Aset Tetap --}}
        <hr>
        <legend>Aset Tetap</legend>
        <div class="mb-3">
            <label for="tanah_bangunan" class="form-label">Tanah dan Bangunan<span class="text-danger">*</span></label>
            <input type="text" class="form-control rupiah @error('tanah_bangunan') is-invalid @enderror"
                id="tanah_bangunan" name="tanah_bangunan" placeholder="Rp 0" value="{{ old('tanah_bangunan') }}"
                autocomplete="off">
            @error('tanah_bangunan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="kendaraan" class="form-label">Kendaraan<span class="text-danger">*</span></label>
            <input type="text" class="form-control rupiah @error('kendaraan') is-invalid @enderror" id="kendaraan"
                name="kendaraan" placeholder="Rp 0" value="{{ old('kendaraan') }}" autocomplete="off">
            @error('kendaraan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="meubel_peralatan" class="form-label">Meubel & Peralatan<span class="text-danger">*</span></label>
            <input type="text" class="form-control rupiah @error('meubel_peralatan') is-invalid @enderror"
                id="meubel_peralatan" name="meubel_peralatan" placeholder="Rp 0" value="{{ old('meubel_peralatan') }}"
                autocomplete="off">
            @error('meubel_peralatan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <input type="hidden" id="total_persediaan" name="total_persediaan" value="0">
    </form>


@endsection


@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const options = {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                unformatOnSubmit: true,
                currencySymbol: 'Rp ',
                currencySymbolPlacement: 'p',
                minimumValue: '0',
            };

            function initRupiahFields() {
                document.querySelectorAll('.rupiah').forEach(el => {
                    if (!el.hasAttribute('data-autonumeric-initialized')) {
                        new AutoNumeric(el, options);
                        el.setAttribute('data-autonumeric-initialized', 'true');
                    }
                });
            }

            initRupiahFields();

            // Konfirmasi sebelum submit form
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Simpan data?',
                    text: 'Pastikan semua data sudah benar sebelum disimpan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // kirim form jika dikonfirmasi
                    }
                });
            });


            // Tambah piutang baru
            let piutangIndex = 1;
            document.getElementById('add-piutang').addEventListener('click', function() {
                const wrapper = document.getElementById('piutang-wrapper');
                const newItem = wrapper.firstElementChild.cloneNode(true);

                newItem.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    input.name = input.name.replace(/\[\d+\]/, `[${piutangIndex}]`);
                });

                wrapper.appendChild(newItem);
                initRupiahFields();
                piutangIndex++;
            });

            // Hapus piutang item
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-piutang')) {
                    const items = document.querySelectorAll('.piutang-item');
                    if (items.length > 1) e.target.closest('.piutang-item').remove();
                }
            });
            // Tambah hutang baru
            // Tambah hutang baru
            let hutangIndex = 1;
            document.getElementById('add-hutang').addEventListener('click', function() {
                const wrapper = document.getElementById('hutang-wrapper');
                const newItem = wrapper.firstElementChild.cloneNode(true);

                // Reset input & perbarui name
                newItem.querySelectorAll('input, select').forEach(el => {
                    el.value = '';
                    el.name = el.name.replace(/\[\d+\]/, `[${hutangIndex}]`);
                    el.disabled = false; // pastikan tidak ada yang disable
                });

                // Hapus pesan error lama
                newItem.querySelectorAll('.invalid-feedback').forEach(e => e.remove());
                newItem.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));

                wrapper.appendChild(newItem);
                initRupiahFields();
                hutangIndex++;
            });


            // Hapus hutang item
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-hutang')) {
                    const items = document.querySelectorAll('.hutang-item');
                    if (items.length > 1) e.target.closest('.hutang-item').remove();
                }
            });

            // Inisialisasi Select2
            $('#barang_select').on('change', function() {
                const selectedIds = $(this).val() || [];
                tbody.innerHTML = ''; // reset tabel

                let grandTotal = 0;

                selectedIds.forEach(id => {
                    const b = barangData.find(x => x.id == id);
                    const kartu = kartuGudang.find(k => k.barang_id == id);
                    const harga = b?.harga_beli_per_kemas || 0;
                    const saldo = kartu?.saldo_perkemasan || 0;
                    const total = harga * saldo;
                    grandTotal += total;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${b?.nama ?? '-'}</td>
                        <td>Rp ${harga.toLocaleString('id-ID')}</td>
                        <td>${saldo}</td>
                        <td>Rp ${total.toLocaleString('id-ID')}</td>
                    `;
                    tbody.appendChild(row);
                });

                // tampilkan / sembunyikan tabel
                table.style.display = selectedIds.length ? 'table' : 'none';
                grandTotalCell.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

                // simpan total ke hidden input agar bisa dikirim ke backend
                document.getElementById('total_persediaan').value = grandTotal;
            });

            const barangData = @json($barang);
            const kartuGudang = @json($kartuGudang);
            const table = document.getElementById("tabel-persediaan");
            const tbody = table.querySelector("tbody");
            const grandTotalCell = document.getElementById("grand-total");

            $('#barang_select').on('change', function() {
                const selectedIds = $(this).val() || [];
                tbody.innerHTML = ''; // reset tabel

                let grandTotal = 0;

                selectedIds.forEach(id => {
                    const b = barangData.find(x => x.id == id);
                    const kartu = kartuGudang.find(k => k.barang_id == id);
                    const harga = b?.harga_beli_per_kemas || 0;
                    const saldo = kartu?.saldo_perkemasan || 0;
                    const total = harga * saldo;
                    grandTotal += total;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                           <td>${b?.nama ?? '-'}</td>
                           <td>Rp ${harga.toLocaleString('id-ID')}</td>
                           <td>${saldo}</td>
                           <td>Rp ${total.toLocaleString('id-ID')}</td>
                       `;
                    tbody.appendChild(row);
                });

                // tampilkan / sembunyikan tabel
                table.style.display = selectedIds.length ? 'table' : 'none';
                grandTotalCell.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
            });

            // === Toggle Piutang Section ===
            const tidakAdaPiutang = document.getElementById('tidak_ada_piutang');
            const piutangWrapper = document.getElementById('piutang-wrapper');

            tidakAdaPiutang.addEventListener('change', function() {
                if (this.checked) {
                    piutangWrapper.classList.add('disabled-section');
                    piutangWrapper.querySelectorAll('input, select, button').forEach(el => {
                        el.disabled = true;
                    });
                } else {
                    piutangWrapper.classList.remove('disabled-section');
                    piutangWrapper.querySelectorAll('input, select, button').forEach(el => {
                        el.disabled = false;
                    });
                }
            });

            // === Toggle Hutang Section ===
            const tidakAdaHutang = document.getElementById('tidak_ada_hutang');
            const hutangWrapper = document.getElementById('hutang-wrapper');

            tidakAdaHutang.addEventListener('change', function() {
                if (this.checked) {
                    hutangWrapper.classList.add('disabled-section');
                    hutangWrapper.querySelectorAll('input, select, button').forEach(el => {
                        el.disabled = true;
                    });
                } else {
                    hutangWrapper.classList.remove('disabled-section');
                    hutangWrapper.querySelectorAll('input, select, button').forEach(el => {
                        el.disabled = false;
                    });
                }
            });

            // Hilangkan pesan error saat input difokuskan
            document.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.addEventListener('focus', function() {
                    // hapus class error visual
                    this.classList.remove('is-invalid');

                    // hapus pesan error di bawahnya
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                });
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2500,
                    toast: true,
                    position: 'top-end',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            @endif

            @if (session('errors'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Terjadi kesalahan pada input. Silakan periksa kembali data Anda.',
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            @endif

        });
    </script>
@endpush
