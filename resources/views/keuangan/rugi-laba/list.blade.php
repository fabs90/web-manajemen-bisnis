@extends('layouts.partial.layouts')
@section('page-title', 'Rugi/Laba')
@section('section-heading', 'Rugi/Laba')

@section('section-row')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">RUGI/LABA</h5>
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm" required>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm" required>
            <button type="submit" class="btn btn-light btn-sm">Filter</button>
        </form>

        <a href="{{ route('keuangan.pengeluaran.rugi-laba.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="btn btn-danger btn-sm" id="downloadPdfButton">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download to PDF
            <span id="loadingSpinner" class="d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Loading...
            </span>
        </a>
    </div>

    <div class="card-body p-0" id="laporan-rugi-laba">
        <table class="table table-bordered table-striped mb-0 pendapatan-table">
            <thead class="bg-light">
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="60%">URAIAN</th>
                    <th width="17%" class="text-end">JUMLAH</th>
                    <th width="18%" class="text-end">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Penjualan Tunai + Kredit -->
                <tr>
                    <td class="text-center">1</td>
                    <td><strong>Penjualan Tunai + Kredit</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="ps-5">Retur Penjualan</td>
                    <td class="text-end">Rp {{ number_format($returPenjualan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="ps-5">Potongan Penjualan</td>
                    <td class="text-end">Rp {{ number_format($potonganPenjualan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr class="table-info">
                    <td class="text-center">4</td>
                    <td><strong>Penjualan Bersih</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($penjualanBersih, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 5. Persediaan Awal -->
                <tr>
                    <td class="text-center">5</td>
                    <td><strong>Persediaan Barang Dagangan Awal</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</td>
                </tr>

                <!-- 6. Pembelian Kredit + Tunai -->
                <tr>
                    <td class="text-center">6</td>
                    <td><strong>Pembelian Secara Kredit + Tunai</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($pembelianKredit + $pembelianTunai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="ps-5">Retur Pembelian</td>
                    <td class="text-end">Rp {{ number_format($returPembelian, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="ps-5">Potongan Pembelian</td>
                    <td class="text-end">Rp {{ number_format($potonganPembelian, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr class="table-secondary">
                    <td class="text-center">9</td>
                    <td><strong>Pembelian Bersih</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($pembelianBersih, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 10. Barang Tersedia -->
                <tr class="table-warning">
                    <td class="text-center">10</td>
                    <td><strong>Barang yang Tersedia untuk Dijual</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($persediaanAwal + $pembelianBersih, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 11. Persediaan Akhir -->
                <tr>
                    <td class="text-center">11</td>
                    <td><strong>Persediaan Barang Dagangan Akhir</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($persediaanAkhir, 0, ',', '.') }}</td>
                </tr>

                <!-- 12. HPP -->
                <tr class="table-danger text-white bg-danger">
                    <td class="text-center">12</td>
                    <td><strong>HPP (Harga Pokok Penjualan)</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($hpp, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 13. Laba Kotor -->
                <tr class="table-success">
                    <td class="text-center">13</td>
                    <td><strong>Laba Kotor</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($labaKotor, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 14. Biaya Operasional -->
                <tr>
                    <td class="text-center">14</td>
                    <td><strong>Biaya Operasional</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($biayaOperasional, 0, ',', '.') }}</td>
                </tr>

                <!-- 15. Laba Operasional -->
                <tr class="table-primary">
                    <td class="text-center">15</td>
                    <td><strong>Laba Operasional</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($labaOperasional, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 16. Pendapatan Lain-lain -->
                <tr>
                    <td class="text-center">16</td>
                    <td><strong>Pendapatan Lain-lain</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($pendapatanLain, 0, ',', '.') }}</td>
                </tr>

                <!-- 17. Biaya Admin Bank -->
                <tr>
                    <td class="text-center">17</td>
                    <td><strong>Biaya Administrasi Bank</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($biayaAdministrasiBank, 0, ',', '.') }}</td>
                </tr>

                <!-- 18. Laba Sebelum Pajak -->
                <tr class="table-info">
                    <td class="text-center">18</td>
                    <td><strong>Laba Sebelum Pajak</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}</strong></td>
                </tr>

                <!-- 19. Pajak -->
                <tr>
                    <td class="text-center">19</td>
                    <td><strong>Pajak (15%)</strong></td>
                    <td></td>
                    <td class="text-end">Rp {{ number_format($pajak, 0, ',', '.') }}</td>
                </tr>

                <!-- 20. Laba Setelah Pajak -->
                <tr class="table-success text-white bg-success">
                    <td class="text-center">20</td>
                    <td><strong>Laba Setelah Pajak</strong></td>
                    <td></td>
                    <td class="text-end"><strong>Rp {{ number_format($labaSetelahPajak, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('.pendapatan-table').DataTable({
        searching: false,
        paging: false,
        info: false,
        ordering: false,
        responsive: true,
        columnDefs: [
            { targets: [0, 1], className: 'text-start' },
            { targets: [2, 3], className: 'text-end' }
        ]
    });
});

$('#downloadPdfButton').on('click', function() {
    var button = $(this);
    var loadingSpinner = $('#loadingSpinner');
    loadingSpinner.removeClass('d-none');
    button.addClass('disabled').attr('disabled', true);

    setTimeout(function() {
        loadingSpinner.addClass('d-none');
        button.removeClass('disabled').attr('disabled', false);
    }, 5000);
});
</script>
@endpush
