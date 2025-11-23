@extends('layouts.partial.layouts')
@section('page-title', 'Surat Keluar | Digitrans - Administrasi Surat')
@section('section-heading', 'Form Surat Keluar')
@section('section-row')
<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('administrasi.surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mb-4">
            <div class="card-header fw-bold">Informasi Surat Keluar</div>
            <div class="card-body">
                <div class="mb-3">
                <label class="form-label">Email Penerima</label>
                <input type="email" name="email_penerima" class="form-control" placeholder="email@example.com (Pisahkan dengan koma jika ingin menambahkan lebih dari satu email)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control" placeholder="001/SK/II/2024">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="text" name="lampiran_text" class="form-control" placeholder="- / 1 berkas / dll">
                </div>

                <div class="mb-3">
                    <label class="form-label">Perihal</label>
                    <input type="text" name="perihal" class="form-control" placeholder="Permohonan, Undangan, Pemberitahuan...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control">
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Nama Penerima</label>
                    <input type="text" name="nama_penerima" class="form-control" placeholder="Nama penerima">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jabatan Penerima</label>
                    <input type="text" name="jabatan_penerima" class="form-control" placeholder="Direktur / Manager / dll">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Penerima</label>
                    <textarea name="alamat_penerima" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Paragraf Pembuka</label>
                    <textarea name="paragraf_pembuka" class="form-control" rows="3" placeholder="Salam dan pengantar"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Isi Surat</label>
                    <textarea name="paragraf_isi" class="form-control" rows="5" placeholder="Maksud dan tujuan surat..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Penutup</label>
                    <textarea name="paragraf_penutup" class="form-control" rows="3" placeholder="Harapan dan ucapan terima kasih"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pengirim</label>
                    <input type="text" name="nama_pengirim" class="form-control" placeholder="Nama pejabat / pimpinan">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jabatan Pengirim</label>
                    <input type="text" name="jabatan_pengirim" class="form-control" placeholder="Jabatan pengirim">
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label">Pihak Tembusan</label>
                    <textarea name="tembusan" class="form-control" rows="2" placeholder="(Opsional)"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Tanda Tangan (JPG/PNG)</label>
                    <input type="file" name="ttd" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Lampiran (Opsional)</label>
                    <input type="file" name="file_lampiran" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan Surat Keluar</button>

            </div>
        </div>
    </form>
</div>
@endsection
