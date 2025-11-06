@extends('landing-page.app')

@section('title', 'Home | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')

@section('content')
    <section id="home" class="hero text-white text-center py-5"
        style="background-image: url('https://images.unsplash.com/photo-1541064828014-503911d13103?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=870'); background-size: cover; background-position: center; position: relative;">
        <div class="overlay"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
        </div>
        <div class="container position-relative">
            <h2 class="display-4 fw-bold mb-3">Selamat Datang</h2>
            <h2 class="mb-4 hero-desc">Website Pengelolaan Administrasi dan Transaksi Bisnis yang Terintegrasi dan
                Berkelanjutan
                Bagi Koperasi, UKM dan Kelompok Nelayan</h2>
        </div>
    </section>

    <section id="services" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" style="color: #3bb273;">Layanan Kami</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
                            class="card-img-top" alt="Pencatatan Transaksi" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold" style="color: #1b3a5a;">Pengelolaan Administrasi dan Transaksi
                                Bisnis</h5>
                            <p class="card-text">Mengatur administrasi dan Mencatat transaksi masuk/keluar dengan cepat dan
                                akurat guna peningkatan efisiensi dan otomatisasi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
                            class="card-img-top" alt="Laporan Keuangan" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold" style="color: #1b3a5a;">Laporan Keuangan</h5>
                            <p class="card-text">Generate laporan bulanan dan tahunan secara otomatis untuk analisis
                                mendalam dan keputusan bisnis yang tepat.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60"
                            class="card-img-top" alt="Konsultasi" style="height: 200px; object-fit: cover;">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold" style="color: #1b3a5a;">Konsultasi</h5>
                            <p class="card-text">Dapatkan saran dari para ahli dalam manajemen bisnis kami, guna
                                mengoptimalkan keberhasilan Bisnis Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="application-flow" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" style="color: #3bb273;">Alur Penggunaan Sistem Keuangan</h2>
            <div class="row g-4">
                <div class="col">
                    <img src="{{ asset('dist/assets/static/images/alur_penggunaan_website.jpg') }}" class="card-img-top"
                        alt="Konsultasi" object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4 fw-bold" style="color: #3bb273;">Hubungi Kami</h2>
                    <p class="mb-4" style="color: #3bb273;">Ada pertanyaan? Jangan ragu untuk menghubungi kami. Tim kami
                        siap membantu Anda.</p>
                    <img src="{{asset("dist/assets/static/images/bg-warung.webp")}}"
                        alt="Contact Us" class="img-fluid rounded shadow" style="max-height: 300px;">
                </div>
                <div class="col-md-6">
                    <form class="bg-white p-4 rounded shadow" action="https://formspree.io/f/xqagvwpg" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold" style="color: #3bb273;">Nama</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Masukkan nama Anda">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold" style="color: #3bb273;">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Masukkan email Anda">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label fw-bold" style="color: #3bb273;">Pesan</label>
                            <textarea class="form-control" id="message" rows="4" name="message" placeholder="Tulis pesan Anda di sini"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"
                            style="background-color: #1b3a5a; border-color: #1b3a5a;">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
