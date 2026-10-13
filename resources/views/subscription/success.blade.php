@extends('layouts.partial.layouts')

@section('page-title', 'Status Pembayaran')
@section('section-heading', 'Status Pembayaran')

@section('section-row')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 text-center p-4">
            <div class="card-body">
                <div class="mb-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Terima Kasih!</h3>
                <p class="text-muted mb-4">
                    Transaksi pembayaran Anda telah diproses. Webhook Midtrans akan secara otomatis memperbarui status langganan Anda dalam beberapa saat.
                </p>

                @if($orderId)
                    <div class="p-3 bg-light rounded text-start mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Order ID / Reference:</span>
                            <code class="fw-bold">{{ $orderId }}</code>
                        </div>
                        @if($transactionStatus)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Transaction Status:</span>
                                <span class="badge bg-primary text-uppercase">{{ $transactionStatus }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('subscriptions.index') }}" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Langganan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
