@extends('layouts.partial.layouts')

@section('page-title', 'Tambah Memo Kredit | Digitrans')

@section('section-row')

<div class="container mt-4">
    <div class="card p-4 shadow">

        <h5 class="text-center fw-bold mb-4">MEMO KREDIT</h5>

        <form action="{{ route('administrasi.memo-kredit.store') }}" method="POST">
            @csrf
            <input type="hidden" name="faktur_id" value="{{ $faktur->id }}">

            {{-- Bagian Detail Faktur --}}
            <table class="table table-bordered mb-3 align-middle">
                <tbody>
                    <tr>
                        <td style="width: 25%">Kepada</td>
                        <td>{{ $faktur->pelanggan->nama ?? '-' }}</td>
                        <td style="width: 25%">Tanggal Memo Kredit</td>
                        <td>
                            <input type="date" name="tanggal" class="form-control" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>{{ $faktur->pelanggan->alamat ?? '-' }}</td>
                        <td>Nomor Memo Kredit</td>
                        <td>
                            <input type="text" name="nomor_memo" class="form-control"
                                   placeholder="Contoh: 001/MK/12/2025" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Nomor Faktur</td>
                        <td>{{ $faktur->kode_faktur }}</td>
                        <td>Tanggal Faktur</td>
                        <td>{{ $faktur->tanggal }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Pesanan</td>
                        <td>{{ $faktur->nomor_pesanan }}</td>
                        <td>Jenis Pengiriman</td>
                        <td>{{ $faktur->jenis_pengiriman ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
            <p class="fw-bold">Rekening saudara sudah kami kredit dengan jumlah sebagai berikut:</p>
            <table class="table table-bordered text-center align-middle" id="table-retur">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">Kuantitas</th>
                        <th>Nama Barang</th>
                        <th style="width: 20%">Harga/Kemas</th>
                        <th style="width: 20%">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <input type="number" name="jumlah_dipesan[]"
                                   class="form-control" min="1" required>
                            <input type="hidden" name="max_qty[]">
                        </td>

                        <td>
                            <select class="form-select" name="barang_id[]" required>
                                @foreach($faktur->fakturPenjualanDetail as $detail)
                                <option value="{{ $detail->id }}"
                                    data-harga="{{ $detail->harga }}"
                                    data-maxqty="{{ $detail->jumlah_dipesan }}">
                                    {{ $detail->nama_barang }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="harga[]" class="form-control"
                                   readonly required>
                        </td>
                        <td>
                            <input type="text" name="total[]" class="form-control"
                                   readonly required>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-end mb-3">
                <button type="button" class="btn btn-outline-success btn-sm" id="add-row">
                    + Tambah Baris
                </button>
            </div>
            {{-- Alasan --}}
            <div class="mb-3">
                <label class="fw-bold">Alasan Pengembalian Barang</label>
                <textarea name="alasan_pengembalian" class="form-control"
                          rows="3" required></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('administrasi.memo-kredit.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Memo Kredit</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('script')
<script>
    const addBtn = document.getElementById('add-row');

    function formatRupiah(number) {
        return new Intl.NumberFormat("id-ID").format(number);
    }

    function updateRowNumber() {
        document.querySelectorAll('#table-retur tbody tr')
            .forEach((row, index) => row.cells[0].innerText = index + 1);
    }

    function updateSelectOptions() {
        let selects = document.querySelectorAll('select[name="barang_id[]"]');
        let selectedIds = [...selects].map(s => s.value);

        selects.forEach(select => {
            [...select.options].forEach(option => {
                option.disabled = selectedIds.includes(option.value) &&
                                  option.value !== select.value;
            });
        });

        addBtn.disabled = selectedIds.length >= selects[0].options.length;
    }

    function autoFillPrice(select) {
        let row = select.closest("tr");
        let option = select.selectedOptions[0];

        let harga = parseInt(option.dataset.harga) || 0;
        let maxQty = parseInt(option.dataset.maxqty) || 0;

        row.querySelector('input[name="harga[]"]').value = formatRupiah(harga);
        row.querySelector('input[name="max_qty[]"]').value = maxQty;

        calculateTotal(row);
    }

    function calculateTotal(row) {
        let qtyInput = row.querySelector('input[name="jumlah_dipesan[]"]');
        let maxQty = parseInt(row.querySelector('input[name="max_qty[]"]').value) || 0;
        let hargaStr = row.querySelector('input[name="harga[]"]').value.replace(/\./g, '');
        let totalInput = row.querySelector('input[name="total[]"]');

        let qty = parseInt(qtyInput.value) || 0;
        let harga = parseInt(hargaStr) || 0;

        if (qty > maxQty) {
            qtyInput.value = maxQty;
            qty = maxQty;
            alert("Jumlah melebihi jumlah pada faktur!🚧");
        }

        totalInput.value = qty > 0 ? formatRupiah(qty * harga) : "";
    }

    document.addEventListener('input', e => {
        if (e.target.name === "jumlah_dipesan[]") {
            calculateTotal(e.target.closest("tr"));
        }
    });

    document.addEventListener('change', e => {
        if (e.target.name === "barang_id[]") {
            autoFillPrice(e.target);
            updateSelectOptions();
        }
    });

    addBtn.addEventListener('click', () => {
        let tbody = document.querySelector('#table-retur tbody');
        let clone = tbody.rows[0].cloneNode(true);

        clone.querySelectorAll('input').forEach(input => input.value = "");
        clone.querySelector('select').selectedIndex = 0;

        tbody.appendChild(clone);

        autoFillPrice(clone.querySelector('select')); // ⬅️ NEW!
        updateRowNumber();
        updateSelectOptions();
    });

    // AUTO INIT for first row ⬇️⬇️⬇️
    document.addEventListener("DOMContentLoaded", () => {
        let firstSelect = document.querySelector('select[name="barang_id[]"]');
        autoFillPrice(firstSelect);
        updateSelectOptions();
    });

</script>
@endpush
