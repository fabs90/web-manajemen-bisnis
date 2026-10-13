@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Manage Plan | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Kelola Paket Langganan')

@push('styles')
    <style>
        .stat-card {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .badge-soft-primary {
            background-color: rgba(67, 94, 190, 0.12);
            color: #435ebe;
            font-weight: 600;
        }

        .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.12);
            color: #198754;
            font-weight: 600;
        }

        .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b78103;
            font-weight: 600;
        }

        .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.12);
            color: #0aa2c0;
            font-weight: 600;
        }

        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.12);
            color: #dc3545;
            font-weight: 600;
        }

        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .pulse-dot.active {
            background-color: #198754;
            box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.3);
        }

        .pulse-dot.inactive {
            background-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.3);
        }

        .plan-code-badge {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.8rem;
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
        }

        .catalog-card {
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .catalog-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.1);
            border-color: #435ebe;
        }

        .catalog-card.featured {
            border: 2px solid #435ebe;
        }

        .feature-item-pill {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 0.78rem;
            color: #475569;
            margin-right: 4px;
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('section-row')

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>
                    <strong>Sukses!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>
                    <strong>Gagal!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Metric Overview Cards --}}
    @php
        $totalPlans = $plan->count();
        $activePlans = $plan->where('is_active', true)->count();
        $recurringPlans = $plan->where('billing_type', \App\Enum\BillingType::Recurring)->count();
        $oneTimePlans = $plan->where('billing_type', \App\Enum\BillingType::OneTime)->count();
    @endphp

    <div class="row g-3 mb-4">
        {{-- Total Plans --}}
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stat-card shadow-sm h-100 mb-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Total Paket</span>
                            <h3 class="fw-bold text-dark mt-1 mb-0">{{ $totalPlans }}</h3>
                            <span class="badge badge-soft-primary mt-2">Semua Paket</span>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Plans --}}
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stat-card shadow-sm h-100 mb-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Paket Aktif</span>
                            <h3 class="fw-bold text-success mt-1 mb-0">{{ $activePlans }}</h3>
                            <span class="badge badge-soft-success mt-2">Dapat Dilanggan</span>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recurring Plans --}}
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stat-card shadow-sm h-100 mb-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Model Berulang</span>
                            <h3 class="fw-bold text-info mt-1 mb-0">{{ $recurringPlans }}</h3>
                            <span class="badge badge-soft-info mt-2">Bulanan / Tahunan</span>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lifetime / One-time --}}
        <div class="col-6 col-lg-3 col-md-6">
            <div class="card stat-card shadow-sm h-100 mb-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small text-uppercase fw-semibold">Model Lifetime</span>
                            <h3 class="fw-bold text-warning mt-1 mb-0">{{ $oneTimePlans }}</h3>
                            <span class="badge badge-soft-warning mt-2">Sekali Bayar</span>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-collection-play me-2 text-primary"></i>Daftar Paket Langganan
                    </h5>
                    <p class="text-muted small mb-0">
                        Kelola katalog harga, model penagihan, durasi, dan hak akses fitur pada setiap paket.
                    </p>
                </div>

                <div>
                    <a href="{{ route('superadmin.plan.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus me-1"></i> Tambah Paket
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body pt-4">
            <div class="tab-content" id="planViewTabContent">

                <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="table-manage-plan" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Paket & Kode</th>
                                    <th>Scope</th>
                                    <th>Tipe Billing</th>
                                    <th>Tarif / Harga</th>
                                    <th>Masa Aktif</th>
                                    <th>Fitur Termasuk</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plan as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-box fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark fs-6">{{ $item->name }}</div>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <span class="plan-code-badge me-2">{{ $item->code }}</span>
                                                        <button type="button"
                                                            class="btn btn-sm btn-link p-0 text-muted copy-btn"
                                                            data-clipboard-text="{{ $item->code }}"
                                                            title="Salin Kode Paket">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->scope === \App\Enum\PlanScope::User)
                                                <span class="badge badge-soft-info py-2 px-2-5">
                                                    <i class="bi bi-person-fill me-1"></i> User
                                                </span>
                                            @else
                                                <span class="badge badge-soft-primary py-2 px-2-5">
                                                    <i class="bi bi-buildings-fill me-1"></i> Organisasi
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->billing_type === \App\Enum\BillingType::Recurring)
                                                <span class="badge badge-soft-primary py-2 px-2-5">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Berlangganan
                                                </span>
                                            @else
                                                <span class="badge badge-soft-warning py-2 px-2-5">
                                                    <i class="bi bi-patch-check-fill me-1"></i> Sekali Bayar
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6">
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </div>
                                            <span class="text-muted small">
                                                @if ($item->billing_type === \App\Enum\BillingType::Recurring)
                                                    {{ $item->duration_days ? 'Per ' . $item->duration_days . ' hari' : 'Siklus berulang' }}
                                                @else
                                                    Akses seumur hidup
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->duration_days)
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                                                    {{ $item->duration_days }} Hari
                                                </span>
                                            @else
                                                <span class="badge bg-light text-success border">
                                                    <i class="bi bi-infinity me-1 text-success"></i> Permanen
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-soft-primary rounded-pill px-3 py-2">
                                                    <i class="bi bi-stars me-1"></i> {{ $item->planFeatures->count() }}
                                                    Fitur
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge badge-soft-success py-2 px-3">
                                                    <span class="pulse-dot active"></span> Aktif
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger py-2 px-3">
                                                    <span class="pulse-dot inactive"></span> Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                data-bs-toggle="modal" data-bs-target="#modal-plan-{{ $item->id }}"
                                                title="Lihat Rincian Paket">
                                                <i class="bi bi-eye me-1"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Detail Modals for Each Plan --}}
    @foreach ($plan as $item)
        <div class="modal fade" id="modal-plan-{{ $item->id }}" tabindex="-1"
            aria-labelledby="modalLabel-{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0" id="modalLabel-{{ $item->id }}">
                                    {{ $item->name }}
                                </h5>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="plan-code-badge">{{ $item->code }}</span>
                                    @if ($item->is_active)
                                        <span class="badge badge-soft-success py-1 px-2">
                                            <span class="pulse-dot active"></span> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger py-1 px-2">
                                            <span class="pulse-dot inactive"></span> Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        {{-- Spec Cards Grid --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 bg-light text-center border">
                                    <span class="text-muted small d-block">Scope Akses</span>
                                    <strong class="text-dark d-block mt-1">
                                        {{ ucfirst($item->scope->value) }}
                                    </strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 bg-light text-center border">
                                    <span class="text-muted small d-block">Tipe Tagihan</span>
                                    <strong class="text-dark d-block mt-1">
                                        {{ $item->billing_type === \App\Enum\BillingType::Recurring ? 'Berulang' : 'Sekali Bayar' }}
                                    </strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 bg-light text-center border">
                                    <span class="text-muted small d-block">Masa Durasi</span>
                                    <strong class="text-dark d-block mt-1">
                                        {{ $item->duration_days ? $item->duration_days . ' Hari' : 'Permanen' }}
                                    </strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-3 bg-light text-center border">
                                    <span class="text-muted small d-block">Tarif Paket</span>
                                    <strong class="text-primary d-block mt-1">
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        {{-- Features Section --}}
                        <div class="card border mb-0">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="bi bi-stars text-warning me-2"></i>Hak Akses Fitur Terbuka
                                    </h6>
                                    <span class="badge badge-soft-primary">
                                        {{ $item->planFeatures->count() }} Fitur Terdaftar
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                @if ($item->planFeatures->count() > 0)
                                    <div class="row g-2">
                                        @foreach ($item->planFeatures as $pf)
                                            @php
                                                $featureEnum = $pf->feature;
                                                $label =
                                                    $featureEnum instanceof \App\Enum\Feature
                                                        ? $featureEnum->label()
                                                        : $pf->feature ?? '-';
                                                $codeName =
                                                    $featureEnum instanceof \App\Enum\Feature
                                                        ? $featureEnum->value
                                                        : $pf->feature;
                                            @endphp
                                            <div class="col-md-6">
                                                <div
                                                    class="p-3 rounded-3 border bg-white d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="me-3 p-2 rounded-circle bg-success bg-opacity-10 text-success">
                                                            <i class="bi bi-check-lg fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-dark">{{ $label }}</div>
                                                            <code class="text-muted small">{{ $codeName }}</code>
                                                        </div>
                                                    </div>
                                                    <span class="badge badge-soft-success">Unlocked</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-lock fs-2 d-block mb-2 text-secondary"></i>
                                        <p class="mb-0 small">Belum ada fitur khusus yang ditugaskan pada paket ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-2">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill"
                            data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            if ($.fn.DataTable.isDataTable('#table-manage-plan')) {
                $('#table-manage-plan').DataTable().destroy();
            }

            $('#table-manage-plan').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari paket atau kode...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ paket",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 paket",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada paket yang sesuai pencarian",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Clipboard Copy Helper
            $('.copy-btn').on('click', function() {
                var textToCopy = $(this).data('clipboard-text');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Kode disalin: ' + textToCopy,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
