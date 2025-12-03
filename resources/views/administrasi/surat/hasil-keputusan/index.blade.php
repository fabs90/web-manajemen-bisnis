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
            <div class="d-flex justify-content-between gap-2 mt-3 mb-2">
                <a href="{{ route('administrasi.rapat.hasil-keputusan.create') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-plus"></i> Tambah Hasil Keputusan Rapat
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="hasil-keputusan-rapat-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Judul Rapat</th>
                            <th>Tempat</th>
                            <th>Tanggal</th>
                            <th>Pimpinan Rapat</th>
                            <th>Keputusan Rapat</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($hasilKeputusan as $item)
                        @php
                            $hasDetail = $item->hasilKeputusanRapat;
                        @endphp

                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>

                            <td class="{{ $hasDetail ? '' : 'text-muted' }}">
                                {{ $item->judul_rapat }}
                            </td>

                            <td>{{ $item->tempat ?? '-' }}</td>

                            <td class="text-center">
                                {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') : '-' }}
                            </td>

                            <td>{{ $item->pimpinan_rapat ?? '-' }}</td>

                            {{-- Keputusan Rapat --}}
                            <td class="{{ $hasDetail ? '' : 'text-muted' }}">
                                {{ $hasDetail->keputusan_rapat ?? '-' }}
                            </td>
                            <td class="text-center">

                                {{-- Tombol PDF --}}
                                <a
                                    @if($hasDetail)
                                        href="{{ route('administrasi.rapat.hasil-keputusan.generatePdf', $item->id) }}"
                                        target="_blank"
                                        class="btn btn-warning btn-sm"
                                    @else
                                        href="javascript:void(0)"
                                        class="btn btn-secondary btn-sm disabled"
                                    @endif
                                    data-bs-toggle="tooltip"
                                    title="{{ $hasDetail ? 'Unduh PDF' : 'Data belum lengkap' }}"
                                >
                                    <i class="bi bi-file-pdf"></i>
                                </a>

                                <form action="{{ route('administrasi.rapat.hasil-keputusan.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus hasil keputusan ini?');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')

                                    {{-- Tombol Delete --}}
                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm {{ $hasDetail ? '' : 'disabled' }}"
                                        title="{{ $hasDetail ? 'Hapus' : 'Tidak dapat dihapus sebelum lengkap' }}"
                                        {{ $hasDetail ? '' : 'disabled' }}
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
            }
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
