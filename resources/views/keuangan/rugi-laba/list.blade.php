@extends('layouts.partial.layouts')
@section('page-title', 'Rugi Laba | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Rugi/Laba')


@section('section-row')
    <div class="card shadow-sm">
        <div
            class="card-header bg-primary text-white d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <h5 class="mb-0 text-white text-center text-lg-start">RUGI/LABA</h5>
            <div class="d-flex flex-column flex-md-row gap-2 w-100 w-lg-auto">
                <form method="GET" class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                    <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm"
                        required>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm"
                        required>
                    <button type="submit" class="btn btn-light btn-sm w-100 w-sm-auto">Filter</button>
                </form>

                <a href="{{ route('laporan-keuangan.rugi-laba.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="btn btn-danger btn-sm text-nowrap text-center w-100 w-md-auto" id="downloadPdfButton">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download to PDF
                    <span id="loadingSpinner" class="d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Loading...
                    </span>
                </a>
            </div>
        </div>

        <div class="card-body p-0" id="laporan-rugi-laba">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 pendapatan-table" style="min-width: 800px;">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 5%;">NO</th>
                            <th style="width: 55%;">URAIAN</th>
                            <th class="text-end" style="width: 20%;">JUMLAH</th>
                            <th class="text-end" style="width: 20%;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 1. Penjualan --}}
                        <tr>
                            <td class="text-center">1</td>
                            <td><strong>Penjualan</strong></td>
                            <td class="text-end">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td class="ps-4">Retur Penjualan</td>
                            <td class="text-end">Rp {{ number_format($returPenjualan, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">3</td>
                            <td class="ps-4">Potongan Penjualan</td>
                            <td class="text-end text-decoration-underline">Rp
                                {{ number_format($potonganPenjualan, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>
                        <tr class="table-info">
                            <td class="text-center">4</td>
                            <td><strong>Penjualan Bersih</strong></td>
                            <td></td>
                            <td class="text-end"><strong>Rp {{ number_format($penjualanBersih, 0, ',', '.') }}</strong></td>
                        </tr>

                        {{-- Sub heading --}}
                        <tr class="table-secondary">
                            <td></td>
                            <td><strong>Harga Pokok Penjualan</strong></td>
                            <td></td>
                            <td></td>
                        </tr>

                        {{-- 5-11. Persediaan / Pembelian --}}
                        <tr>
                            <td class="text-center">5</td>
                            <td><strong>Persediaan Barang Dagang Awal</strong></td>
                            <td class="text-end">Rp {{ number_format($persediaanAwal, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">6</td>
                            <td class="ps-4">Pembelian Kredit dan Tunai</td>
                            <td class="text-end">Rp {{ number_format($pembelianKredit + $pembelianTunai, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">7</td>
                            <td class="ps-4">Retur Pembelian</td>
                            <td class="text-end">Rp {{ number_format($returPembelian, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">8</td>
                            <td class="ps-4">Potongan Pembelian</td>
                            <td class="text-end text-decoration-underline">Rp
                                {{ number_format($potonganPembelian, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>
                        <tr class="table-secondary">
                            <td class="text-center">9</td>
                            <td><strong>Pembelian Bersih</strong></td>
                            <td class="text-end text-decoration-underline"><strong>Rp
                                    {{ number_format($pembelianBersih, 0, ',', '.') }} +</strong></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">10</td>
                            <td>Barang yang Tersedia untuk Dijual</td>
                            <td class="text-end">Rp {{ number_format($persediaanAwal + $pembelianBersih, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">11</td>
                            <td>Persediaan Barang Dagang Akhir</td>
                            <td class="text-end text-decoration-underline">Rp
                                {{ number_format($persediaanAkhir, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>

                        {{-- 12. HPP --}}
                        <tr class="table-danger">
                            <td class="text-center">12</td>
                            <td><strong>HPP (Harga Pokok Penjualan)</strong></td>
                            <td></td>
                            <td class="text-end text-decoration-underline"><strong>Rp
                                    {{ number_format($hpp, 0, ',', '.') }} -</strong></td>
                        </tr>

                        {{-- 13. Laba Kotor --}}
                        <tr class="table-success">
                            <td class="text-center">13</td>
                            <td class="text-center"><strong>Laba Kotor</strong></td>
                            <td></td>
                            <td class="text-end"><strong>Rp {{ number_format($labaKotor, 0, ',', '.') }}</strong></td>
                        </tr>

                        {{-- 14. Biaya Operasional --}}
                        <tr class="table-warning">
                            <td class="text-center">14</td>
                            <td>Biaya Operasional</td>
                            <td></td>
                            <td class="text-end text-decoration-underline">Rp
                                {{ number_format($biayaOperasional, 0, ',', '.') }} -</td>
                        </tr>

                        {{-- 15. Laba Operasional --}}
                        <tr class="table-success">
                            <td class="text-center">15</td>
                            <td class="text-center"><strong>Laba Operasional</strong></td>
                            <td></td>
                            <td class="text-end"><strong>Rp {{ number_format($labaOperasional, 0, ',', '.') }}</strong>
                            </td>
                        </tr>

                        {{-- 16-17. Pendapatan/Biaya lain --}}
                        <tr>
                            <td class="text-center">16</td>
                            <td>Pendapatan Lain-lain</td>
                            <td class="text-end">Rp {{ number_format($pendapatanLain, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center">17</td>
                            <td>Biaya Administrasi Bank</td>
                            <td class="text-end text-decoration-underline">Rp
                                {{ number_format($biayaAdministrasiBank, 0, ',', '.') }} -</td>
                            <td></td>
                        </tr>

                        {{-- 18. Total Pendapatan dan Biaya Lain-lain --}}
                        <tr class="table-info">
                            <td class="text-center">18</td>
                            <td><strong>Total Pendapatan dan Biaya Lain-lain</strong></td>
                            <td></td>
                            <td class="text-end text-decoration-underline"><strong>Rp
                                    {{ number_format($totalPendapatanBiayaLain, 0, ',', '.') }} +</strong></td>
                        </tr>

                        {{-- 19. Laba Sebelum Pajak --}}
                        <tr class="table-primary">
                            <td class="text-center">19</td>
                            <td class="text-center"><strong>Laba Sebelum Pajak</strong></td>
                            <td></td>
                            <td class="text-end"><strong>Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}</strong>
                            </td>
                        </tr>

                        {{-- 20. Pajak --}}
                        <tr class="table-warning">
                            <td class="text-center">20</td>
                            <td>Pajak UKM dan Nelayan 5%</td>
                            <td></td>
                            <td class="text-end text-decoration-underline">Rp {{ number_format($pajak, 0, ',', '.') }} -
                            </td>
                        </tr>

                        {{-- 21. Laba Bersih --}}
                        <tr class="table-success text-white bg-success">
                            <td class="text-center">21</td>
                            <td class="text-center"><strong>Laba Bersih</strong></td>
                            <td></td>
                            <td class="text-end text-decoration-underline"><strong>Rp
                                    {{ number_format($labaSetelahPajak, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
                responsive: false,
                columnDefs: [{
                        targets: [0, 1],
                        className: 'text-start'
                    },
                    {
                        targets: [2, 3],
                        className: 'text-end'
                    }
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
