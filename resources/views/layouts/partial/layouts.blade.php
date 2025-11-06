@include('layouts.partial.header')

@include('layouts.partial.sidebar')

@include('layouts.partial.content')

@include('layouts.partial.footer')

@include('layouts.partial.scripts')

<button type="button" class="btn btn-primary rounded-circle shadow fab-btn" data-bs-toggle="modal"
    data-bs-target="#getStartedModal">
    <i class="bi bi-question-lg fs-4 mb-4"></i>
</button>

<div class="modal fade" id="getStartedModal" tabindex="-1" aria-labelledby="getStartedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex gap-4">
                    <i class="bi bi-lightbulb fs-5"></i>
                    <h5 class="modal-title text-white mb-0 mt-1" id="getStartedLabel">
                        Panduan Penggunaan
                    </h5>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-3">Selamat datang! 👋</h6>
                <p>Berikut langkah singkat untuk memulai penggunaan sistem ini:</p>

                <ol class="mb-4">
                    <li>Buka menu di sidebar untuk mengakses fitur utama seperti
                        <strong>Barang</strong>, <strong>Debitur-Kreditur</strong>, <strong>Pendapatan</strong>,
                        <strong>Pengeluaran</strong>, dan <strong>Neraca</strong>.
                    </li>
                    <li>Tambahkan terlebih dahulu <a href="http://127.0.0.1:8000/barang/create"><strong>Data Barang</strong></a> dan <a href="http://127.0.0.1:8000/debitur-kreditur/create"><strong>Kreditur-Debitur</strong></a></li>
                    <li>Isi <a href="http://127.0.0.1:8000/laporan-keuangan/aset-hutang/create"><strong> Hutang/Neraca Awal</strong></a> pada menu <strong>Aset
                            Hutang</strong>.
                    </li>
                    <li>
                        Masukan data pendapatan penjualan melalui menu
                        <a href="http://127.0.0.1:8000/keuangan/pendapatan/create"><strong>Pendapatan</strong></a> yang terdapat
                        didalam menu <strong>Transaksi Bisnis</strong>.
                    </li>
                    <li>
                        Masukan data pengeluaran pembelian melalui menu
                        <a href="http://127.0.0.1:8000/keuangan/pengeluaran/create"><strong>Pengeluaran</strong></a> yang
                        terdapat
                        didalam menu <strong>Transaksi Bisnis</strong>.
                    </li>
                    <li>
                        Untuk melihat hasil <a href="http://127.0.0.1:8000/laporan-keuangan/rugi-laba"><strong>Rugi Laba</strong></a> dan <a href="http://127.0.0.1:8000/laporan-keuangan/neraca-akhir"><strong>Neraca Akhir</strong></a> dapat diakses melalui menu
                        <strong>Transaksi
                            Bisnis</strong>.
                    </li>
                    <li>Setiap perubahan otomatis tercatat di Buku Besar dan laporan keuangan.</li>
                </ol>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Tips: Klik ikon <strong>?</strong> di pojok kanan bawah kapan saja untuk membuka panduan ini lagi.
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="{{route('dashboard.getStarted')}}">Halaman Get Started</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Button Styling -->
<style>
    .fab-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 55px;
        height: 55px;
        z-index: 1050;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .fab-btn:hover {
        transform: scale(1.1);
    }

    .modal-body a{
        color: #007bff;
        text-decoration: none;
    }
</style>
