@extends('layouts.partial.layouts')

@section('page-title', 'List Hasil Keputusan Rapat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>DATA HASIL KEPUTUSAN RAPAT</strong>
            </div>
            <div class="card-body">

                {{-- Action Buttons --}}
                {{-- <div class="d-flex justify-content-between gap-2 mt-3 mb-2">
                <a href="{{ route('administrasi.rapat.hasil-keputusan.create') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-plus"></i> Tambah Hasil Keputusan Rapat
                </a>
            </div> --}}

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="hasil-keputusan-rapat-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nomor Surat Rapat</th>
                                <th>Judul Rapat</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Pemimpin Rapat</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hasilKeputusan as $item)
                                @php
                                    $hasDetail = $item->rapatDetails->count() > 0;
                                @endphp

                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->nomor_surat ?? '-' }}</td>
                                    <td>{{ $item->judul_rapat ?? '-' }}</td>
                                    <td>{{ $item->tempat ?? '-' }}</td>

                                    <td class="text-center">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                                    </td>

                                    <td>{{ $item->pemimpin_rapat ?? '-' }}</td>
                                    <td class="text-center">
                                        {{-- Tombol PDF --}}
                                        <a @if ($hasDetail) href="{{ route('administrasi.rapat.hasil-keputusan.generatePdf', $item->id) }}"
                                        target="_blank"
                                        class="btn btn-warning btn-sm"
                                    @else
                                        href="javascript:void(0)"
                                        class="btn btn-secondary btn-sm disabled" @endif
                                            data-bs-toggle="tooltip"
                                            title="{{ $hasDetail ? 'Unduh PDF' : 'Data belum lengkap' }}">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
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
            $('#hasil-keputusan-rapat-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada Hasil Keputusan Rapat 📌"
                },
                columnDefs: [{
                    targets: [0, 6],
                    orderable: false
                }]
            });

            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                let form = this;

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let btn = $(form).find('.delete-btn');
                        btn.prop('disabled', true);
                        btn.find('.delete-text').addClass('d-none');
                        btn.find('.spinner-border').removeClass('d-none');
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
