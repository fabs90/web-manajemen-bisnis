@extends('layouts.partial.layouts')

@section('page-title', 'Tambah Memo Kredit dari Penjual | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-row')

    {{-- Alert Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <a href="{{ route('administrasi.memo-kredit.penjual') }}" class="btn btn-light btn-sm me-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h5 class="mb-0"><strong>TAMBAH MEMO KREDIT DARI PENJUAL (RETUR PEMBELIAN)</strong></h5>
        </div>
        <div class="card-body">
            <form action="{{ route('administrasi.memo-kredit.store-penjual') }}" method="POST" id="formMemoKreditPenjual">
                @csrf
                <input type="hidden" name="spp_id" value="{{ $spp->id }}">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Nomor SPP</label>
                        <input type="text" class="form-control" value="{{ $spp->nomor_pesanan_pembelian }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" class="form-control" value="{{ $spp->supplier->nama ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor Retur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nomor_retur') is-invalid @enderror" name="nomor_retur"
                            value="{{ old('nomor_retur') }}" required placeholder="Masukkan Nomor Retur">
                        @error('nomor_retur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                            value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label">Alasan Pengembalian <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alasan_pengembalian') is-invalid @enderror" name="alasan_pengembalian"
                            rows="3" required placeholder="Contoh: Barang cacat, tidak sesuai pesanan, dll">{{ old('alasan_pengembalian') }}</textarea>
                        @error('alasan_pengembalian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>
                <h6 class="fw-bold mb-3">Pilih Barang yang Dikembalikan</h6>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Harga Beli per-Satuan</th>
                                <th>Qty Pesanan</th>
                                <th>Qty Dikembalikan</th>
                                <th>Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($spp->pesananPembelianDetail as $index => $detail)
                                <tr>
                                    <td>
                                        <input type="hidden" name="barang_id[]" value="{{ $detail->id }}">
                                        {{ $detail->nama_barang }}
                                    </td>
                                    <td>
                                        <input type="text" class="form-control harga-satuan" name="harga[]"
                                            value="Rp {{ number_format($detail->barang->harga_beli_per_unit ?? 0, 0, ',', '.') }}"
                                            readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control max-qty" value="{{ $detail->kuantitas }}"
                                            readonly>
                                    </td>
                                    <td>
                                        <input type="number"
                                            class="form-control qty-input @error('jumlah_dikembalikan.' . $index) is-invalid @enderror"
                                            name="jumlah_dikembalikan[]"
                                            value="{{ old('jumlah_dikembalikan.' . $index, 0) }}" min="0"
                                            max="{{ $detail->kuantitas }}">
                                        @error('jumlah_dikembalikan.' . $index)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal" name="total[]" value="Rp 0"
                                            readonly>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Pengembalian</th>
                                <th>
                                    <input type="text" class="form-control" id="totalSemua" name="total_keseluruhan"
                                        value="Rp 0" readonly>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="bi bi-save"></i> Simpan Memo Kredit
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('script')
    <script>
        $(document).ready(function() {
            function cleanRupiah(str) {
                return parseInt(str.replace(/[^0-9]/g, '')) || 0;
            }

            function formatRupiah(angka) {
                return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function calculateTotals() {
                let totalKeseluruhan = 0;

                $('.qty-input').each(function() {
                    let tr = $(this).closest('tr');
                    let qty = parseInt($(this).val()) || 0;
                    let maxQty = parseInt(tr.find('.max-qty').val());
                    let hargaStr = tr.find('.harga-satuan').val();
                    let harga = cleanRupiah(hargaStr);

                    // Validasi qty tidak boleh melebihi max
                    if (qty > maxQty) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops...',
                            text: 'Kuantitas tidak boleh melebihi jumlah pesanan (' + maxQty + ')',
                        });
                        $(this).val(maxQty);
                        qty = maxQty;
                    }

                    // Hitung subtotal
                    let subtotal = qty * harga;
                    tr.find('.subtotal').val(formatRupiah(subtotal));

                    totalKeseluruhan += subtotal;
                });

                $('#totalSemua').val(formatRupiah(totalKeseluruhan));
            }

            // Event listener saat qty diubah
            $('.qty-input').on('input', function() {
                calculateTotals();
            });

            // Hitung awal (saat page load, misal ada old() value)
            calculateTotals();

            // Submit form logic
            $('#formMemoKreditPenjual').on('submit', function(e) {
                e.preventDefault();
                let form = this;
                let total = cleanRupiah($('#totalSemua').val());

                if (total <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Pilih minimal satu barang yang akan dikembalikan!',
                    });
                    return false;
                }

                Swal.fire({
                    title: 'Simpan Memo Kredit (Retur Pembelian)?',
                    text: "Data akan disimpan dan stok barang di gudang akan otomatis dikurangi.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        let btn = $(form).find('#btnSubmit');
                        btn.prop('disabled', true);
                        btn.html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...'
                        );
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
