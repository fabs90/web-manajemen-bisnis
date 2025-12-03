@extends('layouts.partial.layouts')

@section('page-title', 'Surat Pengiriman Barang | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

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
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                       <strong>Data Surat Pesanan Pembelian</strong>
                       <a href="{{ route('administrasi.spp.create') }}" class="btn btn-light btn-sm">
                           + Tambah SPP
                       </a>
                   </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="faktur-penjualan-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Nomor SPP</th>
                                <th>Pelanggan</th>
                                <th>Tanggal Pesan</th>
                                <th>Tanggal Kirim</th>
                                <th>Nama Bagian Pembelian</th>
                                <th>Detail Barang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                              <td class="text-center">{{ $loop->iteration }}</td>
                              <td>{{ $item->nomor_pesanan_pembelian }}</td>
                              <td>{{ $item->pelanggan->nama ?? '-' }}</td>
                              <td>{{ $item->tanggal_pesanan_pembelian }}</td>
                              <td>{{ $item->tanggal_kirim_pesanan_pembelian }}</td>
                              <td>{{ $item->nama_bagian_pembelian }}</td>
                              <td class="text-center">
                                  @if ($item->pesananPembelianDetail->count() > 0)
                                      <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                          data-bs-target="#detailModal{{ $item->id }}">
                                          Lihat Barang
                                      </button>

                                      {{-- Modal Detail Barang --}}
                                      <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1"
                                          aria-labelledby="modalLabel{{ $item->id }}" aria-hidden="true">
                                          <div class="modal-dialog modal-lg">
                                              <div class="modal-content">
                                                  <div class="modal-header bg-primary text-white">
                                                      <h5 class="modal-title" id="modalLabel{{ $item->id }}">
                                                          Detail Barang - SPP {{ $item->nomor_pesanan_pembelian }}
                                                      </h5>
                                                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                  </div>

                                                  <div class="modal-body">
                                                      <table class="table table-sm table-bordered text-center">
                                                          <thead class="table-light">
                                                              <tr>
                                                                  <th>Barang</th>
                                                                  <th>Qty</th>
                                                                  <th>Harga</th>
                                                                  <th>Total</th>
                                                              </tr>
                                                          </thead>
                                                          <tbody>
                                                              @foreach ($item->pesananPembelianDetail as $detail)
                                                                  <tr>
                                                                      <td>{{ $detail->nama_barang }}</td>
                                                                      <td>{{ $detail->kuantitas }}</td>
                                                                      <td>Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                                                      <td>Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                                                                  </tr>
                                                              @endforeach
                                                          </tbody>
                                                      </table>
                                                  </div>

                                                  <div class="modal-footer">
                                                      <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  @else
                                      <span class="text-muted">Tidak ada detail</span>
                                  @endif
                              </td>

                              <td class="text-center">
                              <a href="{{ route('administrasi.spp.generatePdf', $item->id) }}"
                                 class="btn btn-warning btn-sm"
                                 target="_blank">
                                  <i class="bi bi-file-earmark-pdf"></i>
                              </a>
                              <form action="{{ route('administrasi.spp.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm delete-btn"
                                        type="submit"
                                        title="Hapus"}>
                                    <span class="delete-text"><i class="bi bi-trash"></i></span>
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
            $('#faktur-penjualan-table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    emptyTable: "Belum ada Data Pesanan Pembelian (SPP)📪"
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
