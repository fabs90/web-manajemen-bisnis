<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Home | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--page icon-->
    <link rel="icon" href="{{ asset('dist/assets/static/images/logo_web.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('dist/landing-page/landing-page.css') }}">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center fw-bold text-decoration-none" href="#">
                    <img src="{{ asset('dist/assets/static/images/logo_web.png') }}" alt="Logo" width="60"
                        height="60">
                    <div>
                        <span class="tagline small d-none d-lg-block">Administrasi Efektif & Otomatisasi Laporan
                            Keuangan</span>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto ">
                        <li class="nav-item"><a class="nav-link" href="#home">Beranda</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer text-black text-center py-4">
        <p class="m-0">&copy; 2025 Website Pembukuan. Semua hak dilindungi.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll untuk navigasi
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Animasi fade-in pada scroll
        window.addEventListener('scroll', function() {
            const elements = document.querySelectorAll('.card');
            elements.forEach(el => {
                if (el.getBoundingClientRect().top < window.innerHeight) {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }
            });
        });

        // Inisialisasi opacity awal untuk animasi
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s, transform 0.5s';
            });
        });
    </script>
</body>

</html>
