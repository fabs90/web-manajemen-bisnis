@extends('layouts.partial.layouts')

@section('page-title', 'Uji Coba Langganan & Pembayaran')
@section('section-heading', 'Uji Coba Langganan & Pembayaran')

@section('section-row')
<div class="row">
    {{-- Alert Messages --}}
    @if(session('error'))
        <div class="col-12 mb-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="col-12 mb-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Info Gateway Config --}}
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 border-start border-4 border-primary">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1"><i class="bi bi-gear-wide-connected me-1"></i> Konfigurasi Payment Gateway</h6>
                    <p class="text-muted small mb-0">
                        Default Gateway: <span class="badge bg-primary">{{ config('payment.default', 'midtrans') }}</span> |
                        Mode: <span class="badge bg-{{ config('services.midtrans.is_production') ? 'danger' : 'info' }}">
                            {{ config('services.midtrans.is_production') ? 'Production' : 'Sandbox' }}
                        </span>
                    </p>
                </div>
                <div class="text-md-end">
                    <span class="text-muted small d-block">Webhook Notification URL:</span>
                    <code class="small user-select-all">{{ route('webhooks.payments', ['provider' => 'midtrans']) }}</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Paket Langganan --}}
    <div class="col-12 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2"></i> Pilih Paket Langganan</h5>
            <span class="text-muted small">Klik tombol bayar untuk menguji redirect ke Midtrans Snap</span>
        </div>

        <div class="row g-4">
            @forelse($plans as $plan)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 position-relative">
                        <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                            <span class="badge bg-light text-primary fw-semibold mb-2">
                                {{ strtoupper($plan->billing_type->value) }} &bull; {{ strtoupper($plan->scope->value) }}
                            </span>
                            <h4 class="fw-bold text-dark mb-1">{{ $plan->name }}</h4>
                            <p class="text-muted small mb-0">{{ $plan->code }}</p>
                        </div>
                        <div class="card-body text-center d-flex flex-column justify-content-between">
                            <div class="my-3">
                                <h2 class="fw-bold text-primary mb-0">
                                    Rp {{ number_format($plan->price, 0, ',', '.') }}
                                </h2>
                                <span class="text-muted small">
                                    {{ $plan->duration_days ? 'Per ' . $plan->duration_days . ' hari' : 'Akses Selamanya (Permanen)' }}
                                </span>
                            </div>

                            <form action="{{ route('subscriptions.checkout', $plan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm">
                                    <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm border-0 text-center py-5">
                        <p class="text-muted mb-0">Belum ada paket langganan aktif yang tersedia.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Riwayat Subscription & Pembayaran --}}
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Langganan & Transaksi Saya
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Paket</th>
                                <th>Status Subscription</th>
                                <th>Periode Aktif</th>
                                <th>Reference / Pembayaran</th>
                                <th>Status Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $index => $sub)
                                @php
                                    $latestPayment = $sub->payments->first();
                                    $subBadgeClass = match($sub->status->value) {
                                        'active' => 'bg-success',
                                        'pending' => 'bg-warning text-dark',
                                        'expired' => 'bg-secondary',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-light text-dark',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $sub->plan?->name ?? '-' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $sub->plan?->code ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $subBadgeClass }}">
                                            {{ strtoupper($sub->status->value) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($sub->starts_at)
                                            <span class="small">{{ $sub->starts_at->format('d M Y') }} s/d {{ $sub->ends_at ? $sub->ends_at->format('d M Y') : 'Permanen' }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestPayment)
                                            <code class="small">{{ $latestPayment->reference }}</code>
                                            <br>
                                            <span class="small text-muted">Rp {{ number_format($latestPayment->amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestPayment)
                                            @php
                                                $payBadge = match($latestPayment->status->value) {
                                                    'paid' => 'bg-success',
                                                    'pending' => 'bg-warning text-dark',
                                                    'failed' => 'bg-danger',
                                                    'expired' => 'bg-secondary',
                                                    default => 'bg-light text-dark',
                                                };
                                            @endphp
                                            <span class="badge {{ $payBadge }}">
                                                {{ strtoupper($latestPayment->status->value) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestPayment && $latestPayment->status->value === 'pending' && $latestPayment->checkout_url)
                                            <a href="{{ $latestPayment->checkout_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Lanjut Bayar
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada riwayat langganan.
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
