@extends('layouts.superadmin-partial.layouts')

@section('page-title', 'Edit Paket ' . $plan->name . ' | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Edit Paket Langganan')

@push('styles')
    <style>
        .badge-soft-primary {
            background-color: rgba(67, 94, 190, 0.15);
            color: #435ebe;
            font-weight: 600;
        }

        .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
            font-weight: 600;
        }

        .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #b78103;
            font-weight: 600;
        }

        .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0aa2c0;
            font-weight: 600;
        }

        .badge-soft-danger {
            background-color: rgba(220, 53, 69, 0.15);
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
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            border: 1px solid var(--bs-border-color);
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
        }

        .preview-sticky {
            position: sticky;
            top: 24px;
            z-index: 10;
        }

        /* Feature Card & Dark Mode Compatibility */
        .feature-card-item {
            transition: all 0.2s ease;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-body-bg);
            cursor: pointer;
        }

        .feature-card-item:hover {
            border-color: #435ebe;
            background-color: var(--bs-tertiary-bg);
        }

        .feature-card-item.selected {
            border-color: #435ebe;
            background-color: rgba(67, 94, 190, 0.08);
        }

        /* Preview Card */
        .preview-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-body-bg);
        }

        .preview-header {
            background: linear-gradient(135deg, #435ebe 0%, #25396f 100%);
            color: #ffffff;
            padding: 1.5rem;
        }

        .spec-box {
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        /* Dark mode fine-tuning */
        [data-bs-theme="dark"] .badge-soft-primary {
            background-color: rgba(67, 94, 190, 0.3);
            color: #8da2fb;
        }

        [data-bs-theme="dark"] .badge-soft-success {
            background-color: rgba(25, 135, 84, 0.3);
            color: #75b798;
        }

        [data-bs-theme="dark"] .badge-soft-warning {
            background-color: rgba(255, 193, 7, 0.3);
            color: #ffda6a;
        }

        [data-bs-theme="dark"] .badge-soft-info {
            background-color: rgba(13, 202, 240, 0.3);
            color: #6edff6;
        }

        [data-bs-theme="dark"] .feature-card-item.selected {
            border-color: #8da2fb;
            background-color: rgba(67, 94, 190, 0.18);
        }

        [data-bs-theme="dark"] .plan-code-badge {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
        }
    </style>
@endpush

@section('section-row')

    {{-- Breadcrumb & Context Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <a href="{{ route('superadmin.plan.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="plan-code-badge py-2 px-3">
                {{ $plan->code }}
            </span>
            @if ($plan->isUsed())
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle py-2 px-3">
                    <i class="bi bi-people-fill me-1"></i>
                    {{ $plan->subscriptions_count ?? $plan->subscriptions()->count() }} Pelanggan
                </span>
            @endif
            @if ($plan->is_active)
                <span class="badge badge-soft-success py-2 px-3">
                    <span class="pulse-dot active"></span> Aktif
                </span>
            @else
                <span class="badge badge-soft-danger py-2 px-3">
                    <span class="pulse-dot inactive"></span> Nonaktif
                </span>
            @endif

            {{-- Delete Button --}}
            @if ($plan->isUsed())
                <button type="button" class="btn btn-outline-danger" disabled
                    title="Paket ini sudah memiliki pelanggan dan tidak dapat dihapus. Nonaktifkan saja jika tidak ingin digunakan lagi.">
                    <i class="bi bi-trash me-1"></i> Hapus Paket
                </button>
            @else
                <form action="{{ route('superadmin.plan.destroy', $plan) }}" method="POST" id="form-delete-plan" class="d-inline m-0">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-danger" id="btn-delete-plan">
                        <i class="bi bi-trash me-1"></i> Hapus Paket
                    </button>
                </form>
            @endif
        </div>
    </div>

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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                <div>
                    <strong>Terdapat kesalahan input:</strong>
                    <ul class="mb-0 mt-1 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $isUsed = $plan->isUsed();
        $currentFeatures = $plan->planFeatures
            ->map(fn($pf) => $pf->feature instanceof \App\Enum\Feature ? $pf->feature->value : (string) $pf->feature)
            ->toArray();
        $selectedFeatures = old('features', $currentFeatures);
        $currentScope = old('scope', $plan->scope instanceof \App\Enum\PlanScope ? $plan->scope->value : $plan->scope);
        $currentBillingType = old(
            'billing_type',
            $plan->billing_type instanceof \App\Enum\BillingType ? $plan->billing_type->value : $plan->billing_type,
        );
        $currentPrice = old('plan_price', $plan->price);
        $currentDuration = old('plan_duration', $plan->duration_days ?? 30);
    @endphp

    <form action="{{ route('superadmin.plan.update', $plan) }}" method="post" id="form-edit-plan">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Form Column (Left) --}}
            <div class="col-lg-7 col-xl-8">
                {{-- Card: Detail Utama --}}
                <div class="card shadow-sm border mb-4">
                    <div class="card-header border-bottom py-3">
                        <h6 class="fw-bold mb-0">
                            Informasi & Identitas Paket
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="plan_code" class="form-label fw-bold">
                                    Kode Paket <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" class="form-control @error('plan_code') is-invalid @enderror"
                                        id="plan_code" name="plan_code" placeholder="Contoh: PLAN-PRO-MONTHLY"
                                        value="{{ old('plan_code', $plan->code) }}" required>
                                </div>
                                <span class="text-muted small">Kode unik huruf kapital & tanpa spasi.</span>
                                @error('plan_code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="plan_name" class="form-label fw-bold">
                                    Nama Paket <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control @error('plan_name') is-invalid @enderror"
                                        id="plan_name" name="plan_name" placeholder="Contoh: Professional Organisasi"
                                        value="{{ old('plan_name', $plan->name) }}" required>
                                </div>
                                <span class="text-muted small">Nama yang akan tampil pada katalog pelanggan.</span>
                                @error('plan_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="scope"
                                    class="form-label fw-bold d-flex align-items-center justify-content-between">
                                    <span>Scope Akses <span class="text-danger">*</span></span>
                                    @if ($isUsed)
                                        <span
                                            class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning-subtle small py-1 px-2">
                                            <i class="bi bi-lock-fill me-1"></i> Terkunci (Sudah Dipakai)
                                        </span>
                                    @endif
                                </label>
                                <select class="form-select @error('scope') is-invalid @enderror" id="scope"
                                    name="scope" required {{ $isUsed ? 'disabled' : '' }}>
                                    @foreach (\App\Enum\PlanScope::cases() as $item)
                                        <option value="{{ $item->value }}"
                                            {{ $currentScope === $item->value ? 'selected' : '' }}>
                                            {{ $item->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($isUsed)
                                    <input type="hidden" name="scope" value="{{ $currentScope }}">
                                    <span class="text-muted small d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i> Scope dikunci karena paket sudah
                                        digunakan oleh pelanggan.
                                    </span>
                                @else
                                    <span class="text-muted small">Menentukan paket berlaku untuk personal user atau
                                        organisasi.</span>
                                @endif
                                @error('scope')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="billing_type"
                                    class="form-label fw-bold d-flex align-items-center justify-content-between">
                                    <span>Model Penagihan <span class="text-danger">*</span></span>
                                    @if ($isUsed)
                                        <span
                                            class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning-subtle small py-1 px-2">
                                            <i class="bi bi-lock-fill me-1"></i> Terkunci (Sudah Dipakai)
                                        </span>
                                    @endif
                                </label>
                                <select class="form-select @error('billing_type') is-invalid @enderror" id="billing_type"
                                    name="billing_type" required {{ $isUsed ? 'disabled' : '' }}>
                                    @foreach (\App\Enum\BillingType::cases() as $item)
                                        <option value="{{ $item->value }}"
                                            {{ $currentBillingType === $item->value ? 'selected' : '' }}>
                                            {{ $item->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($isUsed)
                                    <input type="hidden" name="billing_type" value="{{ $currentBillingType }}">
                                    <span class="text-muted small d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i> Model penagihan dikunci karena paket sudah
                                        sudah digunakan oleh pelanggan.
                                    </span>
                                @else
                                    <span class="text-muted small">Pilih Berulang (Siklus) atau Sekali Bayar
                                        (Lifetime).</span>
                                @endif
                                @error('billing_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-2">
                                <div
                                    class="p-3 rounded-3 border bg-body-tertiary d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-check-label fw-bold d-block mb-1" for="is_active">
                                            Status Publikasi Paket
                                        </label>
                                        <span class="text-muted small">
                                            Jika dinonaktifkan, paket tidak akan ditampilkan ke calon pelanggan baru.
                                            Pelanggan lama yang sudah aktif tetap dapat menggunakan paketnya.
                                        </span>
                                    </div>
                                    <div class="form-check form-switch ms-3">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input fs-4" type="checkbox" role="switch"
                                            id="is_active" name="is_active" value="1"
                                            {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Harga & Durasi --}}
                <div class="card shadow-sm border mb-4">
                    <div class="card-header border-bottom py-3">
                        <h6 class="fw-bold mb-0">
                            Harga & Masa Durasi
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="plan_price" class="form-label fw-bold">
                                    Tarif / Harga (IDR) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" class="form-control @error('plan_price') is-invalid @enderror"
                                        id="plan_price" name="plan_price" placeholder="0" value="{{ $currentPrice }}"
                                        required>
                                </div>
                                <span class="text-muted small">Masukkan 0 jika paket ini bersifat gratis
                                    (Trial/Free).</span>
                                @error('plan_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6" id="duration_wrapper">
                                <label for="plan_duration" class="form-label fw-bold">
                                    Durasi Aktif (Hari) <span class="text-danger" id="duration_asterisk">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control @error('plan_duration') is-invalid @enderror"
                                        id="plan_duration" name="plan_duration" min="1" placeholder="30"
                                        value="{{ $currentDuration }}" required>
                                    <span class="input-group-text">Hari</span>
                                </div>
                                <span class="text-muted small" id="duration_help">Contoh: 30 untuk 1 bulan, 365 untuk 1
                                    tahun.</span>
                                @error('plan_duration')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Hak Akses Fitur --}}
                <div class="card shadow-sm border mb-4">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold mb-0">
                                Hak Akses Fitur (Plan Features)
                                <span class="text-danger">*</span>
                            </h6>
                            <span class="text-muted small">Pilih fitur atau modul yang akan terbuka bagi pengguna paket
                                ini:</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                id="btn-select-all-features">Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-1"
                                id="btn-deselect-all-features">Reset</button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach (\App\Enum\Feature::cases() as $item)
                                <div class="col-md-6">
                                    <div class="feature-card-item rounded-3 p-3 h-100"
                                        onclick="document.getElementById('feature_{{ $item->value }}').click();">
                                        <div
                                            class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 p-2 rounded-circle bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-shield-check fs-5"></i>
                                                </div>
                                                <div>
                                                    <label class="form-check-label fw-bold cursor-pointer d-block"
                                                        for="feature_{{ $item->value }}">
                                                        {{ $item->label() }}
                                                    </label>
                                                </div>
                                            </div>
                                            <input class="form-check-input feature-checkbox ms-2" type="checkbox"
                                                name="features[]" role="switch" id="feature_{{ $item->value }}"
                                                value="{{ $item->value }}" data-label="{{ $item->label() }}"
                                                onclick="event.stopPropagation();"
                                                {{ in_array($item->value, $selectedFeatures) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('features')
                            <div class="text-danger small mt-3">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Action Button Mobile/Desktop Fallback --}}
                <div class="d-grid gap-2 mb-4 d-lg-none">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-save me-1"></i> Perbarui Paket Langganan
                    </button>
                    <a href="{{ route('superadmin.plan.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>

            {{-- Live Preview Column (Right) --}}
            <div class="col-lg-5 col-xl-4">
                <div class="preview-sticky">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase fw-bold text-muted small letter-spacing-1">
                            <i class="bi bi-eye me-1"></i> Pratinjau Paket
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">
                            <span class="pulse-dot active"></span> Live Preview
                        </span>
                    </div>

                    {{-- Preview Card Model --}}
                    <div class="preview-card">
                        <div class="preview-header">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="plan-code-badge fw-bold" id="preview_code">
                                    {{ $plan->code }}
                                </span>
                                <span class="badge bg-white bg-opacity-25 text-white" id="preview_scope">
                                    {{ $plan->scope->label() }}
                                </span>
                            </div>
                            <h4 class="fw-bold text-white mb-2" id="preview_name">{{ $plan->name }}</h4>
                            <div class="d-flex align-items-baseline gap-1 mt-3">
                                <h3 class="fw-bold text-white mb-0" id="preview_price">Rp
                                    {{ number_format($plan->price, 0, ',', '.') }}</h3>
                                <span class="text-white-50 small" id="preview_duration_text">
                                    {{ $plan->billing_type === \App\Enum\BillingType::Recurring ? '/ ' . ($plan->duration_days ? $plan->duration_days . ' Hari' : '- Hari') : '(Sekali Bayar)' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            {{-- Spec Info Grid --}}
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <div class="spec-box text-center">
                                        <span class="text-muted small d-block">Tipe Billing</span>
                                        <strong class="small d-block mt-1" id="preview_billing">
                                            {{ $plan->billing_type->label() }}
                                        </strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="spec-box text-center">
                                        <span class="text-muted small d-block">Masa Akses</span>
                                        <strong class="small d-block mt-1" id="preview_duration_badge">
                                            {{ $plan->billing_type === \App\Enum\BillingType::Recurring ? ($plan->duration_days ? $plan->duration_days . ' Hari' : '-') : 'Permanen / Lifetime' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Selected Features Checklist --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold small text-uppercase">Fitur Yang Didapat:</span>
                                    <span class="badge badge-soft-primary rounded-pill" id="preview_feature_count">0
                                        Fitur</span>
                                </div>

                                <ul class="list-unstyled mb-0" id="preview_features_list"
                                    style="max-height: 280px; overflow-y: auto;">
                                    <li class="text-muted small fst-italic py-2 text-center" id="preview_empty_features">
                                        <i class="bi bi-info-circle me-1"></i> Belum ada fitur yang dipilih
                                    </li>
                                </ul>
                            </div>

                            {{-- Submit Button Inside Sticky Card --}}
                            <div class="d-grid gap-2 pt-2 border-top">
                                <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Perbarui Paket
                                </button>
                                <a href="{{ route('superadmin.plan.index') }}" class="btn btn-outline-secondary py-2">
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Function to format Rupiah
            function formatRupiah(number) {
                if (number === null || number === '' || isNaN(number)) {
                    return 'Rp 0';
                }
                return 'Rp ' + parseInt(number, 10).toLocaleString('id-ID');
            }

            // Sync Form to Preview Card
            function updatePreview() {
                // Plan Code
                const codeVal = $('#plan_code').val().trim();
                $('#preview_code').text(codeVal ? codeVal.toUpperCase() : 'PLAN-CODE');

                // Plan Name
                const nameVal = $('#plan_name').val().trim();
                $('#preview_name').text(nameVal ? nameVal : 'Nama Paket');

                // Scope
                const scopeText = $('#scope option:selected').text().trim();
                $('#preview_scope').text(scopeText || 'Personal');

                // Billing Type
                const billingVal = $('#billing_type').val();
                const billingText = $('#billing_type option:selected').text().trim();
                $('#preview_billing').text(billingText || 'Berulang');

                // Price
                const rawPrice = $('#plan_price').val().replace(/\D/g, '');
                $('#preview_price').text(formatRupiah(rawPrice));

                // Duration handling based on Billing Type
                const durationVal = $('#plan_duration').val();
                const isRecurring = (billingVal === 'recurring');

                if (isRecurring) {
                    $('#duration_wrapper').slideDown(150);
                    $('#plan_duration').prop('required', true);
                    const days = durationVal ? durationVal + ' Hari' : '- Hari';
                    $('#preview_duration_text').text('/ ' + days);
                    $('#preview_duration_badge').text(days);
                } else {
                    // One Time / Lifetime
                    $('#duration_wrapper').slideUp(150);
                    $('#plan_duration').prop('required', false);
                    $('#preview_duration_text').text('(Sekali Bayar)');
                    $('#preview_duration_badge').text('Permanen / Lifetime');
                }

                // Features list sync
                const $featureCheckboxes = $('.feature-checkbox:checked');
                const featureCount = $featureCheckboxes.length;
                $('#preview_feature_count').text(featureCount + ' Fitur');

                const $featuresList = $('#preview_features_list');
                $featuresList.empty();

                if (featureCount === 0) {
                    $featuresList.append(`
                        <li class="text-muted small fst-italic py-2 text-center" id="preview_empty_features">
                            <i class="bi bi-info-circle me-1"></i> Belum ada fitur yang dipilih
                        </li>
                    `);
                } else {
                    $featureCheckboxes.each(function() {
                        const label = $(this).data('label');
                        $featuresList.append(`
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary-subtle">
                                <span class="small fw-semibold">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>${label}
                                </span>
                                <span class="badge badge-soft-success small py-1">Aktif</span>
                            </li>
                        `);
                    });
                }

                // Toggle card style on selection
                $('.feature-checkbox').each(function() {
                    const isChecked = $(this).is(':checked');
                    $(this).closest('.feature-card-item').toggleClass('selected', isChecked);
                });
            }

            // Auto uppercase & slug format on Plan Code
            $('#plan_code').on('input', function() {
                $(this).val($(this).val().toUpperCase().replace(/\s+/g, '-'));
                updatePreview();
            });

            // Listeners
            $('#plan_name, #plan_price, #plan_duration').on('input', updatePreview);
            $('#scope, #billing_type').on('change', updatePreview);
            $('.feature-checkbox').on('change', updatePreview);

            // Select all / Deselect all features
            $('#btn-select-all-features').on('click', function() {
                $('.feature-checkbox').prop('checked', true);
                updatePreview();
            });

            $('#btn-deselect-all-features').on('click', function() {
                $('.feature-checkbox').prop('checked', false);
                updatePreview();
            });

            // Initial call
            updatePreview();

            // SweetAlert Delete Plan confirmation
            $('#btn-delete-plan').on('click', function(e) {
                e.preventDefault();
                const form = $('#form-delete-plan');

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Paket Langganan?',
                        text: 'Paket "{{ $plan->name }}" ({{ $plan->code }}) beserta fitur yang terdaftar di dalamnya akan dihapus secara permanen!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Paket!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('Apakah Anda yakin ingin menghapus paket ini secara permanen?')) {
                        form.submit();
                    }
                }
            });
        });
    </script>
@endpush
