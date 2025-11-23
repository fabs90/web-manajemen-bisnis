@extends('layouts.partial.layouts')

@section('page-title', 'Disposisi Surat Masuk | Digitrans - Administrasi Surat')
@section('section-heading', 'Lembar Disposisi')

@section('section-row')

<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <div class="card-header fw-bold">Lembar Disposisi</div>
        <div class="card-body">
            <form action="{{route('administrasi.surat-masuk.disposisi.store', ["id" => $surat->id])}}" method="POST" enctype="multipart/form-data">
                @csrf
                <table class="table table-bordered">
                    <tr><th colspan="2" class="text-center">LEMBAR DISPOSISI</th></tr>

                    <tr><td width="200px">Nomor Agenda Surat Masuk</td><td>{{ $surat->nomor_agenda }}</td></tr>
                    <tr><td>Tanggal Terima</td><td>{{ $surat->tanggal_terima }}</td></tr>
                    <tr><td>Nomor Surat</td><td>{{ $surat->nomor_surat }}</td></tr>
                    <tr><td>Tanggal Surat</td><td>{{ $surat->tanggal_surat }}</td></tr>
                    <tr><td>Pengirim</td><td>{{ $surat->pengirim }}</td></tr>
                    <tr><td>Perihal</td><td>{{ $surat->perihal }}</td></tr>
                </table>

                <hr>

                <h5 class="fw-bold">Disposisi:</h5>

                <div class="row">
                    <div class="col-md-4">
                        <div><input type="checkbox" name="disposisi[]" value="Segera"
                            {{ $surat->disp_segera ? 'checked' : '' }}> Segera</div>

                        <div><input type="checkbox" name="disposisi[]" value="Teliti dan beri pendapat"
                            {{ $surat->disp_teliti ? 'checked' : '' }}> Teliti dan beri pendapat</div>

                        <div><input type="checkbox" name="disposisi[]" value="Edarkan"
                            {{ $surat->disp_edarkan ? 'checked' : '' }}> Edarkan</div>

                        <div><input type="checkbox" name="disposisi[]" value="Untuk diketahui"
                            {{ $surat->disp_diketahui ? 'checked' : '' }}> Untuk diketahui</div>
                    </div>

                    <div class="col-md-4">
                        <div><input type="checkbox" name="disposisi[]" value="Koordinasikan"
                            {{ $surat->disp_koordinasikan ? 'checked' : '' }}> Koordinasikan</div>

                        <div><input type="checkbox" name="disposisi[]" value="Proses lebih lanjut"
                            {{ $surat->disp_proses_lanjut ? 'checked' : '' }}> Proses lebih lanjut</div>

                        <div><input type="checkbox" name="disposisi[]" value="Arsipkan"
                            {{ $surat->disp_arsipkan ? 'checked' : '' }}> Arsipkan</div>

                        <div><input type="checkbox" name="disposisi[]" value="Mohon dijawab"
                            {{ $surat->disp_mohon_dijawab ? 'checked' : '' }}> Mohon dijawab</div>
                    </div>
                </div>

                <div class="mt-3">
                    <label>Catatan:</label>
                    <textarea name="catatan" class="form-control" rows="3">{{$surat->catatan ? $surat->catatan : ''}}</textarea>
                </div>

                <hr>

                <h5 class="fw-bold">Ditujukan kepada:</h5>

                <div>
                    <div><input type="checkbox" name="tujuan[]" value="Keuangan"
                        {{ $surat->tujuan_keuangan ? 'checked' : '' }}> Keuangan</div>

                    <div><input type="checkbox" name="tujuan[]" value="Kepala Bagian Gudang"
                        {{ $surat->tujuan_gudang ? 'checked' : '' }}> Ka. Bagian Gudang</div>

                    <div><input type="checkbox" name="tujuan[]" value="Karyawan"
                        {{ $surat->tujuan_karyawan ? 'checked' : '' }}> Karyawan</div>

                    <div><input type="checkbox" name="tujuan[]" value="Lainnya"
                        {{ $surat->tujuan_lainnya ? 'checked' : '' }}> Lainnya</div>
                </div>

                <div class="mt-3">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal_disposisi" class="form-control"
                           value="{{ $surat->tanggal_disposisi  }}" required>
                </div>

                <div class="mt-3">
                    <label>Tanda Tangan Pimpinan (opsional upload)</label>
                    <input type="file" name="ttd_pimpinan" class="form-control">
                </div>
                @if ($surat->ttd_pimpinan)
                    <div class="mt-3">
                        <label>Tanda Tangan Saat Ini:</label><br>
                        <img src="{{ asset('storage/' . $surat->ttd_pimpinan) }}"
                             alt="TTD Pimpinan"
                             style="max-height: 150px; border:1px solid #ccc; padding:4px;">
                    </div>
                @endif

                <div class="mt-4">
                    <button class="btn btn-primary w-100">Simpan Disposisi</button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script>
    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 3000,
            toast: true,
            position: 'top-end',
        });
    @endif
    </script>
@endpush
