@extends('layouts.partial.layouts')

@section('page-title', 'Uji Coba Langganan & Pembayaran')
@section('section-heading', 'Langganan & Paket Layanan')

@push('styles')
    <style>
        .subscription-stat-card {
            border-radius: 14px;
            transition: all 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .subscription-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .plan-catalog-card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.25s ease;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .plan-catalog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-color: #435ebe;
        }

        .plan-catalog-card.current-active-plan {
            border: 2px solid #198754;
            position: relative;
        }

        .badge-current-plan {
            position: absolute;
            top: -12px;
            right: 20px;
            background: #198754;
            color: #ffffff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(25, 135, 84, 0.35);
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
            background-color: rgba(255, 193, 7, 0.18);
            color: #996500;
            font-weight: 600;
        }

        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.12);
            color: #dc3545;
            font-weight: 600;
        }

        .badge-soft-secondary {
            background-color: rgba(108, 117, 125, 0.12);
            color: #6c757d;
            font-weight: 600;
        }

        .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.12);
            color: #0aa2c0;
            font-weight: 600;
        }

        .plan-code-badge {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.78rem;
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
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

        .pulse-dot.pending {
            background-color: #ffc107;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.3);
        }

        .pulse-dot.expired {
            background-color: #6c757d;
        }

        .pulse-dot.cancelled {
            background-color: #dc3545;
        }

        .feature-list-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #334155;
        }

        .feature-icon-check {
            color: #198754;
            font-size: 1.1rem;
            line-height: 1.2;
            flex-shrink: 0;
        }

        /* Dark Mode Support */
        html[data-bs-theme="dark"] .plan-catalog-card {
            background-color: #1e1e2d;
            border-color: #35354f;
        }

        html[data-bs-theme="dark"] .plan-catalog-card:hover {
            border-color: #435ebe;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
        }

        html[data-bs-theme="dark"] .plan-catalog-card.current-active-plan {
            border: 2px solid #198754;
        }

        html[data-bs-theme="dark"] .subscription-stat-card {
            background-color: #1e1e2d;
            border-color: #35354f;
        }

        html[data-bs-theme="dark"] .plan-code-badge {
            background-color: #2b2b40;
            color: #c2c2d9;
            border-color: #4b4b68;
        }

        html[data-bs-theme="dark"] .feature-list-item {
            color: #c2c2d9;
        }

        html[data-bs-theme="dark"] .badge-soft-primary {
            background-color: rgba(67, 94, 190, 0.25);
            color: #8fa5f7;
        }

        html[data-bs-theme="dark"] .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.25);
            color: #75b798;
        }

        html[data-bs-theme="dark"] .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.25);
            color: #ffda6a;
        }

        html[data-bs-theme="dark"] .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.25);
            color: #ea868f;
        }

        html[data-bs-theme="dark"] .badge-soft-secondary {
            background-color: rgba(108, 117, 125, 0.25);
            color: #a7acb1;
        }

        html[data-bs-theme="dark"] .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.25);
            color: #6edff6;
        }

        html[data-bs-theme="dark"] .card-header.bg-white {
            background-color: #1e1e2d !important;
            border-color: #35354f !important;
        }

        html[data-bs-theme="dark"] .border-top,
        html[data-bs-theme="dark"] .border-bottom {
            border-color: #35354f !important;
        }

        html[data-bs-theme="dark"] .text-dark {
            color: #e9ecef !important;
        }
    </style>
@endpush

@section('section-row')
    @php
        $now = now();
        // Cari semua langganan yang masih memiliki hak akses aktif (active atau cancelled tapi belum lewat masa aktif)
        $activeSubs = $subscriptions->filter(function ($s) use ($now) {
            $isNotExpired = !$s->ends_at || !$s->ends_at->isPast();

            if ($s->status->value === 'active' && $isNotExpired) {
                return true;
            }

            if ($s->status->value === 'cancelled' && $isNotExpired) {
                return true;
            }

            return false;
        });

        // Key-by plan_id untuk kemudahan pencarian per paket
        $activePlanSubMap = $activeSubs->keyBy('plan_id');

        // Untuk backward compatibility komponen ringkasan tunggal
        $activeSub = $activeSubs->first();

        $hasPendingPayment = $subscriptions->contains(function ($s) {
            $p = $s->payments->first();
            return $s->status->value === 'pending' || ($p && $p->status->value === 'pending');
        });
    @endphp

    <div class="row">
        {{-- Alert Messages --}}
        @if (session('error'))
            <div class="col-12 mb-3">
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="col-12 mb-3">
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        {{-- Banner Status Langganan Aktif Saya --}}
        <div class="col-12 mb-4">
            @if ($activeSub)
                @php
                    $isPermanent = is_null($activeSub->ends_at);
                    $daysRemaining = $activeSub->ends_at
                        ? max(0, (int) $now->diffInDays($activeSub->ends_at, false))
                        : null;
                    $isNearExpire = !$isPermanent && $daysRemaining !== null && $daysRemaining <= 7;
                @endphp
                <div class="card border-0 shadow-sm overflow-hidden"
                    style="background: linear-gradient(135deg, #435ebe 0%, #25396f 100%); color: #ffffff;">
                    <div class="card-body p-4 p-md-4">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-3 bg-white bg-opacity-20 rounded-circle text-white d-flex align-items-center justify-content-center"
                                    style="width: 56px; height: 56px;">
                                    <i class="bi bi-shield-check fs-2"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        @if ($activeSub->status->value === 'cancelled')
                                            <span class="badge bg-warning bg-opacity-75 text-dark px-2 py-1">
                                                <i class="bi bi-x-circle me-1"></i> Dibatalkan (Akses Berjalan)
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-75 text-white px-2 py-1">
                                                <i class="bi bi-check-circle me-1"></i> Langganan Aktif
                                            </span>
                                        @endif
                                        <span class="badge bg-white bg-opacity-25 text-white">
                                            {{ strtoupper($activeSub->plan?->billing_type->value ?? 'PLAN') }}
                                        </span>
                                    </div>
                                    <h3 class="fw-bold mb-1 text-white">{{ $activeSub->plan?->name ?? 'Paket Kustom' }}</h3>
                                    <p class="mb-0 text-white-50 small">
                                        Mulai aktif sejak:
                                        <strong>{{ $activeSub->starts_at?->format('d M Y') ?? '-' }}</strong>
                                        &bull;
                                        Berlaku hingga:
                                        @if ($isPermanent)
                                            <strong class="text-white"><i class="bi bi-infinity"></i> Akses Permanen /
                                                Selamanya</strong>
                                        @else
                                            <strong class="text-white">{{ $activeSub->ends_at->format('d M Y') }}</strong>
                                            <span
                                                class="ms-1 badge {{ $isNearExpire ? 'bg-warning text-dark' : 'bg-white bg-opacity-25 text-white' }}">
                                                @if ($daysRemaining == 0)
                                                    Berakhir Hari Ini
                                                @else
                                                    Sisa {{ $daysRemaining }} hari lagi
                                                @endif
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-lg-0">
                                <a href="#pilih-paket" class="btn btn-light fw-semibold text-primary px-3 py-2 shadow-sm">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Upgrade / Perpanjang Paket
                                </a>
                                <a href="#riwayat-transaksi" class="btn btn-outline-light px-3 py-2">
                                    <i class="bi bi-receipt me-1"></i> Riwayat Tagihan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm border-start border-4 border-warning">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 52px; height: 52px;">
                                    <i class="bi bi-exclamation-circle-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Belum Ada Paket Langganan Aktif</h5>
                                    <p class="text-muted small mb-0">
                                        Pilih salah satu paket di bawah untuk membuka akses penuh ke fitur laporan keuangan,
                                        pencetakan nota kasir thermal, dan fasilitas lainnya.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <a href="#pilih-paket" class="btn btn-primary px-3 py-2 text-nowrap">
                                    <i class="bi bi-cart-plus me-1"></i> Pilih Paket Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Ringkasan Statistik Singkat --}}
        @php
            $totalSubscriptions = $subscriptions->count();
            $totalPaid = $subscriptions->reduce(function ($carry, $sub) {
                $p = $sub->payments->first();
                return $p && $p->status->value === 'paid' ? $carry + $p->amount : $carry;
            }, 0);
            $pendingCount = $subscriptions
                ->filter(function ($sub) {
                    $p = $sub->payments->first();
                    return $sub->status->value === 'pending' || ($p && $p->status->value === 'pending');
                })
                ->count();
        @endphp

        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card subscription-stat-card shadow-sm h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Status Akun Saya</span>
                                <h5 class="fw-bold mt-1 mb-0">
                                    @if ($activeSub)
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Berlangganan
                                            Aktif</span>
                                    @else
                                        <span class="text-secondary"><i class="bi bi-dash-circle me-1"></i> Akun
                                            Gratis</span>
                                    @endif
                                </h5>
                            </div>
                            <div
                                class="p-2 rounded-3 {{ $activeSub ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                <i class="bi {{ $activeSub ? 'bi-patch-check-fill' : 'bi-person-badge' }} fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card subscription-stat-card shadow-sm h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Total Pengeluaran</span>
                                <h5 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalPaid, 0, ',', '.') }}
                                </h5>
                            </div>
                            <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card subscription-stat-card shadow-sm h-100 mb-0">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small text-uppercase fw-semibold">Transaksi Menunggu</span>
                                <h5 class="fw-bold {{ $pendingCount > 0 ? 'text-warning' : 'text-muted' }} mt-1 mb-0">
                                    {{ $pendingCount }} Menunggu Bayar
                                </h5>
                            </div>
                            <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Pilihan Paket Langganan --}}
        <div class="col-12 mb-5" id="pilih-paket">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-tags me-2 text-primary"></i> Pilihan Paket Langganan
                    </h5>
                    <p class="text-muted small mb-0">Pilih paket terbaik yang sesuai dengan kebutuhan operasional bisnis
                        Anda.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-muted border">
                        <i class="bi bi-shield-lock me-1"></i> Pembayaran Aman via Midtrans
                    </span>
                </div>
            </div>

            <div class="row g-4">
                @forelse($plans as $plan)
                    @php
                        $matchingActiveSub = $activePlanSubMap->get($plan->id);
                        $isCurrentActive = !is_null($matchingActiveSub);
                        $planFeatures = $plan->planFeatures;
                        $noticeDays = config('subscription.renewal_notice_days');
                        $canRenew =
                            $isCurrentActive &&
                            $matchingActiveSub->ends_at &&
                            now()->diffInDays($matchingActiveSub->ends_at, false) <= $noticeDays;
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="plan-catalog-card shadow-sm p-4 {{ $isCurrentActive ? 'current-active-plan' : '' }}">
                            @if ($isCurrentActive)
                                @if ($matchingActiveSub->status->value === 'cancelled')
                                    <div class="badge-current-plan bg-warning text-dark border border-warning">
                                        <i class="bi bi-clock-history me-1"></i> DIBATALKAN (AKSES AKTIF)
                                    </div>
                                @else
                                    <div class="badge-current-plan">
                                        <i class="bi bi-check-circle-fill me-1"></i> PAKET AKTIF ANDA
                                    </div>
                                @endif
                            @endif

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge badge-soft-primary">
                                    {{ strtoupper($plan->billing_type->value) }}
                                </span>
                                <span class="plan-code-badge">{{ $plan->code }}</span>
                            </div>

                            <h4 class="fw-bold text-dark mb-1">{{ $plan->name }}</h4>
                            <p class="text-muted small mb-3">
                                Akses
                                {{ $plan->scope->value === 'organization' ? 'Organisasi / Multi User' : 'Pengguna Individual' }}
                            </p>

                            <div class="mb-3 py-2 border-top border-bottom">
                                <div class="d-flex align-items-baseline">
                                    <span class="h2 fw-bold text-primary mb-0">
                                        Rp {{ number_format($plan->price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <span class="text-muted small">
                                    @if ($plan->duration_days)
                                        Masa aktif <strong>{{ $plan->duration_days }} hari</strong>
                                    @else
                                        <i class="bi bi-infinity text-success"></i> <strong>Akses Permanen</strong> (Sekali
                                        Bayar)
                                    @endif
                                </span>
                            </div>

                            {{-- Fitur Paket --}}
                            <div class="mb-4 flex-grow-1">
                                <div class="text-muted small fw-semibold text-uppercase mb-2">Fitur Termasuk:</div>
                                @if ($planFeatures && $planFeatures->count() > 0)
                                    @foreach ($planFeatures as $pf)
                                        @php
                                            $label = $pf->feature->label();
                                        @endphp
                                        <div class="feature-list-item">
                                            <i class="bi bi-check-circle-fill feature-icon-check"></i>
                                            <span>{{ $label }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="feature-list-item text-muted">
                                        <i class="bi bi-check-circle-fill feature-icon-check"></i>
                                        <span>Akses modul standar sistem</span>
                                    </div>
                                @endif
                            </div>

                            <div>
                                @if ($isCurrentActive)
                                    <div class="d-flex flex-column gap-2">
                                        @if ($matchingActiveSub->status->value === 'cancelled')
                                            <button type="button"
                                                class="btn btn-light text-warning w-100 py-2 fw-semibold border" disabled>
                                                <i class="bi bi-x-circle me-1"></i> Langganan Dibatalkan
                                            </button>
                                            <span class="text-muted small text-center">
                                                Akses tetap aktif s/d
                                                {{ $matchingActiveSub->ends_at?->format('d M Y') ?? 'selesai' }}
                                            </span>
                                        @else
                                            @if ($canRenew)
                                                <form action="{{ route('subscriptions.renew', $matchingActiveSub->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-outline-success w-100 py-2 fw-semibold">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Perpanjang Paket Ini
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button"
                                                    class="btn btn-light text-success w-100 py-2 fw-semibold border"
                                                    disabled>
                                                    <i class="bi bi-check-circle me-1"></i> Paket Sedang Aktif
                                                </button>
                                            @endif

                                            <form action="{{ route('subscriptions.cancel', $matchingActiveSub->id) }}"
                                                method="POST" class="form-cancel-subscription"
                                                data-plan-name="{{ $plan->name }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-1 fw-semibold">
                                                    <i class="bi bi-x-circle me-1"></i> Batalkan Langganan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <form action="{{ route('subscriptions.checkout', $plan->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                                            <i class="bi bi-credit-card me-1"></i>
                                            {{ $activeSub ? 'Upgrade ke Paket Ini' : 'Berlangganan Sekarang' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card shadow-sm border-0 text-center py-5">
                            <i class="bi bi-inbox text-muted fs-1 mb-2"></i>
                            <p class="text-muted mb-0">Belum ada paket langganan aktif yang dapat dipilih.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Riwayat Subscription & Transaksi --}}
        <div class="col-12" id="riwayat-transaksi">
            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-0">
                            <i class="bi bi-clock-history me-2 text-primary"></i> Riwayat Langganan & Transaksi Saya
                        </h5>
                        <p class="text-muted small mb-0">Semua riwayat pengajuan paket dan status pembayaran yang tercatat.
                        </p>
                    </div>
                    <span class="badge bg-light text-muted border px-2 py-1">
                        Total: {{ $subscriptions->count() }} Data
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="table-user-subscriptions">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Paket Layanan</th>
                                    <th>Status Langganan</th>
                                    <th>Periode Aktif</th>
                                    <th>Kode Referensi / Nominal</th>
                                    <th>Status Pembayaran</th>
                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscriptions as $index => $sub)
                                    @php
                                        $latestPayment = $sub->payments->first();
                                        $subBadgeClass = match ($sub->status->value) {
                                            'active' => 'badge-soft-success',
                                            'pending' => 'badge-soft-warning',
                                            'expired' => 'badge-soft-secondary',
                                            'cancelled' => 'badge-soft-danger',
                                            default => 'badge-soft-secondary',
                                        };
                                        $subPulseClass = match ($sub->status->value) {
                                            'active' => 'active',
                                            'pending' => 'pending',
                                            'expired' => 'expired',
                                            'cancelled' => 'cancelled',
                                            default => 'expired',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-box-seam fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $sub->plan?->name ?? 'Paket' }}
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 mt-1">
                                                        <span
                                                            class="plan-code-badge">{{ $sub->plan?->code ?? '-' }}</span>
                                                        @if ($sub->plan?->billing_type)
                                                            <span class="badge bg-light text-muted border">
                                                                {{ strtoupper($sub->plan->billing_type->value) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $subBadgeClass }} py-2 px-3">
                                                <span class="pulse-dot {{ $subPulseClass }}"></span>
                                                {{ strtoupper($sub->status->value) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($sub->starts_at)
                                                <div class="small">
                                                    <div class="text-dark fw-semibold">
                                                        <i class="bi bi-calendar-check me-1 text-primary"></i>
                                                        {{ $sub->starts_at->format('d M Y') }}
                                                    </div>
                                                    <div class="text-muted">
                                                        s/d
                                                        @if ($sub->ends_at)
                                                            {{ $sub->ends_at->format('d M Y') }}
                                                        @else
                                                            <span class="text-success fw-semibold"><i
                                                                    class="bi bi-infinity"></i> Permanen</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Menunggu aktivasi pembayaran</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($latestPayment)
                                                <div class="d-flex align-items-center gap-1">
                                                    <code class="small fw-semibold">{{ $latestPayment->reference }}</code>
                                                    <button type="button"
                                                        class="btn btn-sm btn-link p-0 text-muted copy-ref-btn"
                                                        data-clipboard-text="{{ $latestPayment->reference }}"
                                                        title="Salin Kode Referensi">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                                <div class="fw-bold text-dark small mt-1">
                                                    Rp {{ number_format($latestPayment->amount, 0, ',', '.') }}
                                                </div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($latestPayment)
                                                @php
                                                    $payBadge = match ($latestPayment->status->value) {
                                                        'paid' => 'badge-soft-success',
                                                        'pending' => 'badge-soft-warning',
                                                        'failed' => 'badge-soft-danger',
                                                        'expired' => 'badge-soft-secondary',
                                                        default => 'badge-soft-secondary',
                                                    };
                                                    $payIcon = match ($latestPayment->status->value) {
                                                        'paid' => 'bi-check-circle-fill',
                                                        'pending' => 'bi-hourglass-split',
                                                        'failed' => 'bi-x-circle-fill',
                                                        'expired' => 'bi-slash-circle',
                                                        default => 'bi-dash-circle',
                                                    };
                                                @endphp
                                                <span class="badge {{ $payBadge }} py-2 px-3">
                                                    <i class="bi {{ $payIcon }} me-1"></i>
                                                    {{ strtoupper($latestPayment->status->value) }}
                                                </span>
                                                @if ($latestPayment->paid_at)
                                                    <div class="text-muted small mt-1">
                                                        {{ $latestPayment->paid_at->format('d/m/Y H:i') }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                                @if ($latestPayment && $latestPayment->status->value === 'pending' && $latestPayment->checkout_url)
                                                    <a href="{{ $latestPayment->checkout_url }}" target="_blank"
                                                        class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                        <i class="bi bi-credit-card me-1"></i> Bayar
                                                    </a>
                                                @elseif($latestPayment && $latestPayment->status->value === 'paid')
                                                    <span class="badge badge-soft-success py-2 px-2">
                                                        <i class="bi bi-check2-all me-1"></i> Selesai
                                                    </span>
                                                    <a href="{{ route('subscriptions.invoice', $sub->id) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2 shadow-sm"
                                                        title="Cetak Bukti Pembayaran / Invoice">
                                                        <i class="bi bi-printer me-1"></i> Invoice
                                                    </a>
                                                @endif

                                                @if ($sub->status->value === 'active' && (!$sub->ends_at || !$sub->ends_at->isPast()))
                                                    <form action="{{ route('subscriptions.cancel', $sub->id) }}"
                                                        method="POST"
                                                        class="form-cancel-subscription d-inline"
                                                        data-plan-name="{{ $sub->plan?->name ?? 'paket ini' }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Batalkan Langganan">
                                                            <i class="bi bi-x-circle me-1"></i> Batal
                                                        </button>
                                                    </form>
                                                @elseif(!$latestPayment || ($latestPayment->status->value !== 'pending' && $latestPayment->status->value !== 'paid'))
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                            <p class="mb-0">Belum ada riwayat langganan dan transaksi pembayaran.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Salin Kode Referensi Helper
            $('.copy-ref-btn').on('click', function() {
                var textToCopy = $(this).data('clipboard-text');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Referensi disalin: ' + textToCopy,
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    });
                }
            });

            // Konfirmasi Pembatalan Langganan via SweetAlert
            $(document).on('submit', '.form-cancel-subscription', function(e) {
                e.preventDefault();
                const form = this;
                const planName = $(form).data('plan-name') || 'paket ini';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Batalkan Langganan?',
                        text: 'Apakah Anda yakin ingin membatalkan langganan ' + planName + '? Anda tetap dapat menikmati akses hingga masa aktif berakhir.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Batalkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Apakah Anda yakin ingin membatalkan langganan ' + planName + '?')) {
                        form.submit();
                    }
                }
            });
        });
    </script>
@endpush
