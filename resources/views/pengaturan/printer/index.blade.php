@extends('layouts.partial.layouts')
@section('page-title', 'Pengaturan Printer | TRANSDIGITAL')
@section('section-heading', 'Pengaturan Printer Thermal')
@section('section-row')

    <div class="row">
        <div class="col-md-6 col-12">
            <div class="card shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white pb-0">
                    <h5 class="fw-bold"><i class="bi bi-printer text-primary me-2"></i> Pengaturan Printer Bluetooth</h5>
                    <p class="text-muted small">Aktifkan pengaturan ini jika Anda menggunakan printer bluetooth (misal Iware
                        C58BT) untuk mencetak struk transaksi kasir.</p>
                </div>
                <div class="card-body mt-3">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('printer.update') }}" method="POST">
                        @csrf

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_printer_enabled"
                                name="is_printer_enabled" value="1" {{ $user->is_printer_enabled ? 'checked' : '' }}
                                style="transform: scale(1.3); margin-left: -2rem;">
                            <label class="form-check-label ms-3 fw-bold" for="is_printer_enabled">Aktifkan Cetak Struk (Web
                                Bluetooth API)</label>
                            <div class="text-muted small ms-3 mt-1">Jika aktif, sistem akan menawarkan popup pencetakan
                                setiap transaksi kasir berhasil. Fitur ini hanya didukung di browser Google Chrome, Edge,
                                dan Opera.</div>
                        </div>

                        <div class="mb-4">
                            <label for="printer_store_name" class="form-label fw-bold">Nama Toko di Struk</label>
                            <input type="text" class="form-control" id="printer_store_name" name="printer_store_name"
                                value="{{ old('printer_store_name', $user->printer_store_name ?? 'printer-' . auth()->user()->name) }}">
                            <div class="form-text">Nama toko yang akan dicetak pada bagian atas struk.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="bi bi-save me-1"></i>
                            Simpan Pengaturan</button>
                    </form>

                </div>
            </div>
        </div>
        <div class="col-md-6 col-12 mt-4 mt-md-0">
            <div class="card shadow-sm border-info" style="border-radius: 12px;">
                <div class="card-body">
                    <h6 class="fw-bold text-info"><i class="bi bi-info-circle me-1"></i> Tes Printer</h6>
                    <p class="text-muted small">Anda bisa melakukan test koneksi printer di bawah ini untuk memastikan
                        browser Anda dan printer sudah bisa terhubung dengan baik.</p>

                    <button type="button" class="btn btn-outline-info w-100" id="btn-test-print"><i
                            class="bi bi-printer me-1"></i> Lakukan Test Print</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script src="{{ asset('dist/assets/pos-printer.js') }}"></script>
    <script>
        document.getElementById('btn-test-print').addEventListener('click', function() {
            const testData = {
                toko: '{{ auth()->user()->name }}' || 'Nama Toko',
                alamat: '{{ auth()->user()->alamat }}' || 'Alamat',
                no_telp: '{{ auth()->user()->nomor_telepon }}' || 'Nomor Telepon',
                tanggal: new Date().toLocaleString('id-ID'),
                kasir: '{{ auth()->user()->name }}',
                kode_transaksi: 'TEST-12345',
                jenis_pembayaran: 'TUNAI',
                items: [{
                        nama: 'Item Tes 1',
                        qty: 1,
                        harga: 15000,
                        subtotal: 15000
                    },
                    {
                        nama: 'Item Tes 2',
                        qty: 2,
                        harga: 10000,
                        subtotal: 20000
                    }
                ],
                total: 35000,
                bayar: 50000,
                kembali: 15000
            };

            const printer = new PosPrinter();
            printer.printReceipt(testData);
        });
    </script>
@endpush
