@extends('layouts.partial.layouts')

@section('page-title', 'Input Permintaan Kas Kecil | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')
<div class="container mt-4">
    {{-- Alert sukses --}}
     @if(session('success'))
         <div class="alert alert-success alert-dismissible fade show" role="alert">
             <strong>Sukses!</strong> {{ session('success') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
     @endif

     {{-- Alert error --}}
     @if(session('error'))
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
             <strong>Gagal!</strong> {{ session('error') }}
             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
         </div>
     @endif

    <form action="{{route('administrasi.kas-kecil.store')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <h4 class="text-center mb-4"><strong>FORMULIR PERMINTAAN KAS KECIL</strong></h4>

        {{-- Informasi Utama --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Nomor<span
                        class="text-danger">*</span></label>
                <input type="text"
                       name="nomor"
                       class="form-control @error('nomor') is-invalid @enderror"
                       >
                @error('nomor')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label>Tanggal<span
                        class="text-danger">*</span></label>
                <input type="date"
                       name="tanggal"
                       class="form-control @error('tanggal') is-invalid @enderror"
                       >
                @error('tanggal')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label>Nama Pemohon<span
                        class="text-danger">*</span></label>
                <input type="text"
                       name="nama_pemohon"
                       class="form-control @error('nama_pemohon') is-invalid @enderror"
                       >
                @error('nama_pemohon')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label>Departemen<span
                        class="text-danger">*</span></label>
                <input type="text"
                       name="departemen"
                       class="form-control @error('departemen') is-invalid @enderror"
                       >
                @error('departemen')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label>Jenis Permintaan <span class="text-danger">*</span></label>
                <select name="jenis" class="form-control @error('jenis') is-invalid @enderror">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="penambahan">Penambahan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
                @error('jenis')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        {{-- Rincian Permintaan --}}
        <h5><strong>Rincian Permintaan:</strong></h5>
        <table class="table table-bordered" id="table-kebutuhan">
            <thead class="text-center">
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Uraian Kebutuhan<span
                            class="text-danger">*</span></th>
                    <th style="width: 180px;">Jenis<span
                            class="text-danger">*</span></th>
                    <th style="width: 200px;">Jumlah (Rp)<span
                            class="text-danger">*</span></th>
                    <th style="width: 60px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="body-kebutuhan">
                <tr>
                    <td class="text-center nomor">1</td>
                    <td>
                        <input type="text"
                               name="keterangan[]"
                               class="form-control @error('keterangan.*') is-invalid @enderror">
                        @foreach ($errors->get('keterangan.*') as $msg)
                            <small class="text-danger d-block">{{ $msg[0] }}</small>
                        @endforeach
                    </td>
                    <td>
                        <select name="kategori[]"
                                class="form-control @error('kategori') is-invalid @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="transport">Transport</option>
                            <option value="bensin">Bensin</option>
                            <option value="konsumsi">Konsumsi</option>
                            <option value="atm">ATM</option>
                            <option value="lain">Lain-lain</option>
                        </select>
                        @foreach ($errors->get('kategori') as $error)
                            <small class="text-danger d-block">{{ $error }}</small>
                        @endforeach
                    </td>
                    <td>
                        <input type="text" name="jumlah[]"
                            class="form-control autonumeric @if($errors->has('jumlah.*')) is-invalid @endif">

                        @foreach ($errors->get('jumlah.*') as $msg)
                            <small class="text-danger d-block">{{ $msg[0] }}</small>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-hapus">X</button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="button" id="btnTambah" class="btn btn-success btn-sm">
                            + Tambah Baris
                        </button>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td><input type="text" name="total" class="form-control autonumeric-total"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Tanda Tangan --}}
        <h5 class="mt-4"><strong>Pengesahan</strong></h5>

        <div class="row text-center mt-3">
            <!-- PEMOHON -->
            <div class="col-md-4">
                <label>Ttd. Pemohon</label>

                <input type="file" name="ttd_nama_pemohon"
                    class="form-control mt-2 @error('ttd_nama_pemohon') is-invalid @enderror"
                    accept="image/*">
                @error('ttd_nama_pemohon')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- ATASAN -->
            <div class="col-md-4">
                <label>Atasan Langsung <span class="text-danger">*</span></label>

                <!-- Nama Atasan (WAJIB) -->
                <input type="text" name="nama_atasan_langsung"
                    class="form-control mt-2 @error('nama_atasan_langsung') is-invalid @enderror"
                    placeholder="Nama Atasan">
                @error('nama_atasan_langsung')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                <label class="mt-2">Ttd. Atasan Langsung</label>
                <input type="file" name="ttd_nama_atasan_langsung"
                    class="form-control mt-2 @error('ttd_nama_atasan_langsung') is-invalid @enderror"
                    accept="image/*">
                @error('ttd_nama_atasan_langsung')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- KEUANGAN -->
            <div class="col-md-4">
                <label>Bagian Keuangan <span class="text-danger">*</span></label>

                <!-- Nama Keuangan (WAJIB) -->
                <input type="text" name="nama_bagian_keuangan"
                    class="form-control mt-2 @error('nama_bagian_keuangan') is-invalid @enderror"
                    placeholder="Nama Keuangan">
                @error('nama_bagian_keuangan')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
                <label class="mt-2">Ttd. Nama Bagian Keuangan</label>
                <input type="file" name="ttd_nama_bagian_keuangan"
                    class="form-control mt-2 @error('ttd_nama_bagian_keuangan') is-invalid @enderror"
                    accept="image/*">
                @error('ttd_nama_bagian_keuangan')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>


        {{-- Tombol Submit --}}
        <div class="mt-4 text-end">
             <a href="{{ route('administrasi.kas-kecil.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">
                Simpan Permintaan
            </button>
        </div>

    </form>
</div>
@endsection
@push('script')
<script>
document.addEventListener("DOMContentLoaded", function () {

    let body = document.getElementById("body-kebutuhan");
    let btnTambah = document.getElementById("btnTambah");

    let autoNumericList = [];

    // Inisialisasi AutoNumeric pada semua field jumlah & total
    function initAutoNumeric() {
        document.querySelectorAll('.autonumeric').forEach(el => {
            if (!el.hasAttribute('an-init')) {
                new AutoNumeric(el, {
                    digitGroupSeparator: ".",
                    decimalCharacter: ",",
                    decimalPlaces: 0,
                    unformatOnSubmit: true
                });
                el.setAttribute('an-init', 'true');
            }
        });

        document.querySelectorAll('.autonumeric-total').forEach(el => {
            if (!el.hasAttribute('an-init')) {
                new AutoNumeric(el, {
                    digitGroupSeparator: ".",
                    decimalCharacter: ",",
                    decimalPlaces: 0,
                    unformatOnSubmit: true,
                    readOnly: true
                });
                el.setAttribute('an-init', 'true');
            }
        });
    }

    initAutoNumeric();

    // Tambah baris
    btnTambah.addEventListener("click", function () {
        const rowCount = body.querySelectorAll("tr").length + 1;

        let row = `
        <tr>
            <td class="text-center nomor">${rowCount}</td>

            <td>
                <input type="text" name="keterangan[]" class="form-control">
            </td>

            <td>
                <select name="kategori[]" class="form-control">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="transport">Transport</option>
                    <option value="bensin">Bensin</option>
                    <option value="konsumsi">Konsumsi</option>
                    <option value="atm">ATM</option>
                    <option value="lain">Lain-lain</option>
                </select>
            </td>

            <td>
                <input type="text" name="jumlah[]" class="form-control autonumeric">
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-hapus">X</button>
            </td>
        </tr>
        `;

        body.insertAdjacentHTML('beforeend', row);

        initAutoNumeric();
        updateNomor();
        hitungTotal();
    });


    // Hapus baris
    body.addEventListener("click", function (e) {
        if (e.target.classList.contains("btn-hapus")) {
            e.target.closest("tr").remove();
            updateNomor();
            hitungTotal();
        }
    });

    function updateNomor() {
        let nomorCells = document.querySelectorAll(".nomor");
        nomorCells.forEach((cell, index) => {
            cell.innerText = index + 1;
        });
    }

    // Hitung total berdasarkan raw value AutoNumeric
    function hitungTotal() {
        let total = 0;

        // disini hitung total berdasarkan raw value AutoNumeric
        document.querySelectorAll('.autonumeric').forEach(input => {
            const rawValue = AutoNumeric.getNumber(input) || 0;
            total += parseFloat(rawValue);
        });

        let totalField = document.querySelector('.autonumeric-total');
        if (totalField) {
            AutoNumeric.getAutoNumericElement(totalField).set(total);
        }
    }

    // Recalculate total on input
    body.addEventListener("input", function (e) {
        if (e.target.classList.contains("autonumeric")) {
            hitungTotal();
        }
    });




});
</script>
@endpush
