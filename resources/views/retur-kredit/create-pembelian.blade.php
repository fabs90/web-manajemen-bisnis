@extends('layouts.partial.layouts')
@section('page-title', 'Retur Pembelian | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Retur Pembelian')
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

        <form action="{{ route('retur.store-pembelian') }}" method="POST">
            @csrf

            {{-- Tanggal Retur --}}
            <div class="mb-3">
                <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
            </div>

            {{-- Nama Debitur --}}
            <div class="mb-3">
                <label class="form-label">Nama Kreditur <span class="text-danger">*</span></label>
                <select name="nama_pelanggan" id="nama_pelanggan" class="form-control" required>
                    <option value="">-- Pilih Kreditur --</option>
                    @foreach ($kreditur as $d)
                        <option value="{{ $d->id }}" {{ old('nama_pelanggan') == $d->id ? 'selected' : '' }}>
                            {{ $d->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Hutang Aktif (akan diisi via JS) --}}
            <div class="mb-3">
                <label class="form-label">Hutang Aktif (untuk diretur) <span class="text-danger">*</span></label>
                <select name="hutang_aktif" id="hutang_aktif" class="form-control" required>
                    <option value="">-- Pilih Kreditur terlebih dahulu --</option>
                </select>
                <div class="form-text text-muted">Pilih kreditur untuk melihat hutang aktif.</div>
            </div>

            {{-- Jumlah Retur --}}
            <div class="mb-3">
                <label class="form-label">Jumlah Retur Pembelian<span class="text-danger">*</span></label>
                <input type="text" name="retur_jumlah" id="retur_jumlah"
                       class="form-control rupiah" value="{{ old('retur_jumlah') }}" placeholder="0" required>
            </div>

            {{-- Keterangan --}}
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="retur_keterangan" class="form-control" rows="2">{{ old('retur_keterangan') }}</textarea>
            </div>

            {{-- Penanganan Retur --}}
            <div class="mb-3">
                <label class="form-label">Penanganan Retur <span class="text-danger">*</span></label>
                <select name="retur_penanganan" class="form-select" required>
                    <option value="">-- Pilih Penanganan --</option>
                    <option value="kurangi_hutang" {{ old('retur_penanganan') == 'kurangi_hutang' ? 'selected' : '' }}>
                        Kurangi Hutang (kurangi ke tagihan)
                    </option>
                    <option value="tunai_kembali" {{ old('retur_penanganan') == 'tunai_kembali' ? 'selected' : '' }}>
                        Kembalikan Tunai (uang keluar)
                    </option>
                </select>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-danger">
                    Simpan Retur
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Data piutang tersembunyi untuk JS --}}
<div id="piutang-data" style="display: none;">
    @foreach ($listHutang as $p)
        <div class="piutang-item"
             data-debitur="{{ $p->pelanggan_id }}"
             data-kode="{{ $p->kode }}"
             data-saldo="{{ $p->saldo }}">
            {{ $p->pelanggan->nama }} - Rp {{ number_format($p->saldo, 0, ',', '.') }}
        </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const namaPelanggan = document.getElementById('nama_pelanggan');
    const hutangSelect = document.getElementById('hutang_aktif');
    const piutangData = document.getElementById('piutang-data');
    const items = piutangData.querySelectorAll('.piutang-item');

    // Reset select
    function resetHutangSelect() {
        hutangSelect.innerHTML = '<option value="">-- Pilih Debitur terlebih dahulu --</option>';
        hutangSelect.disabled = true;
    }

    // Isi select berdasarkan debitur
    function loadPiutang(debiturId) {
        hutangSelect.innerHTML = '<option value="">-- Pilih Piutang Aktif --</option>';
        let found = false;

        items.forEach(item => {
            if (item.dataset.debitur == debiturId && parseFloat(item.dataset.saldo) > 0) {
                const opt = document.createElement('option');
                opt.value = item.dataset.kode;
                opt.textContent = item.textContent;
                hutangSelect.appendChild(opt);
                found = true;
            }
        });

        if (found) {
            hutangSelect.disabled = false;
        } else {
            hutangSelect.innerHTML = '<option value="">-- Tidak ada piutang aktif --</option>';
            hutangSelect.disabled = true;
        }
    }

    // Event: Pilih debitur
    namaPelanggan.addEventListener('change', function () {
        const debiturId = this.value;
        if (debiturId) {
            loadPiutang(debiturId);
        } else {
            resetHutangSelect();
        }
    });

    // AutoNumeric
    new AutoNumeric.multiple('.rupiah', {
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        currencySymbol: 'Rp ',
        unformatOnSubmit: true,
        minimumValue: '0'
    });

    // Jika old input ada, reload
    @if(old('nama_pelanggan'))
        loadPiutang({{ old('nama_pelanggan') }});
        @if(old('hutang_aktif'))
            hutangSelect.value = '{{ old('hutang_aktif') }}';
        @endif
    @endif

    //sweetAlert
    // -----------------------------
    // SweetAlert feedback dari session
    // -----------------------------
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            toast: true,
            position: 'top-end',
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end',
        });
    @endif

});
</script>
@endsection
