@extends('layouts.partial.layouts')

@section('page-title', 'Dashboard | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Dashboard')

@push('styles')
    <style>
        /* DataTables Dark Mode Fixes */
        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_length select,
        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_filter input {
            background-color: #1b1b29;
            color: #c2c2d9;
            border-color: #435ebe;
        }

        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_info,
        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate {
            color: #c2c2d9 !important;
        }

        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #c2c2d9 !important;
            border: 1px solid transparent;
        }

        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #435ebe !important;
            border-color: #435ebe !important;
            color: white !important;
        }

        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #25396f !important;
            border-color: #435ebe !important;
            color: white !important;
        }

        html[data-bs-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #607080 !important;
        }

        html[data-bs-theme="dark"] table.dataTable {
            border-color: #343a40 !important;
        }

        html[data-bs-theme="dark"] table.dataTable thead th {
            border-bottom: 2px solid #435ebe !important;
            color: #e9ecef;
        }

        html[data-bs-theme="dark"] .table-striped>tbody>tr:nth-of-type(odd)>* {
            --bs-table-accent-bg: rgba(255, 255, 255, 0.02);
        }

        /* Grouping row contrast in dark mode */
        html[data-bs-theme="dark"] .table-secondary.fw-bold {
            background-color: #25396f !important;
            color: #e9ecef !important;
        }
    </style>
@endpush

@section('section-row')
    <!-- Greetings Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm border-0 greeting-card">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    {{-- Left Side --}}
                    <div>
                        <h4 class="fw-bold mb-1 text-white">Selamat Datang, {{ Auth::user()->name }}! 👋</h4>
                        <p class="mb-0 opacity-75">Anda masuk sebagai <span
                                class="badge bg-white text-primary fw-bold">{{ strtoupper(Auth::user()->role) }}</span></p>
                    </div>
                    {{-- Right Side --}}
                    <div class="d-none d-md-block text-end">
                        <p class="mb-0 opacity-75 small">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM YYYY') }}</p>
                        <h5 class="mb-0 fw-bold" id="live-clock"></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="row mb-4 g-4">
        <!-- KAS -->
        <div class="col-md-3">
            <div class="card text-white shadow-lg kas-card h-100">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-wallet2 me-2"></i> Total Kas
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($totalKas, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-cash-stack icon-bg"></i>
                </div>
            </div>
        </div>

        <!-- PIUTANG -->
        <div class="col-md-3">
            <div class="card text-white shadow-lg piutang-card h-100">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-arrow-up-right-circle me-2"></i> Total Piutang
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-receipt icon-bg"></i>
                </div>
            </div>
        </div>

        <!-- HUTANG -->
        <div class="col-md-3">
            <div class="card text-white shadow-lg hutang-card h-100">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-arrow-down-left-circle me-2"></i> Total Hutang
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($totalHutang, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-credit-card-2-back icon-bg"></i>
                </div>
            </div>
        </div>

        <!-- PERSEDIAAN -->
        <div class="col-md-3">
            <div class="card text-white shadow-lg persediaan-card h-100">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-box-seam me-2"></i> Total Persediaan
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($totalPersediaan, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-archive icon-bg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <!-- LABA BERSIH -->
        <div class="col-md-6">
            <div class="card text-white shadow-lg laba-card h-100">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-trophy-fill me-2"></i> Laba Bersih
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($labaBersih, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-graph-up icon-bg"></i>
                </div>
            </div>
        </div>

        <!-- KAS KECIL -->
        <div class="col-md-6">
            <div class="card text-white shadow-lg kas-kecil-card h-100" style="background-color: #17a2b8; border:none;">
                <div class="card-body position-relative d-flex flex-column justify-content-between p-4">
                    <div>
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-wallet2 me-2"></i> Kas Kecil
                        </h5>
                        <p class="card-text fs-3 fw-bold mt-3 mb-0">
                            Rp {{ number_format($totalKasKecil, 0, ',', '.') }}
                        </p>
                    </div>
                    <i class="bi bi-cash icon-bg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaksi Kas --}}
    <div class="mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Buku Besar Kas</h5>
            </div>
            <div class="card-body">
                <div>
                    <table id="table-kas" class="table table-bordered table-striped mt-3">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Uraian</th>
                                <th>Penerimaan Kas Perusahaan</th>
                                <th>Pengeluaran Kas Perusahaan</th>
                                <th>Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detailKas as $kas)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($kas->created_at)->isoFormat('dddd, D MMMM YYYY') }}</td>
                                    <td>
                                        {{ $kas->uraian }}<br>
                                        <small class="text-muted"><i class="bi bi-clock"></i> Pukul
                                            {{ $kas->time ?? '-' }}</small>
                                    </td>
                                    <td>{{ number_format($kas->debit, 0, ',', '.') }}</td>
                                    <td>{{ number_format($kas->kredit, 0, ',', '.') }}</td>
                                    <td>{{ number_format($kas->saldo, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaksi Kas Kecil --}}
    <div class="mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Buku Kas Kecil</h5>
                <a href="{{ route('administrasi.kas-kecil.index') }}" class="btn btn-sm btn-primary">Lihat Selengkapnya</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-kas-kecil">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Referensi</th>
                                <th>Uraian</th>
                                <th>Nama Pemohon</th>
                                <th>Penerimaan Kas Perusahaan</th>
                                <th>Pengeluaran Kas Perusahaan</th>
                                <th>Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kasKecilData as $item)
                                <tr>
                                    <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>
                                    <td>{{ $item->nomor_referensi }}</td>
                                    <td>
                                        @if ($item->kasKecilLog->isEmpty())
                                            {{ $item->kasKecilDetail->pluck('keterangan')->join(', ') }}
                                        @else
                                            {{ $item->kasKecilLog->pluck('uraian')->join(', ') }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->kasKecilFormulir->pluck('nama_pemohon')->join(', ') ?? '-' }}
                                    </td>
                                    <td>Rp {{ number_format($item->penerimaan, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->pengeluaran, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->saldo_akhir, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Pendapatan vs Pengeluaran Kas Perusahaan -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Chart Pendapatan vs Pengeluaran Kas Perusahaan</h5>
                <div>
                    <select id="filterPeriode" class="form-select form-select-sm w-auto d-inline-block">
                        <option value="1">1 Bulan Terakhir</option>
                        <option value="6" selected>6 Bulan Terakhir</option>
                        <option value="12">1 Tahun Terakhir</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="financeChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabel Data Barang -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Data Barang dan Kartu Gudang Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-barang" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kode Barang</th>
                                <th>Stok Per-unit (Terbaru)</th>
                                <th>Stok Perkemasan (Terbaru)</th>
                                <th>Tanggal Update</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barangDenganKartuTerbaru as $index => $barang)
                                @php
                                    $kartu = $barang->kartuGudang->first();
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $barang->nama }}</td>
                                    <td>{{ $barang->kode_barang }}</td>
                                    <td>{{ $kartu->saldo_persatuan ?? '-' }}</td>
                                    <td>{{ $kartu->saldo_perkemasan ?? '-' }}</td>
                                    <td>{{ $kartu->created_at ?? '-' }}</td>
                                    <td>
                                        @if (empty($kartu->saldo_perkemasan) || empty($barang->jumlah_min))
                                            <span class="badge bg-secondary">Data Belum Ada</span>
                                        @elseif ($kartu->saldo_perkemasan > $barang->jumlah_min)
                                            <span class="badge bg-success">Stok Cukup</span>
                                        @elseif ($kartu->saldo_perkemasan - $barang->jumlah_min == 2)
                                            <span class="badge bg-warning">Isi Stok!</span>
                                        @else
                                            <span class="badge bg-danger">Stok Kurang</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Transaksi Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-transaksi" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Uraian</th>
                                <th>Jumlah</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaksiTerbaru as $transaksi)
                                <tr>
                                    <td>{{ $transaksi->tanggal }}</td>
                                    <td>{{ $transaksi->uraian }}</td>
                                    <td>Rp {{ number_format($transaksi->jumlah, 2, ',', '.') }}</td>
                                    <td>{{ $transaksi->tipe }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Piutang Perusahaan -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Semua Data Piutang Perusahaan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-piutang" class="table table-sm table-bordered table-striped">
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
                            @foreach ($listPiutang as $subLedgerId => $items)
                                <tr class="table-secondary fw-bold">
                                    <td>{{ $items->first()->sub_ledger->nama ?? 'Tidak diketahui' }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->uraian }}</td>
                                        <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hutang Perusahaan -->
    <div class="mb-4">
        <div class="card">
            <div class="card-header">
                <h5>Semua Data Hutang Perusahaan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-hutang" class="table table-sm table-bordered table-striped">
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
                            @foreach ($listHutang as $subLedgerId => $items)
                                <tr class="table-secondary fw-bold">
                                    <td>{{ $items->first()->sub_ledger->nama ?? 'Tidak diketahui' }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->uraian }}</td>
                                        <td>Rp {{ number_format($item->debit ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->kredit ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const tableOptions = {
                responsive: true,
                order: [],
            };
            $('#table-kas').DataTable(tableOptions);
            $('#table-barang').DataTable(tableOptions);
            $('#table-transaksi').DataTable(tableOptions);
            $('#table-piutang').DataTable(tableOptions);
            $('#table-hutang').DataTable(tableOptions);
            $('#table-kas-kecil').DataTable(tableOptions);
        });

        let chartInstance = null;

        function renderFinanceChart(labels, pendapatan, pengeluaran) {
            const ctx = document.getElementById('financeChart').getContext('2d');
            if (chartInstance) chartInstance.destroy(); // hapus chart lama jika ada

            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Pendapatan',
                            data: pendapatan,
                            backgroundColor: 'rgba(75, 192, 192, 0.7)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Pengeluaran Kas Perusahaan',
                            data: pengeluaran,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Pendapatan vs Pengeluaran Kas Perusahaan'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }

        // Render awal pakai data server
        renderFinanceChart(@json($months), @json($pendapatanPerBulan), @json($pengeluaranPerBulan));

        // Ubah data chart ketika filter berubah
        document.getElementById('filterPeriode').addEventListener('change', function() {
            const periode = this.value;
            fetch(`/dashboard/chart-data?periode=${periode}`)
                .then(res => res.json())
                .then(data => {
                    renderFinanceChart(data.labels, data.pendapatan, data.pengeluaran);
                })
                .catch(err => console.error(err));
        });

        // Sweet alert ketika stok dibawah minimum
        @php
            $barangKurang = $barangDenganKartuTerbaru->filter(function ($barang) {
                $kartu = $barang->kartuGudang->first();
                return $kartu && $kartu->saldo_perkemasan < $barang->jumlah_min;
            });
        @endphp

        @if ($barangKurang->count() > 0)
            let barangKurangList = `
              <ul style="text-align:left;">
                  @foreach ($barangKurang as $barang)
                      <li><b>{{ $barang->nama }}</b> — Stok: {{ $barang->kartuGudang->first()->saldo_perkemasan ?? 0 }}, Min: {{ $barang->jumlah_min }}</li>
                  @endforeach
              </ul>
          `;

            Swal.fire({
                icon: 'warning',
                title: '⚠️ Stok Barang di Bawah Minimum!',
                html: `Beberapa barang memiliki stok perkemasan di bawah batas minimum:<br><br>${barangKurangList}`,
                confirmButtonText: 'Oke, Saya Cek',
                confirmButtonColor: '#d33'
            });
        @endif
    </script>
@endpush
