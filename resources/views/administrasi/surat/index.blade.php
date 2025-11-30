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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }

        .menu-card[data-tooltip]:hover::after {
            opacity: 1;
            bottom: 120%;
        }
    </style>

    <div class="menu-grid">
        <a href="{{ route('administrasi.surat-masuk.create') }}" class="menu-card text-black" style="background:#ffff66"
            data-tooltip="Dokumen yang diterima dari luar perusahaan, seperti surat pemberitahuan, permohonan, maupun penawaran.">
            SURAT MASUK
        </a>

        <a href="{{ route('administrasi.surat-keluar.index') }}" class="menu-card text-black" style="background:#ffe5b4"
            data-tooltip="Surat resmi yang dikirim perusahaan ke pihak luar, baik balasan maupun pemberitahuan.">
            SURAT KELUAR
        </a>

        <a href="{{ route('administrasi.kas-kecil.index') }}" class="menu-card text-black" style="background:#d7eaff"
            data-tooltip="Catatan transaksi pengeluaran kecil sehari-hari yang dibayar secara tunai.">
            KAS KECIL
        </a>

        <a href="{{ route('administrasi.agenda-telpon.index') }}" class="menu-card text-black" style="background:#c7d6ef"
            data-tooltip="Pencatatan janji atau kesepakatan hasil komunikasi melalui telepon.">
            JANJI TELEPON
        </a>

        <a href="{{ route('administrasi.agenda-perjalanan.index') }}" class="menu-card text-black"
            style="background:#ffd400"
            data-tooltip="Dokumen rencana perjalanan dinas berisi jadwal, agenda, dan estimasi kegiatan.">
            ITINERARI PERJALANAN
        </a>

        <a href="{{ route('administrasi.janji-temu.index') }}" class="menu-card text-black" style="background:#c1e1c1"
            data-tooltip="Catatan jadwal pertemuan dengan pihak terkait, lengkap dengan waktu dan lokasi.">
            JANJI TEMU
        </a>

        <a href="{{ route('administrasi.surat-undangan-rapat.index') }}" class="menu-card text-black"
            style="background:#ffd97a"
            data-tooltip="Surat resmi untuk mengundang peserta menghadiri rapat dengan agenda tertentu.">
            SURAT UNDANGAN RAPAT
        </a>

        <a href="{{ route('administrasi.rapat.index') }}" class="menu-card text-black" style="background:#e9f4dd"
            data-tooltip="Dokumen hasil rapat berisi keputusan, ringkasan diskusi, dan tindak lanjut.">
            BERITA ACARA RAPAT
        </a>

        <a href="{{route('administrasi.rapat.hasil-keputusan.index')}}" class="menu-card text-black" style="background:#eeeeee"
            data-tooltip="Dokumen pemesanan resmi barang atau jasa kepada supplier (Purchase Order).">
            SURAT HASIL KEPUTUSAN RAPAT
        </a>

        <a href="{{route('administrasi.faktur-penjualan.index')}}" class="menu-card text-black" style="background:#c6d9f1"
            data-tooltip="Dokumen penagihan kepada pelanggan berisi daftar barang/jasa yang dijual.">
            FAKTUR PENJUALAN
        </a>

        <a href="{{route('administrasi.spb.index')}}" class="menu-card text-black" style="background:#63f28f"
            data-tooltip="Dokumen yang menyertai pengiriman barang sebagai bukti barang telah dikirim.">
            SURAT PENGIRIMAN BARANG
        </a>

        <a href="#" class="menu-card text-black" style="background:#eeeeee"
            data-tooltip="Dokumen permohonan resmi kepada pihak terkait untuk melakukan suatu kegiatan.">
            MEMO KREDIT
        </a>

        <a href="#" class="menu-card text-black" style="background:#ffb3e6"
            data-tooltip="Arsip lengkap agenda rapat dan catatan jalannya rapat (notulen).">
            AGENDA DAN NOTULEN RAPAT
        </a>

    </div>

    {{-- letakkan ini sebelum @endsection, setelah markup menu-grid --}}
    <style>
        /* --- Coming Soon badge --- */
        .coming-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff6b6b, #ffb86b);
            color: white;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .4px;
            transform-origin: center;
            box-shadow: 0 6px 18px rgba(255, 107, 107, 0.18);
            display: inline-block;
            pointer-events: none;
            animation: pulseBadge 1.8s infinite;
            z-index: 50;
        }

        @keyframes pulseBadge {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.07);
                opacity: 0.92;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* --- Ripple on click --- */
        .cs-ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 700ms ease-out;
            background: rgba(255, 255, 255, 0.45);
            pointer-events: none;
            z-index: 40;
        }

        @keyframes ripple {
            to {
                transform: scale(6);
                opacity: 0;
            }
        }

        /* --- Subtle glow card when hover --- */
        .menu-card.cs-soon:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            filter: saturate(1.05);
        }

        /* --- Tiny modal / toast --- */
        .cs-modal {
            position: fixed;
            left: 50%;
            bottom: 6%;
            transform: translateX(-50%) translateY(20px);
            background: linear-gradient(180deg, #ffffff, #fff8f0);
            border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 12px 40px rgba(18, 18, 18, 0.12);
            display: flex;
            gap: 12px;
            align-items: center;
            min-width: 260px;
            max-width: calc(100% - 40px);
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity .28s ease, transform .28s cubic-bezier(.2, .9, .3, 1);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .cs-modal.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .cs-modal .icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-weight: 700;
            background: linear-gradient(135deg, #ffd6a5, #ffb4a2);
            color: #602d00;
            box-shadow: 0 6px 18px rgba(255, 160, 120, 0.16);
        }

        .cs-modal .text {
            font-size: 14px;
            line-height: 1.2;
            color: #12202a;
        }

        .cs-modal .cta {
            margin-left: auto;
            font-size: 13px;
            color: #0f62fe;
            cursor: pointer;
            text-decoration: underline;
        }

        /* small-screen tweaks */
        @media (max-width: 576px) {
            .coming-badge {
                top: 8px;
                right: 8px;
                font-size: 11px;
                padding: 5px 8px;
            }

            .cs-modal {
                left: 50%;
                bottom: 8%;
                min-width: 200px;
                padding: 12px;
            }
        }
    </style>

    <!-- Coming soon modal (insert once in page) -->
    <div id="cs-modal" class="cs-modal" role="status" aria-live="polite" aria-hidden="true" style="display:none;">
        <div class="icon">✨</div>
        <div class="text">
            <div style="font-weight:700">Coming Soon</div>
            <div style="opacity:.8; font-size:13px; margin-top:4px">Fitur ini sedang dikembangkan. Nantikan pembaruan
                berikutnya!</div>
        </div>
    </div>

    <script>
        (function() {
            // Pilih semua menu-card anchor yang href="#" atau yang punya data-coming-soon="true"
            const anchors = Array.from(document.querySelectorAll('.menu-grid a'))
                .filter(a => a.getAttribute('href') === '#' || a.dataset.comingSoon === "true");

            if (!anchors.length) return;

            // buat badge dinamis & attach behavior
            anchors.forEach(a => {
                // tambahkan class untuk styling hover
                a.classList.add('cs-soon');

                // wrapper relatif supaya badge & ripple bisa absolute
                a.style.position = a.style.position || 'relative';

                // buat badge (sekali)
                const badge = document.createElement('span');
                badge.className = 'coming-badge';
                badge.innerText = 'COMING SOON';
                badge.setAttribute('aria-hidden', 'true');
                // posisikan sedikit di pojok (kamu bisa adjust)
                badge.style.top = '10px';
                badge.style.right = '10px';

                // tambahkan badge ke anchor
                a.appendChild(badge);

                // click handler: tampilkan ripple + modal, cegah navigasi
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    showModalOnce();
                });

                // juga keyboard accessible: Enter/Space
                a.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        showModalOnce();
                    }
                });
            });

            // Modal (toast) logic: show only briefly; pressing Info triggers route/help
            const modal = document.getElementById('cs-modal');
            let modalTimer = null;

            function showModalOnce() {
                if (!modal) return;
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                void modal.offsetWidth; // reflow for transition
                modal.classList.add('show');

                // auto hide in 3.2s
                clearTimeout(modalTimer);
                modalTimer = setTimeout(hideModal, 3200);
            }

            function hideModal() {
                if (!modal) return;
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                // hide after transition
                setTimeout(() => modal.style.display = 'none', 300);
            }

            // OPTIONAL: apabila kamu ingin menandai manual, tambahkan attribute `data-coming-soon="true"` ke <a>
            // contoh: <a href="/some" data-coming-soon="true">Feature</a>
        })();
    </script>
@endsection
