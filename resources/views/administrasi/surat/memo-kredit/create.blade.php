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
                            <td>
                                @php
                                    $po = $faktur->suratPengirimanBarang?->pesananPembelian;
                                @endphp
                                @if ($po?->jenis == 'transaksi_masuk')
                                    {{ $po->pelanggan?->nama ?? '-' }}
                                    <input type="hidden" name="pelanggan_id" value="{{ $po->pelanggan_id }}">
                                @elseif ($po?->jenis == 'transaksi_keluar')
                                    {{ $po->supplier?->nama ?? '-' }}
                                    <input type="hidden" name="supplier_id" value="{{ $po->supplier_id }}">
                                @else
                                    -
                                @endif
                            </td>
                            <td style="width: 25%">Tanggal Memo Kredit<span class="text-danger">*</span></td>
                            <td>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}"
                                    required>
                            </td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>
                                @if ($po?->jenis == 'transaksi_masuk')
                                    {{ $po->pelanggan?->alamat ?? '-' }}
                                @else
                                    {{ $po->supplier?->alamat ?? '-' }}
                                @endif
                            </td>
                            <td>Nomor Memo Kredit<span class="text-danger">*</span></td>
                            <td>
                                <input type="text" name="nomor_memo" value="{{ old('nomor_memo') }}" class="form-control"
                                    required>
                            </td>
                        </tr>
                        <tr>
                            <td>Nomor Faktur</td>
                            <td>{{ $faktur->kode_faktur ?? '-' }}</td>
                            <td>Tanggal Faktur</td>
                            <td>{{ $faktur->tanggal_faktur ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Nomor Pesanan</td>
                            <td>{{ $faktur->suratPengirimanBarang?->pesananPembelian?->nomor_pesanan_pembelian ?? '-' }}
                            </td>
                            <td>Jenis Pengiriman</td>
                            <td>{{ $faktur->suratPengirimanBarang?->jenis_pengiriman ?? '-' }}</td>
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
                                <input type="number" name="jumlah_dikembalikan[]" class="form-control" min="1"
                                    required>
                                <input type="hidden" name="max_qty[]">
                            </td>

                            <td>
                                <select class="form-select" name="barang_id[]" required>
                                    @foreach ($faktur->suratPengirimanBarang->pesananPembelian->pesananPembelianDetail as $detail)
                                        <option value="{{ $detail->id }}" data-harga="{{ $detail->harga }}"
                                            data-maxqty="{{ $detail->kuantitas }}">
                                            {{ $detail->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="harga[]" class="form-control bg-light" readonly required>
                            </td>
                            <td>
                                <input type="text" name="total[]" class="form-control" readonly required>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="fw-bold text-end">Grand Total</td>
                            <td class="fw-bold">
                                <input type="text" id="grand-total" class="form-control bg-light fw-bold" readonly>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-outline-success btn-sm" id="add-row">
                        + Tambah Baris
                    </button>
                </div>
                {{-- Alasan --}}
                <div class="mb-3">
                    <label class="fw-bold">Alasan Pengembalian Barang</label>
                    <textarea name="alasan_pengembalian" class="form-control" rows="3" required></textarea>
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
                    if (option.value === select.value || !selectedIds.includes(option.value)) {
                        option.style.display = "";
                        option.disabled = false;
                    } else {
                        option.style.display = "none";
                        option.disabled = true;
                    }
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
            let qtyInput = row.querySelector('input[name="jumlah_dikembalikan[]"]');
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

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('input[name="total[]"]').forEach(input => {
                let value = parseInt(input.value.replace(/\./g, '')) || 0;
                grandTotal += value;
            });
            document.getElementById('grand-total').value = grandTotal > 0 ? formatRupiah(grandTotal) : "";
        }

        document.addEventListener('input', e => {
            if (e.target.name === "jumlah_dikembalikan[]") {
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
