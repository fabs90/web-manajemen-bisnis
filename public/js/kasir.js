/**
 * Kasir POS Script
 * Digunakan pada modul Kasir Penjualan
 */

function updateStokInfo(selectElement) {
    if (!selectElement) return;
    const opt = selectElement.options[selectElement.selectedIndex];
    const stokValue = document.getElementById('stok-value');
    if (!stokValue) return;

    if (opt && opt.value) {
        stokValue.innerText = opt.dataset.stok || '0';
    } else {
        stokValue.innerText = '-';
    }
}
window.updateStokInfo = updateStokInfo;

document.addEventListener('DOMContentLoaded', function () {
    const kasirCard = document.querySelector('.kasir-card');
    if (!kasirCard) {
        return;
    }

    // Sembunyikan loading overlay setelah semua ter-load
    const hidePageLoader = () => {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    };

    if (document.readyState === 'complete') {
        hidePageLoader();
    } else {
        window.addEventListener('load', hidePageLoader);
    }

    // Inisialisasi metadata & printer script
    const metadataEl = document.getElementById('kasir-metadata');
    if (metadataEl) {
        const printerUrl = metadataEl.dataset.printerUrl || '/dist/assets/pos-printer.js';
        const printerScript = document.createElement('script');
        printerScript.src = printerUrl;
        document.head.appendChild(printerScript);

        const successMsg = metadataEl.dataset.success;
        const errorMsg = metadataEl.dataset.error;
        const isPrinterEnabled = metadataEl.dataset.printerEnabled === '1';

        if (successMsg && typeof Swal !== 'undefined') {
            const receiptJsonEl = document.getElementById('kasir-receipt-json');
            let receiptData = null;
            if (receiptJsonEl) {
                try {
                    receiptData = JSON.parse(receiptJsonEl.textContent);
                } catch (e) {
                    console.error('Gagal mengurai data struk JSON:', e);
                }
            }

            if (receiptData && isPrinterEnabled) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: successMsg,
                    showCancelButton: true,
                    confirmButtonText: '🖨️ Cetak Struk',
                    cancelButtonText: 'Tutup',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof PosPrinter !== 'undefined') {
                            const printer = new PosPrinter();
                            printer.printReceipt(receiptData);
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: successMsg,
                    timer: 2800,
                    showConfirmButton: true,
                });
            }
        }

        if (errorMsg && typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: errorMsg,
                showConfirmButton: true,
            });
        }
    }

    function parseRupiah(value) {
        if (!value) return 0;
        return parseFloat(value.toString().replace(/[^,\d]/g, '').replace(/,/g, '.')) || 0;
    }

    function formatRupiah(angka) {
        if (isNaN(angka) || angka === null || angka === undefined) return '';
        return Math.round(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    const rupiahInputs = document.querySelectorAll('.rupiah');
    rupiahInputs.forEach((input) => {
        input.addEventListener('input', (e) => {
            let angka = parseRupiah(e.target.value);
            e.target.value = angka === 0 ? '' : formatRupiah(angka);
        });

        if (input.value) {
            let angka = parseRupiah(input.value);
            input.value = angka === 0 ? '' : formatRupiah(angka);
        }
    });

    const kasirForm = document.querySelector('form[action*="kasir"]') || document.querySelector('form');
    if (kasirForm) {
        kasirForm.addEventListener('submit', function () {
            rupiahInputs.forEach((input) => {
                input.value = parseRupiah(input.value);
            });
        });
    }

    const keranjangTable = document.querySelector('#keranjang-table tbody');
    const selectBarang = document.getElementById('select-barang');
    const qtyInput = document.getElementById('qty');
    const grandTotalEl = document.getElementById('grand-total');
    const grandTotalInput = document.getElementById('grand-total-value');
    const inputBayar = document.getElementById('bayar');
    const inputKembalian = document.getElementById('kembalian');
    const inputKembalianValue = document.getElementById('kembalian-value');
    const btnSimpan = document.getElementById('btn-simpan');
    const selectJenisPembayaran = document.getElementById('jenis_pembayaran_id');
    const qrisContainer = document.getElementById('qris-container');
    const selectDiskon = document.getElementById('paket_diskon_id');
    const infoBayarOtomatis = document.getElementById('info-bayar-otomatis');

    function isMetodeNonTunai() {
        if (!selectJenisPembayaran || selectJenisPembayaran.selectedIndex < 0) return false;
        const opt = selectJenisPembayaran.options[selectJenisPembayaran.selectedIndex];
        if (!opt || !opt.value) return false;
        const nama = (opt.dataset.nama || opt.textContent || '').toLowerCase().replace(/[\s_-]/g, '');
        return nama === 'transferbank' || nama === 'qris' || nama === 'transfer';
    }

    function isQris() {
        if (!selectJenisPembayaran || selectJenisPembayaran.selectedIndex < 0) return false;
        const opt = selectJenisPembayaran.options[selectJenisPembayaran.selectedIndex];
        if (!opt || !opt.value) return false;
        const nama = (opt.dataset.nama || opt.textContent || '').toLowerCase().replace(/[\s_-]/g, '');
        return nama === 'qris';
    }

    // Inisialisasi stok barang
    if (selectBarang && selectBarang.value) {
        updateStokInfo(selectBarang);
    }

    const btnTambah = document.getElementById('btn-tambah');
    if (btnTambah && selectBarang && qtyInput && keranjangTable) {
        btnTambah.addEventListener('click', () => {
            const opt = selectBarang.selectedOptions[0];
            if (!opt || !opt.value) return;

            const nama = opt.dataset.nama || opt.textContent;
            const harga = parseFloat(opt.dataset.harga) || 0;
            const qty = parseInt(qtyInput.value) || 1;
            const subtotal = harga * qty;

            const row = `
                <tr>
                    <td>
                        ${nama}
                        <input type="hidden" name="id_barang_terjual[]" value="${opt.value}">
                    </td>
                    <td><input class="form-control qty text-center px-1" name="jumlah_barang_dijual[]" type="number" min="1" value="${qty}"></td>
                    <td>Rp ${harga.toLocaleString('id-ID')}</td>
                    <td class="subtotal" data-sub="${subtotal}">
                        Rp ${subtotal.toLocaleString('id-ID')}
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm hapus">X</button></td>
                </tr>
            `;
            keranjangTable.insertAdjacentHTML('beforeend', row);
            hitungTotal();
        });
    }

    // Update Qty
    if (keranjangTable) {
        keranjangTable.addEventListener('input', function (e) {
            if (!e.target.classList.contains('qty')) return;

            const row = e.target.closest('tr');
            const harga = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace(/[Rp .]/g, '')) || 0;
            const qty = parseInt(e.target.value) || 1;
            const subtotal = harga * qty;

            const subtotalEl = row.querySelector('.subtotal');
            subtotalEl.dataset.sub = subtotal;
            subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');

            hitungTotal();
        });

        // Hapus barang
        keranjangTable.addEventListener('click', function (e) {
            if (e.target.classList.contains('hapus')) {
                e.target.closest('tr').remove();
                hitungTotal();
            }
        });
    }

    // Hitung ulang bila bayar diketik manual (tunai)
    if (inputBayar) {
        inputBayar.addEventListener('input', hitungKembalian);
    }

    // Toggle QRIS Display & pembayaran non-tunai
    if (selectJenisPembayaran) {
        selectJenisPembayaran.addEventListener('change', function () {
            if (qrisContainer) {
                if (isQris()) {
                    qrisContainer.classList.remove('d-none');
                } else {
                    qrisContainer.classList.add('d-none');
                }
            }
            hitungKembalian();
        });
    }

    if (selectDiskon) {
        selectDiskon.addEventListener('change', hitungTotal);
    }

    function hitungTotal() {
        let subtotal = 0;
        document.querySelectorAll('.subtotal').forEach((el) => {
            subtotal += parseFloat(el.dataset.sub) || 0;
        });

        let diskonTotal = 0;
        if (selectDiskon) {
            const optDiskon = selectDiskon.options[selectDiskon.selectedIndex];

            if (optDiskon && optDiskon.value) {
                const jenis = optDiskon.dataset.jenis;
                const nilai = parseFloat(optDiskon.dataset.nilai) || 0;
                const minimal = parseFloat(optDiskon.dataset.minimal) || 0;
                const barangId = optDiskon.dataset.barang;

                if (subtotal >= minimal) {
                    if (barangId) {
                        // Diskon khusus 1 produk
                        document.querySelectorAll('#keranjang-table tbody tr').forEach((row) => {
                            const idBarangInput = row.querySelector('input[name="id_barang_terjual[]"]');
                            const idBarangTerjual = idBarangInput ? idBarangInput.value : null;
                            if (idBarangTerjual === barangId) {
                                const subRow = parseFloat(row.querySelector('.subtotal').dataset.sub) || 0;
                                if (jenis === 'persentase') {
                                    diskonTotal += (subRow * nilai) / 100;
                                } else {
                                    const qty = parseInt(row.querySelector('.qty').value) || 1;
                                    diskonTotal += nilai * qty;
                                }
                            }
                        });
                    } else {
                        // Diskon global
                        if (jenis === 'persentase') {
                            diskonTotal = (subtotal * nilai) / 100;
                        } else {
                            diskonTotal = nilai;
                        }
                    }
                }
            }
        }

        if (diskonTotal > subtotal) diskonTotal = subtotal;

        const grandTotal = Math.max(0, subtotal - diskonTotal);

        const diskonBox = document.getElementById('diskon-box');
        const diskonTotalVal = document.getElementById('diskon-total-value');
        if (diskonBox) diskonBox.innerText = '- Rp ' + diskonTotal.toLocaleString('id-ID');
        if (diskonTotalVal) diskonTotalVal.value = diskonTotal;

        if (grandTotalEl) grandTotalEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        if (grandTotalInput) grandTotalInput.value = grandTotal;

        hitungKembalian();
    }

    function hitungKembalian() {
        const total = parseFloat(grandTotalInput?.value) || 0;
        const nonTunai = isMetodeNonTunai();

        if (nonTunai) {
            // Transfer Bank atau QRIS: tidak perlu input uang dibayar, otomatis lunas
            if (inputBayar) {
                inputBayar.value = total > 0 ? formatRupiah(total) : '';
                inputBayar.readOnly = true;
                inputBayar.classList.add('bg-light');
            }
            if (infoBayarOtomatis) {
                infoBayarOtomatis.classList.remove('d-none');
            }
            if (inputKembalian) {
                inputKembalian.value = 'Rp 0';
            }
            if (inputKembalianValue) {
                inputKembalianValue.value = 0;
            }
            if (btnSimpan) {
                btnSimpan.disabled = !(total > 0 && selectJenisPembayaran?.value);
            }
            return;
        }

        // Metode pembayaran tunai
        if (inputBayar) {
            inputBayar.readOnly = false;
            inputBayar.classList.remove('bg-light');
        }
        if (infoBayarOtomatis) {
            infoBayarOtomatis.classList.add('d-none');
        }

        const bayar = parseRupiah(inputBayar ? inputBayar.value : 0);
        const selisih = bayar - total;

        if (inputKembalian && inputKembalianValue) {
            if (selisih < 0) {
                inputKembalian.value = 'Kurang (-) Rp ' + Math.abs(selisih).toLocaleString('id-ID');
                inputKembalianValue.value = selisih;
                if (btnSimpan) btnSimpan.disabled = true;
            } else {
                inputKembalian.value = 'Rp ' + selisih.toLocaleString('id-ID');
                inputKembalianValue.value = selisih;
                if (btnSimpan) {
                    btnSimpan.disabled = !(total > 0 && bayar >= total && selectJenisPembayaran?.value);
                }
            }
        }
    }
});
