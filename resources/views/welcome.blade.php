@extends('landing-page.app')

@section('title', 'Home | Website Pembukuan')

@section('content')
<section id="home" class="hero bg-primary text-white text-center py-5" style="background-image: url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'); background-size: cover; background-position: center; position: relative;">
    <div class="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);"></div>
    <div class="container position-relative">
        <h1 class="display-4 fw-bold mb-3">Selamat Datang di Website Pembukuan</h1>
        <p class="lead mb-4">Kelola keuangan bisnis Anda dengan mudah dan akurat. Kami siap membantu Anda mencapai kesuksesan finansial.</p>
        <a href="#services" class="btn btn-light btn-lg px-4 py-2">Pelajari Lebih Lanjut</a>
    </div>
</section>

<section id="services" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Layanan Kami</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Pencatatan Transaksi" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Pencatatan Transaksi</h5>
                        <p class="card-text">Catat semua transaksi masuk dan keluar dengan cepat dan akurat untuk memudahkan pengelolaan keuangan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Laporan Keuangan" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Laporan Keuangan</h5>
                        <p class="card-text">Generate laporan bulanan dan tahunan secara otomatis untuk analisis mendalam dan keputusan bisnis yang tepat.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" class="card-img-top" alt="Konsultasi" style="height: 200px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Konsultasi</h5>
                        <p class="card-text">Dapatkan saran dari ahli pembukuan kami untuk mengoptimalkan strategi keuangan bisnis Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-4 fw-bold">Hubungi Kami</h2>
                <p class="mb-4">Ada pertanyaan? Jangan ragu untuk menghubungi kami. Tim kami siap membantu Anda.</p>
                <img src="https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60" alt="Contact Us" class="img-fluid rounded shadow" style="max-height: 300px;">
            </div>
            <div class="col-md-6">
                <form class="bg-white p-4 rounded shadow">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Nama</label>
                        <input type="text" class="form-control" id="name" placeholder="Masukkan nama Anda">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Masukkan email Anda">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label fw-bold">Pesan</label>
                        <textarea class="form-control" id="message" rows="4" placeholder="Tulis pesan Anda di sini"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.hero {
    min-height: 70vh;
    display: flex;
    align-items: center;
}

.card:hover {
    transform: translateY(-15px);
    transition: transform 0.3s ease;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: scale(1.05);
}
</style>
@endsection
