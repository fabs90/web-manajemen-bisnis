<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="logo">
                    <a href="{{ route('dashboard') }}">
                        <p class="h5 fw-bold mb-0 text-primary">TRANSDIGITAL</p>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="theme-toggle d-flex gap-2 align-items-center mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 21 21">
                            <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2"
                                    opacity=".3"></path>
                                <g transform="translate(-210 -1)">
                                    <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                    <circle cx="220.5" cy="11.5" r="4"></circle>
                                    <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2">
                                    </path>
                                </g>
                            </g>
                        </svg>
                        <div class="form-check form-switch fs-6 mb-0">
                            <input class="form-check-input me-0" type="checkbox" id="toggle-dark"
                                style="cursor: pointer">
                            <label class="form-check-label"></label>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                            </path>
                        </svg>
                    </div>
                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu" style="padding-bottom: 120px;">
                <li class="sidebar-title">Menu Utama</li>

                <li class="sidebar-item {{ Request::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Request::is('dashboard/administrasi*') ? 'active' : '' }}">
                    <a href="{{ route('administrasi.surat.index') }}" class="sidebar-link">
                        <i class="bi bi-stack"></i>
                        <span>Administrasi</span>
                    </a>
                </li>

                <li class="sidebar-item has-sub {{ Request::is('dashboard/laporan-keuangan*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-file-bar-graph"></i>
                        <span>Laporan Keuangan</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="submenu-item {{ Request::is('dashboard/laporan-keuangan/neraca-awal*') ? 'active' : '' }}">
                            <a href="{{ route('laporan-keuangan.neraca-awal.index') }}" class="submenu-link">Neraca
                                Awal</a>
                        </li>
                        <li
                            class="submenu-item {{ Request::is('dashboard/laporan-keuangan/rugi-laba*') ? 'active' : '' }}">
                            <a href="{{ route('laporan-keuangan.rugi-laba') }}" class="submenu-link">Rugi Laba</a>
                        </li>
                        <li
                            class="submenu-item {{ Request::is('dashboard/laporan-keuangan/neraca-akhir*') ? 'active' : '' }}">
                            <a href="{{ route('laporan-keuangan.neraca-akhir') }}" class="submenu-link">Neraca
                                Akhir</a>
                        </li>
                    </ul>
                </li>

                <li
                    class="sidebar-item has-sub {{ Request::is('dashboard/transaksi-bisnis*') || Request::is('dashboard/keuangan*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-collection-fill"></i>
                        <span>Transaksi Bisnis</span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ Request::is('dashboard/keuangan/kasir*') ? 'active' : '' }}">
                            <a href="{{ route('keuangan.kasir.index') }}" class="submenu-link">Kasir</a>
                        </li>
                        <li class="submenu-item {{ Request::is('dashboard/keuangan/paket-diskon*') ? 'active' : '' }}">
                            <a href="{{ route('keuangan.paket-diskon.index') }}" class="submenu-link">Paket Diskon</a>
                        </li>
                        <li class="submenu-item {{ Request::is('dashboard/keuangan/pendapatan*') ? 'active' : '' }}">
                            <a href="{{ route('keuangan.pendapatan.list') }}" class="submenu-link">Penerimaan Kas
                                Perusahaan/Penjualan</a>
                        </li>
                        <li
                            class="submenu-item {{ Request::is('dashboard/keuangan/pengeluaran*') || Request::is('dashboard/keuangan/pengeluaran-kas-kecil*') ? 'active' : '' }}">
                            <a href="{{ route('keuangan.pengeluaran.list') }}" class="submenu-link">Pengeluaran Kas
                                Perusahaan/Pembelian</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item has-sub {{ Request::is('dashboard/barang*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-archive-fill"></i>
                        <span>Barang</span>
                    </a>
                    <ul class="submenu">
                        <li
                            class="submenu-item {{ Request::is('dashboard/barang') || Request::is('dashboard/barang/list*') || Request::is('dashboard/barang/create*') || Request::is('dashboard/barang/detail*') ? 'active' : '' }}">
                            <a href="{{ route('barang.index') }}" class="submenu-link">List Barang</a>
                        </li>
                        <li class="submenu-item {{ Request::is('dashboard/barang/kartu-gudang*') ? 'active' : '' }}">
                            <a href="{{ route('kartu-gudang.index') }}" class="submenu-link">Atur Kartu Gudang</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item {{ Request::is('dashboard/debitur-kreditur*') ? 'active' : '' }}">
                    <a href="{{ route('debitur-kreditur.list') }}" class='sidebar-link'>
                        <i class="bi bi-people-fill"></i>
                        <span>Debitur & Kreditur</span>
                    </a>
                </li>
                <li class="sidebar-item has-sub {{ Request::is('dashboard/retur*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-truck"></i>
                        <span>Retur</span>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item {{ Request::routeIs('retur.list-penjualan') ? 'active' : '' }}">
                            <a href="{{ route('retur.list-penjualan') }}" class="submenu-link">List Retur Penjualan</a>
                        </li>
                        <li class="submenu-item {{ Request::routeIs('retur.list-pembelian') ? 'active' : '' }}">
                            <a href="{{ route('retur.list-pembelian') }}" class="submenu-link">List Retur Pembelian</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-title">Pengaturan</li>

                <li class="sidebar-item {{ Request::is('dashboard/profile*') ? 'active' : '' }}">
                    <a href="{{ route('profile.edit') }}" class="sidebar-link">
                        <i class="bi bi-person-circle"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>

                <li class="sidebar-item {{ Request::is('dashboard/qris*') ? 'active' : '' }}">
                    <a href="{{ route('qris.index') }}" class="sidebar-link">
                        <i class="bi bi-qr-code"></i>
                        <span>QRIS</span>
                    </a>
                </li>

                <li class="sidebar-item {{ Request::is('dashboard/printer*') ? 'active' : '' }}">
                    <a href="{{ route('printer.index') }}" class="sidebar-link">
                        <i class="bi bi-printer"></i>
                        <span>Printer Thermal</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="#" class="sidebar-link text-danger" id="logout-link">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('logout-link')?.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Anda akan keluar dari sistem ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Keluar',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    });
</script>
