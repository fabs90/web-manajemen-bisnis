@extends('layouts.partial.layouts')

@section('page-title', 'Buat Agenda Perjalanan | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
    <div class="container mt-4">
        {{-- Alert sukses --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Sukses!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Alert error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-header bg-primary text-white fw-bold">
                Form Agenda Perjalanan
            </div>
            <div class="card-body mt-3">
                <form action="{{ route('administrasi.agenda-perjalanan.store') }}" method="POST">
                    @csrf

                    {{-- HEADER INFO --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nama Pelaksana</label>
                            <input type="text" name="nama_pelaksana"
                                class="form-control @error('nama_pelaksana') is-invalid @enderror"
                                value="{{ old('nama_pelaksana') }}" required>
                            @error('nama_pelaksana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Jabatan</label>
                            <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror"
                                value="{{ old('jabatan') }}" required>
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tujuan</label>
                            <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror"
                                value="{{ old('tujuan') }}" required>
                            @error('tujuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai"
                                class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                value="{{ old('tanggal_mulai') }}" required>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai"
                                class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                value="{{ old('tanggal_selesai') }}" required>
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label>Keperluan</label>
                            <textarea name="keperluan" class="form-control @error('keperluan') is-invalid @enderror" rows="2" required>{{ old('keperluan') }}</textarea>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- JADWAL DETAIL (DINAMIS) --}}
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">JADWAL DETAIL</h5>
                        <div>
                            <button type="button" id="btn-add-day" class="btn btn-sm btn-success">+ Tambah Hari</button>
                        </div>
                    </div>

                    <div id="days-container">
                        {{-- Render old days if validation failed, otherwise render one default day --}}
                        @php
                            $oldDays = old('jadwal', null);
                        @endphp

                        @if ($oldDays && is_array($oldDays) && count($oldDays) > 0)
                            @foreach ($oldDays as $dIndex => $d)
                                <div class="card mb-3 day-card" data-day-index="{{ $dIndex }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6>Hari {{ $loop->iteration }} - <small>Tanggal: </small></h6>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-day">Hapus
                                                Hari</button>
                                        </div>

                                        <div class="mb-3">
                                            <input type="date" name="jadwal[{{ $dIndex }}][tanggal]"
                                                class="form-control @error('jadwal.' . $dIndex . '.tanggal') is-invalid @enderror"
                                                value="{{ old('jadwal.' . $dIndex . '.tanggal', $d['tanggal'] ?? '') }}">
                                            @error('jadwal.' . $dIndex . '.tanggal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- items --}}
                                        <div class="items-container">
                                            @if (isset($d['items']) && is_array($d['items']))
                                                @foreach ($d['items'] as $iIndex => $item)
                                                    <div class="row mb-2 item-row" data-item-index="{{ $iIndex }}">
                                                        <div class="col-md-2">
                                                            <input type="time"
                                                                name="jadwal[{{ $dIndex }}][items][{{ $iIndex }}][waktu]"
                                                                class="form-control @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.waktu') is-invalid @enderror"
                                                                value="{{ old('jadwal.' . $dIndex . '.items.' . $iIndex . '.waktu', $item['waktu'] ?? '') }}">
                                                            @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.waktu')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-7">
                                                            <input type="text"
                                                                name="jadwal[{{ $dIndex }}][items][{{ $iIndex }}][kegiatan]"
                                                                class="form-control @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.kegiatan') is-invalid @enderror"
                                                                placeholder="Kegiatan"
                                                                value="{{ old('jadwal.' . $dIndex . '.items.' . $iIndex . '.kegiatan', $item['kegiatan'] ?? '') }}">
                                                            @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.kegiatan')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="input-group">
                                                                <input type="text"
                                                                    name="jadwal[{{ $dIndex }}][items][{{ $iIndex }}][lokasi]"
                                                                    class="form-control @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.lokasi') is-invalid @enderror"
                                                                    placeholder="Lokasi / PIC"
                                                                    value="{{ old('jadwal.' . $dIndex . '.items.' . $iIndex . '.lokasi', $item['lokasi'] ?? '') }}">
                                                                <button type="button"
                                                                    class="btn btn-danger btn-remove-item">-</button>
                                                            </div>
                                                            @error('jadwal.' . $dIndex . '.items.' . $iIndex . '.lokasi')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                {{-- no items; show one empty --}}
                                                <div class="row mb-2 item-row" data-item-index="0">
                                                    <div class="col-md-2">
                                                        <input type="time"
                                                            name="jadwal[{{ $dIndex }}][items][0][waktu]"
                                                            class="form-control" value="">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <input type="text"
                                                            name="jadwal[{{ $dIndex }}][items][0][kegiatan]"
                                                            class="form-control" placeholder="Kegiatan">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="input-group">
                                                            <input type="text"
                                                                name="jadwal[{{ $dIndex }}][items][0][lokasi]"
                                                                class="form-control" placeholder="Lokasi / PIC">
                                                            <button type="button"
                                                                class="btn btn-danger btn-remove-item">-</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary btn-add-item">+ Tambah
                                                Jadwal</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- default single day --}}
                            <div class="card mb-3 day-card" data-day-index="0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6>Hari 1 - <small>Tanggal: </small></h6>
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-day">Hapus
                                            Hari</button>
                                    </div>

                                    <div class="mb-3">
                                        <input type="date" name="jadwal[0][tanggal]" class="form-control"
                                            value="{{ old('jadwal.0.tanggal') }}">
                                    </div>

                                    <div class="items-container">
                                        <div class="row mb-2 item-row" data-item-index="0">
                                            <div class="col-md-2">
                                                <input type="time" name="jadwal[0][items][0][waktu]"
                                                    class="form-control" value="{{ old('jadwal.0.items.0.waktu') }}">
                                            </div>
                                            <div class="col-md-7">
                                                <input type="text" name="jadwal[0][items][0][kegiatan]"
                                                    class="form-control" placeholder="Kegiatan"
                                                    value="{{ old('jadwal.0.items.0.kegiatan') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <input type="text" name="jadwal[0][items][0][lokasi]"
                                                        class="form-control" placeholder="Lokasi / PIC"
                                                        value="{{ old('jadwal.0.items.0.lokasi') }}">
                                                    <button type="button"
                                                        class="btn btn-danger btn-remove-item">-</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary btn-add-item">+ Tambah
                                            Jadwal</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- OTHER INFO SECTIONS --}}
                    <hr>

                    {{-- INFORMASI TRANSPORTASI --}}
                    <h5>INFORMASI TRANSPORTASI</h5>
                    <div class="mb-3">
                        <label>Penerbangan Pergi (contoh: GA-799, 12.45-14.45 wita, MDO-CKG)</label>
                        <input type="text" name="transportasi_pergi"
                            class="form-control @error('transportasi_pergi') is-invalid @enderror"
                            value="{{ old('transportasi_pergi') }}" required>
                        @error('transportasi_pergi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label>Penerbangan Pulang</label>
                        <input type="text" name="transportasi_pulang"
                            class="form-control @error('transportasi_pulang') is-invalid @enderror"
                            value="{{ old('transportasi_pulang') }}" required>
                        @error('transportasi_pulang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label>Kode Booking</label>
                        <input type="text" name="kode_booking"
                            class="form-control @error('kode_booking') is-invalid @enderror"
                            value="{{ old('kode_booking') }}" required>
                        @error('kode_booking')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label>Transportasi Lokal (Rental / Taxi / Lainnya)</label>
                        <input type="text" name="transportasi_lokal"
                            class="form-control @error('transportasi_lokal') is-invalid @enderror"
                            value="{{ old('transportasi_lokal') }}" required>
                        @error('transportasi_lokal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- INFORMASI AKOMODASI --}}
                    <hr>
                    <h5>INFORMASI AKOMODASI</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Hotel</label>
                            <input type="text" name="akomodasi_hotel" class="form-control"
                                value="{{ old('akomodasi_hotel') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Alamat</label>
                            <input type="text" name="akomodasi_alamat" class="form-control"
                                value="{{ old('akomodasi_alamat') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Nomor Telepon</label>
                            <input type="text" name="akomodasi_telpon" class="form-control"
                                value="{{ old('akomodasi_telpon') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Check-In</label>
                            <input type="date" name="akomodasi_check_in" class="form-control"
                                value="{{ old('akomodasi_check_in') }}" placeholder="Check-In / Check-Out" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Check-Out</label>
                            <input type="date" name="akomodasi_check_out" class="form-control"
                                value="{{ old('akomodasi_check_out') }}" placeholder="Check-In / Check-Out" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Booking Number</label>
                            <input type="text" name="akomodasi_booking_no" class="form-control"
                                value="{{ old('akomodasi_booking_no') }}" required>
                        </div>
                    </div>

                    {{-- KONTAK PENTING --}}
                    <hr>
                    <h5>KONTAK PENTING</h5>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label>Nama 1</label>
                            <input type="text" name="kontak[0][nama]" class="form-control"
                                value="{{ old('kontak.0.nama') }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label>Telp</label>
                            <input type="text" name="kontak[0][tel]" class="form-control"
                                value="{{ old('kontak.0.tel') }}" required>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>Nama 2</label>
                            <input type="text" name="kontak[1][nama]" class="form-control"
                                value="{{ old('kontak.1.nama') }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label>Telp</label>
                            <input type="text" name="kontak[1][tel]" class="form-control"
                                value="{{ old('kontak.1.tel') }}" required>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label>Emergency</label>
                            <input type="text" name="kontak[2][nama]" class="form-control"
                                value="{{ old('kontak.2.nama') }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label>Telp Emergency</label>
                            <input type="text" name="kontak[2][tel]" class="form-control"
                                value="{{ old('kontak.2.tel') }}" required>
                        </div>
                    </div>

                    {{-- BIAYA --}}
                    <hr>
                    <h5>RINCIAN BIAYA</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Transport</label>
                            <input type="text" name="transport"
                                class="form-control rupiah @error('transport') is-invalid @enderror"
                                value="{{ old('transport', 0) }}" placeholder="0" required>
                            @error('transport')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Akomodasi</label>
                            <input type="text" name="akomodasi"
                                class="form-control rupiah @error('akomodasi') is-invalid @enderror"
                                value="{{ old('akomodasi', 0) }}" placeholder="0" required>
                            @error('akomodasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Konsumsi</label>
                            <input type="text" name="konsumsi"
                                class="form-control rupiah @error('konsumsi') is-invalid @enderror"
                                value="{{ old('konsumsi', 0) }}" placeholder="0" required>
                            @error('konsumsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Lain-lain</label>
                            <input type="text" name="lain_lain"
                                class="form-control rupiah @error('lain_lain') is-invalid @enderror"
                                value="{{ old('lain_lain', 0) }}" placeholder="0" required>
                            @error('lain_lain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- TOTAL BIAYA (hanya tampilan) -->
                        <div class="col-md-4 mb-3">
                            <label>Total Biaya</label>
                            <input type="text" id="total_biaya_display"
                                class="form-control fw-bold text-end bg-light rupiah" readonly value="Rp 0">
                            <!-- Nilai bersih yang dikirim ke controller -->
                            <input type="hidden" name="total_biaya" id="total_biaya">
                        </div>
                    </div>

                    {{-- DISIAPKAN / DISETUJUI --}}
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Disiapkan Oleh</label>
                            <input type="text" name="disiapkan_oleh" class="form-control"
                                value="{{ old('disiapkan_oleh') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Tanggal Disiapkan</label>
                            <input type="date" name="tanggal_disiapkan" class="form-control"
                                value="{{ old('tanggal_disiapkan') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Disetujui Oleh</label>
                            <input type="text" name="disetujui_oleh" class="form-control"
                                value="{{ old('disetujui_oleh') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Tanggal Disetujui</label>
                            <input type="date" name="tanggal_disetujui" class="form-control"
                                value="{{ old('tanggal_disetujui') }}">
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('administrasi.agenda-perjalanan.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TEMPLATES (hidden) --}}
    <template id="template-day">
        <div class="card mb-3 day-card" data-day-index="__DAY_INDEX__">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6>Hari <span class="day-number">__DAY_NO__</span> - <small>Tanggal: </small></h6>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-day">Hapus Hari</button>
                </div>

                <div class="mb-3">
                    <input type="date" name="jadwal[__DAY_INDEX__][tanggal]" class="form-control">
                </div>

                <div class="items-container">
                    <!-- default one item -->
                    <div class="row mb-2 item-row" data-item-index="0">
                        <div class="col-md-2">
                            <input type="time" name="jadwal[__DAY_INDEX__][items][0][waktu]" class="form-control">
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="jadwal[__DAY_INDEX__][items][0][kegiatan]" class="form-control"
                                placeholder="Kegiatan">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="jadwal[__DAY_INDEX__][items][0][lokasi]" class="form-control"
                                    placeholder="Lokasi / PIC">
                                <button type="button" class="btn btn-danger btn-remove-item">-</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-primary btn-add-item">+ Tambah Jadwal</button>
                </div>
            </div>
        </div>
    </template>

    <template id="template-item">
        <div class="row mb-2 item-row" data-item-index="__ITEM_INDEX__">
            <div class="col-md-2">
                <input type="time" name="jadwal[__DAY_INDEX__][items][__ITEM_INDEX__][waktu]" class="form-control">
            </div>
            <div class="col-md-7">
                <input type="text" name="jadwal[__DAY_INDEX__][items][__ITEM_INDEX__][kegiatan]" class="form-control"
                    placeholder="Kegiatan">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" name="jadwal[__DAY_INDEX__][items][__ITEM_INDEX__][lokasi]"
                        class="form-control" placeholder="Lokasi / PIC">
                    <button type="button" class="btn btn-danger btn-remove-item">-</button>
                </div>
            </div>
        </div>
    </template>

@endsection
@push('script')
    <script>
        (function() {
            // Pastikan RupiahHelper sudah ada (dari layout global)
            if (typeof RupiahHelper === 'undefined') {
                console.error('RupiahHelper tidak ditemukan!');
                return;
            }

            // Inisialisasi semua input rupiah yang sudah ada saat load
            RupiahHelper.initAll('.rupiah');

            // Hitung ulang total setiap ada perubahan di input rupiah
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll(
                    'input.rupiah[name="transport"], input.rupiah[name="akomodasi"], input.rupiah[name="konsumsi"], input.rupiah[name="lain_lain"]'
                ).forEach(input => {
                    total += RupiahHelper.getCleanValue(input);
                });

                // Update tampilan
                const display = document.getElementById('total_biaya_display');
                if (display) {
                    display.value = total === 0 ? 'Rp 0' : 'Rp ' + total.toLocaleString('id-ID');
                }

                // Update hidden input yang dikirim ke backend
                const hidden = document.getElementById('total_biaya');
                if (hidden) {
                    hidden.value = total;
                }
            }

            // Event listener untuk semua input rupiah
            document.querySelectorAll('.rupiah').forEach(input => {
                input.addEventListener('input', calculateTotal);
                input.addEventListener('blur', calculateTotal);
            });

            // Update nomor hari
            function updateDayNumbers() {
                document.querySelectorAll('#days-container .day-card').forEach((card, idx) => {
                    const el = card.querySelector('.day-number');
                    if (el) el.textContent = idx + 1;
                });
            }

            // === LOGIKA TAMBAH/HAPUS HARI & ITEM (tetap sama) ===
            function newIndex() {
                return Date.now() + Math.floor(Math.random() * 1000);
            }

            document.getElementById('btn-add-day')?.addEventListener('click', function() {
                const tpl = document.getElementById('template-day').innerHTML;
                const dayIndex = newIndex();
                const dayNo = document.querySelectorAll('#days-container .day-card').length + 1;
                let html = tpl.replace(/__DAY_INDEX__/g, dayIndex).replace(/__DAY_NO__/g, dayNo);
                document.getElementById('days-container').insertAdjacentHTML('beforeend', html);
                updateDayNumbers();
            });

            document.getElementById('days-container')?.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-add-item')) {
                    const dayCard = e.target.closest('.day-card');
                    const dayIndex = dayCard.dataset.dayIndex;
                    const container = dayCard.querySelector('.items-container');
                    const itemIndex = newIndex();
                    let html = document.getElementById('template-item').innerHTML
                        .replace(/__DAY_INDEX__/g, dayIndex)
                        .replace(/__ITEM_INDEX__/g, itemIndex);
                    container.insertAdjacentHTML('beforeend', html);
                    return;
                }

                if (e.target.classList.contains('btn-remove-item')) {
                    e.target.closest('.item-row').remove();
                    return;
                }

                if (e.target.classList.contains('btn-remove-day')) {
                    if (confirm('Hapus hari ini beserta semua jadwalnya?')) {
                        e.target.closest('.day-card').remove();
                        updateDayNumbers();
                    }
                }
            });

            // Init saat halaman siap
            document.addEventListener('DOMContentLoaded', function() {
                updateDayNumbers();
                calculateTotal(); // pertama kali
            });

        })();
    </script>
@endpush
