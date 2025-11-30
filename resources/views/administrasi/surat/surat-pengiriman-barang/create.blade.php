@extends('layouts.partial.layouts')

@section('page-title', 'Tambah Surat Pengiriman Barang | Digitrans')

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
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>FORM SURAT PENGIRIMAN BARANG</strong>
        </div>

        <div class="card-body">
            <form action="{{route('administrasi.spb.store')}}" method="POST">
                @csrf

                {{-- Pilih Faktur --}}
                <div class="my-3">
                    <label class="form-label"><strong>Pilih Faktur Penjualan</strong></label>
                    <select class="form-select" name="faktur_id" id="fakturSelect" required>
                        <option value="" selected disabled>-- Pilih Faktur --</option>
                        @foreach ($dataFaktur as $faktur)
                            <option value="{{ $faktur->id }}">
                                {{ $faktur->kode_faktur }} - {{ $faktur->nama_pembeli }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DETAIL FAKTUR OTOMATIS MUNCUL --}}
                <div id="fakturInfo" class="d-none">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Kepada:</strong>
                            <div id="namaPembeli"></div>
                            <div id="alamatPembeli"></div>
                        </div>

                        <div class="col-md-6">
                            <strong>Informasi:</strong>
                            <div>Nomor Faktur : <span id="kodeFaktur"></span></div>
                            <div>Nomor Pesanan : <span id="nomorPesanan"></span></div>
                            <div>Tanggal : <span id="tanggal"></span></div>
                            <div>Barang dikirim via : <span id="jenisPengiriman"></span></div>
                        </div>
                    </div>

                    {{-- Tabel Barang --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Jumlah Dipesan</th>
                                    <th>Jumlah Dikirim</th>
                                    <th>Nama Barang</th>
                                </tr>
                            </thead>
                            <tbody id="detailBarang"></tbody>
                        </table>
                    </div>
                </div>
                <div id="loadingSpinner" class="text-center my-3 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Memuat detail faktur...</div>
                </div>
                <hr>
                {{-- INPUT BAGIAN BAWAH --}}
                <div class="mb-3">
                    <label class="form-label"><strong>Nomor Surat</strong></label>
                    <input type="text" name="nomor_surat" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Tanggal Diterima</strong></label>
                    <input type="date" name="tanggal_barang_diterima" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Keadaan Barang</strong></label>
                    <select class="form-select" name="keadaan" required>
                        <option value="Baik">Baik</option>
                        <option value="Rusak">Rusak</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Keterangan (Optional)</strong></label>
                    <input type="text" name="keterangan" class="form-control">
                </div>

                <div class="row text-center mt-4">
                    <div class="col">
                        <strong>Yang Menerima</strong>
                        <input type="text" name="nama_penerima" class="form-control mt-2" required>
                    </div>
                    <div class="col">
                        <strong>Bagian Pengiriman</strong>
                        <input type="text" name="nama_pengirim" class="form-control mt-2" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('administrasi.spb.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
$('#fakturSelect').change(function () {
    let id = $(this).val();
    let url = "{{ route('administrasi.spb.getDetail', ':id') }}";
    url = url.replace(':id', id);

    $('#fakturInfo').addClass('d-none');
    $('#loadingSpinner').removeClass('d-none');

    $.get(url, function (res) {
        $('#loadingSpinner').addClass('d-none');
        $('#fakturInfo').removeClass('d-none');
        $('#namaPembeli').text(res.faktur.nama_pembeli);
        $('#alamatPembeli').text(res.faktur.alamat_pembeli);
        $('#kodeFaktur').text(res.faktur.kode_faktur);
        $('#nomorPesanan').text(res.faktur.nomor_pesanan ?? "-");
        $('#tanggal').text(res.faktur.tanggal);
        $('#jenisPengiriman').text(res.faktur.jenis_pengiriman);

        let detail = '';
        res.details.forEach((item, i) => {
            detail += `
                <tr>
                    <td>${i+1}</td>
                    <td>${item.jumlah_dipesan}</td>
                    <td>${item.jumlah_dikirim ?? '-'}</td>
                    <td>${item.nama_barang}</td>
                </tr>
            `;
        });
        $('#detailBarang').html(detail);

    }).fail(function(xhr){
        $('#loadingSpinner').addClass('d-none');
        console.error(xhr.responseText);
        alert("Gagal memuat data faktur!");
    });
});
</script>
@endpush
