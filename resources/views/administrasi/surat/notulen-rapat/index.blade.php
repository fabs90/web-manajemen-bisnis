@extends('layouts.partial.layouts')

@section('page-title', 'Agenda Rapat | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-row')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>DATA NOTULEN RAPAT</strong>
                <a href="{{ route('administrasi.rapat.create') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-plus me-1"></i> Tambah Notulen Rapat
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-bordered table-striped" id="agenda-rapat-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Judul Rapat</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Pemimpin Rapat</th>
                                <th>Nama Notulis</th>
                                <th width="15%">Unduh PDF</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($agendaRapat as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->judul_rapat }}</td>
                                    <td>{{ $item->tempat }}</td>
                                    <td class="text-center">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">{{ $item->waktu ?? '-' }}</td>
                                    <td>{{ $item->pemimpin_rapat ?? ($item->pemimpin_rapat ?? '-') }}</td>
                                    <td>{{ $item->nama_notulis ?? ($item->nama_notulis ?? '-') }}</td>

                                    <td class="text-center text-nowrap">
                                        <div class="d-flex justify-content-center gap-1 flex-nowrap">

                                            <form
                                                action="{{ route('administrasi.rapat.generatePdf', ['rapatId' => $item->id]) }}"
                                                method="GET" target="_blank" class="d-inline formGenerateAgendaRapatPdf">
                                                <button class="btn btn-warning btn-sm generateAgendaRapatBtn text-white"
                                                    type="submit" title="Download Notulen Rapat">
                                                    <i class="bi bi-file-pdf text-white"></i> Notulen
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('administrasi.rapat.hasil-keputusan.generatePdf', ['hasilKeputusanId' => $item->id]) }}"
                                                method="GET" target="_blank" class="d-inline formGenerateHasilKeputusanPdf">
                                                <button class="btn btn-success btn-sm generateHasilKeputusanBtn"
                                                    type="submit" title="Download Hasil Keputusan">
                                                    <i class="bi bi-file-earmark-pdf text-white"></i> Keputusan
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                    <td class="text-center text-nowrap">
                                        <div class="d-flex justify-content-center gap-1 flex-nowrap">

                                            <a href="{{ route('administrasi.rapat.edit', ['rapatId' => $item->id]) }}"
                                                class="btn btn-info btn-sm" title="Edit Agenda Rapat">
                                                <i class="bi bi-pencil text-white"></i>
                                            </a>

                                            <form
                                                action="{{ route('administrasi.rapat.destroy', ['rapatId' => $item->id]) }}"
                                                method="POST" class="d-inline formDeleteAgendaRapat"
                                                onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm deleteAgendaRapatBtn">
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

    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: "{{ session('success') }}",
                    timer: 3000
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    timer: 3000
                });
            @endif

            $('#agenda-rapat-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada agenda rapat📪"
                }
            });

            $('.formGenerateAgendaRapatPdf').on('submit', function(e) {
                const $btn = $(this).find('.generateAgendaRapatBtn');
                const originalHtml = $btn.html();
                $btn.prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                    );
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 2000);
            });

            $('.formGenerateHasilKeputusanPdf').on('submit', function(e) {
                const $btn = $(this).find('.generateHasilKeputusanBtn');
                const originalHtml = $btn.html();
                $btn.prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                    );
                setTimeout(() => {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 2000);
            });

            $('.formDeleteAgendaRapat').on('submit', function(e) {
                const $btn = $(this).find('.deleteAgendaRapatBtn');
                $btn.prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
                    );
            });
        });
    </script>
@endpush
