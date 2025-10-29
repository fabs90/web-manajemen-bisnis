@extends('layouts.partial.layouts')
@section('page-title', 'Pendapatan')

@section('section-heading', 'Pendapatan ')
@section('section-row')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daftar Piutang Perusahaan</h5>
        <a href="{{ route('keuangan.pendapatan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Data Pendapatan
        </a>
    </div>

    <div class="table-responsive">
          <table class="table table-sm pendapatan-table">
              <thead>
                  <tr>
                      <th>Tanggal</th>
                      <th>Uraian</th>
                      <th>Debit</th>
                      <th>Kredit</th>
                      <th>Saldo</th>
                  </tr>
              </thead>
              <tbody>
                  @forelse ($dataPiutang as $item)
                      <tr>
                          <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                          <td>{{ $item->uraian }}</td>
                          <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                          <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                          <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                      </tr>
                  @empty
                      <tr>
                          <td colspan="7" class="text-center text-muted py-3">
                              <em>Tidak ada data pendapatan.</em>
                          </td>
                      </tr>
                  @endforelse
              </tbody>
          </table>
      </div>

      {{-- ===================== TABEL SEMUA DATA (dengan tombol hapus) ===================== --}}
          <h5 class="mb-3">Semua Data Pendapatan</h5>
          <div class="table-responsive">
              <table class="table table-bordered table-striped table-hover align-middle" id="allDatasTable">
                  <thead class="table-light">
                      <tr>
                          <th>#</th>
                          <th>Tanggal</th>
                          <th>Uraian</th>
                          <th>Piutang Dagang</th>
                          <th>Penjualan Tunai</th>
                          <th>Uang Diterima</th>
                          <th>Aksi</th>
                      </tr>
                  </thead>
                  <tbody>
                      @forelse ($allDatas as $data)
                          <tr>
                              <td>{{ $loop->iteration }}</td>
                              <td>{{ \Carbon\Carbon::parse($data->tanggal)->format('d-m-Y') }}</td>
                              <td>{{ $data->uraian }}</td>
                              <td>Rp {{ number_format($data->piutang_dagang ?? 0, 0, ',', '.') }}</td>
                              <td>Rp {{ number_format($data->penjualan_tunai ?? 0, 0, ',', '.') }}</td>
                              <td>Rp {{ number_format($data->uang_diterima ?? 0, 0, ',', '.') }}</td>
                              <td>
                                  <form id="deleteForm-{{ $data->id }}" action="{{ route('keuangan.pendapatan.destroy', $data->id) }}" method="POST" class="d-inline">
                                      @csrf
                                      @method('DELETE')
                                      <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $data->id }})">
                                          <i class="bi bi-trash"></i> Hapus
                                      </button>
                                  </form>
                              </td>
                          </tr>
                      @empty
                          <tr>
                              <td colspan="7" class="text-center text-muted py-3">
                                  <em>Belum ada data pendapatan yang tercatat.</em>
                              </td>
                          </tr>
                      @endforelse
                  </tbody>
                  <tfoot>
                      <tr>
                          <td colspan="6" class="text-center">Bunga Bank</td>
                          <td>Rp {{ number_format($bunga_bank->bunga_bank ?? 0, 0, ',', '.') }}</td>
                      </tr>
                      <tr>
                          <td colspan="6" class="text-center">Total</td>
                          <td><b>Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</b></td>
                      </tr>
                  </tfoot>
              </table>
          </div>

{{-- Form masukin data pendapatan nya ada pilihan apakah pendapatan tunai atau piutang (debit/kredit) --}}
{{-- Kalo pendapatn nya tunai lgsg ke buku besar pendapatan tunai --}}
{{-- Kalo pendapatn nya piutang lgsg ke buku besar piutang perusahaan DAN buku besar pendapatan tunai --}}

@endsection
@push('script')
<script>
    $(document).ready(function() {
        $('.pendapatan-table').DataTable({
            searching: false,
            paging: false,
            info: false,
            responsive: false
        });

        $('#allDatasTable').DataTable({
            paging: true,
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                emptyTable: "Tidak ada data untuk ditampilkan",
                search: "Cari:"
            }
        });
    });

    // SweetAlert konfirmasi hapus
    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data ini akan dihapus secara permanen!",
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
</script>
@endpush
