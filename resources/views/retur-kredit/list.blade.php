@extends('layouts.partial.layouts')
@section('page-title', 'List Retur Kredit')

@section('section-heading', 'List Retur Kredit')
@section('section-row')

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="returTable">
                <thead class="table-danger">
                    <tr>
                        <th width="5%">#</th>
                        <th>Tanggal</th>
                        <th>Debitur</th>
                        <th>Kode Piutang</th>
                        <th>Uraian</th>
                        <th>Retur (Rp)</th>
                        <th>Penanganan</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returPenjualan as $index => $retur)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $retur->pelanggan->nama ?? '-' }}</td>
                            <td><code>{{ $retur->kode }}</code></td>
                            <td>{{ $retur->uraian }}</td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($retur->debit, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($retur->kredit > 0)
                                    <span class="badge bg-warning text-dark">Tunai Kembali</span>
                                @else
                                    <span class="badge bg-info">Kurangi Piutang</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Edit">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Tidak ada data retur kredit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        $('#returTable').DataTable({
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            language: {
                search: "Cari:",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                },
                emptyTable: "Tidak ada data retur untuk ditampilkan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            },
            columnDefs: [
                { targets: [0, 7], orderable: false }
            ]
        });
    });

    // SweetAlert konfirmasi hapus
    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data retur ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm-' + id).submit();
            }
        });
    }

    // Fungsi Edit (bisa dikembangkan ke modal atau redirect)
    function editRetur(id) {
        // Contoh: redirect ke form edit
        window.location.href = `/keuangan/retur-kredit/${id}/edit`;

        // Atau buka modal (jika pakai modal)
        // $('#editModal').modal('show');
    }
</script>
@endpush
