@extends('layouts.partial.layouts')
@section('page-title', 'Tambah Retur Pembelian | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('section-heading', 'Retur Pembelian')
@section('section-row')

<div class="card shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('retur.store-pembelian') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Retur <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Kreditur <span class="text-danger">*</span></label>
                        <select name="nama_pelanggan" id="nama_pelanggan" class="form-control" required>
                            <option value="">-- Pilih Kreditur --</option>
                            @foreach ($kreditur as $d)
                                <option value="{{ $d->id }}" {{ old('nama_pelanggan') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="retur_keterangan" class="form-control" rows="2" placeholder="Contoh: Barang cacat dari supplier">{{ old('retur_keterangan') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Penanganan Retur <span class="text-danger">*</span></label>
                <select name="retur_penanganan" class="form-select" required>
                    <option value="kurangi_hutang" {{ old('retur_penanganan') == 'kurangi_hutang' ? 'selected' : '' }}>
                        Kurangi Hutang (Debit ke Utang Usaha)
                    </option>
                    <option value="tunai_kembali" {{ old('retur_penanganan') == 'tunai_kembali' ? 'selected' : '' }}>
                        Kembalikan Tunai (Debit ke Kas Utama)
                    </option>
                </select>
            </div>

            <hr>
            <h6 class="fw-bold mb-3">Item yang Diretur ke Supplier</h6>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-items">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Barang</th>
                            <th style="width: 20%">Qty</th>
                            <th style="width: 20%">Harga Beli</th>
                            <th style="width: 20%">Total</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="items[0][barang_id]" class="form-select barang-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($barang as $b)
                                        <option value="{{ $b->id }}" data-harga="{{ $b->harga_beli_per_unit }}">
                                            {{ $b->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][qty]" class="form-control qty-input" min="1" value="1" required>
                            </td>
                            <td>
                                <input type="text" name="items[0][harga]" class="form-control harga-input rupiah" required>
                            </td>
                            <td>
                                <input type="text" class="form-control total-input bg-light" readonly>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="fw-bold text-end" id="grand-total-display">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="add-row">+ Tambah Barang</button>
            </div>

            <div class="text-end">
                <a href="{{ route('retur.list') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-danger text-white">Simpan Retur Pembelian</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#table-items tbody');
    const addRowBtn = document.getElementById('add-row');
    const grandTotalDisplay = document.getElementById('grand-total-display');
    let rowCount = 1;

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    function calculateRowTotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const harga = AutoNumeric.getNumericElement(row.querySelector('.harga-input')).getNumber() || 0;
        const total = qty * harga;
        row.querySelector('.total-input').value = formatRupiah(total);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.harga-input').forEach((input, index) => {
            const qty = parseFloat(document.querySelectorAll('.qty-input')[index].value) || 0;
            const harga = AutoNumeric.getNumericElement(input).getNumber() || 0;
            grandTotal += qty * harga;
        });
        grandTotalDisplay.textContent = formatRupiah(grandTotal);
    }

    function initAutoNumeric(element) {
        new AutoNumeric(element, {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            currencySymbol: 'Rp ',
            unformatOnSubmit: true,
            minimumValue: '0'
        });
    }

    // Init first row
    initAutoNumeric(document.querySelector('.harga-input'));

    addRowBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="items[${rowCount}][barang_id]" class="form-select barang-select" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($barang as $b)
                        <option value="{{ $b->id }}" data-harga="{{ $b->harga_beli_per_unit }}">
                            {{ $b->nama }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][qty]" class="form-control qty-input" min="1" value="1" required>
            </td>
            <td>
                <input type="text" name="items[${rowCount}][harga]" class="form-control harga-input rupiah" required>
            </td>
            <td>
                <input type="text" class="form-control total-input bg-light" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(newRow);
        initAutoNumeric(newRow.querySelector('.harga-input'));
        rowCount++;
    });

    tableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('barang-select')) {
            const row = e.target.closest('tr');
            const selectedOption = e.target.options[e.target.selectedIndex];
            const harga = selectedOption.dataset.harga || 0;
            AutoNumeric.getNumericElement(row.querySelector('.harga-input')).set(harga);
            calculateRowTotal(row);
        }
    });

    tableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-input')) {
            calculateRowTotal(e.target.closest('tr'));
        }
    });

    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            if (document.querySelectorAll('#table-items tbody tr').length > 1) {
                e.target.closest('tr').remove();
                calculateGrandTotal();
            }
        }
    });
});
</script>
@endpush
