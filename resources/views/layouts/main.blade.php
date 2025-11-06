@extends('layouts.partial.layouts')

@section('page-title', 'Dashboard | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Dashboard')

@section('section-row')
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
        <div class="col">
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
    </div>


    <!-- Chart Pendapatan vs Pengeluaran -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Chart Pendapatan vs Pengeluaran</h5>
            <div>
                <select id="filterPeriode" class="form-select form-select-sm w-auto d-inline-block">
                    <option value="1">1 Bulan Terakhir</option>
                    <option value="6" selected>6 Bulan Terakhir</option>
                    <option value="12">1 Tahun Terakhir</option>
                </select>
            </div>
        </div>
        <canvas id="financeChart" height="200"></canvas>
    </div>

    <!-- Tabel Data Barang (seperti yang sudah ada) -->
    <div class="mb-4">
        <h5>Data Barang dan Kartu Gudang Terbaru</h5>
        <table class="table table-bordered table-striped mt-3">
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
                @forelse ($barangDenganKartuTerbaru as $index => $barang)
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
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data barang atau kartu gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="mb-4">
        <h5>Transaksi Terbaru</h5>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Uraian</th>
                    <th>Jumlah</th>
                    <th>Tipe</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksiTerbaru as $transaksi)
                    <tr>
                        <td>{{ $transaksi->tanggal }}</td>
                        <td>{{ $transaksi->uraian }}</td>
                        <td>Rp {{ number_format($transaksi->jumlah, 2, ',', '.') }}</td>
                        <td>{{ $transaksi->tipe }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


    </div>
@endsection
@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
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
                            label: 'Pengeluaran',
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
                            text: 'Pendapatan vs Pengeluaran'
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
