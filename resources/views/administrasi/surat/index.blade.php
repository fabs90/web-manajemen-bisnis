@extends('layouts.partial.layouts')
@section('page-title', 'Administrasi Surat | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')
@section('section-heading', 'Administrasi Surat')
@section('section-row')

<style>
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
    }

    .menu-card {
        padding: 25px 10px;
        text-align: center;
        font-weight: bold;
        border-radius: 8px;
        color: #000;
        cursor: pointer;
        transition: all .2s ease-in-out;
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .menu-card:hover {
           transform: translateY(-5px);
           box-shadow: 0 4px 10px rgba(0,0,0,0.2);
       }
    /* Tooltip */
    .menu-card[data-tooltip] {
        position: relative;
    }

    .menu-card[data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 110%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: #fff;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        width: max-content;
        max-width: 220px;
        line-height: 1.4;
        text-align: center;
        opacity: 0;
        pointer-events: none;
        transition: 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        z-index: 10;
    }

    .menu-card[data-tooltip]:hover::after {
        opacity: 1;
        bottom: 120%;
    }
</style>

<div class="menu-grid">
    <a href="{{route('administrasi.surat-masuk.create')}}"
       class="menu-card text-black"
       style="background:#ffff66"
       data-tooltip="Dokumen yang diterima dari luar perusahaan, seperti surat pemberitahuan, permohonan, maupun penawaran.">
       SURAT MASUK
    </a>

    <a href="{{route('administrasi.surat-keluar.index')}}"
       class="menu-card text-black"
       style="background:#ffe5b4"
       data-tooltip="Surat resmi yang dikirim perusahaan ke pihak luar, baik balasan maupun pemberitahuan.">
       SURAT KELUAR
    </a>

    <a href="{{route('administrasi.kas-kecil.index')}}"
       class="menu-card text-black"
       style="background:#d7eaff"
       data-tooltip="Catatan transaksi pengeluaran kecil sehari-hari yang dibayar secara tunai.">
       KAS KECIL
    </a>

    <a href="{{route('administrasi.agenda-telpon.index')}}"
       class="menu-card text-black"
       style="background:#c7d6ef"
       data-tooltip="Pencatatan janji atau kesepakatan hasil komunikasi melalui telepon.">
       JANJI TELEPON
    </a>

    <a href="{{route('administrasi.agenda-perjalanan.index')}}"
       class="menu-card text-black"
       style="background:#ffd400"
       data-tooltip="Dokumen rencana perjalanan dinas berisi jadwal, agenda, dan estimasi kegiatan.">
       ITINERARI PERJALANAN
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#c1e1c1"
       data-tooltip="Catatan jadwal pertemuan dengan pihak terkait, lengkap dengan waktu dan lokasi.">
       JANJI TEMU
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#ffd97a"
       data-tooltip="Surat resmi untuk mengundang peserta menghadiri rapat dengan agenda tertentu.">
       SURAT UNDANGAN RAPAT
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#e9f4dd"
       data-tooltip="Dokumen hasil rapat berisi keputusan, ringkasan diskusi, dan tindak lanjut.">
       BERITA ACARA RAPAT
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#eeeeee"
       data-tooltip="Dokumen pemesanan resmi barang atau jasa kepada supplier (Purchase Order).">
       SURAT PESANAN PEMBELIAN
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#c6d9f1"
       data-tooltip="Dokumen penagihan kepada pelanggan berisi daftar barang/jasa yang dijual.">
       FAKTUR PENJUALAN
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#63f28f"
       data-tooltip="Dokumen yang menyertai pengiriman barang sebagai bukti barang telah dikirim.">
       SURAT PENGIRIMAN BARANG
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#eeeeee"
       data-tooltip="Dokumen permohonan resmi kepada pihak terkait untuk melakukan suatu kegiatan.">
       SURAT PERMOHONAN
    </a>

    <a href="#"
       class="menu-card text-black"
       style="background:#ffb3e6"
       data-tooltip="Arsip lengkap agenda rapat dan catatan jalannya rapat (notulen).">
       AGENDA DAN NOTULEN RAPAT
    </a>

</div>
@endsection
