<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,900&display=swap" rel="stylesheet" />

    <!-- Native CSS Styles -->
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'figtree', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Logo colors: Dark Navy Blue */
            background: linear-gradient(to bottom right, #0b1935, #1d335c);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .error-card {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border-radius: 1.5rem;
            width: 100%;
            max-width: 28rem;
            padding: 2.5rem;
            text-align: center;
            margin: 1rem;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 900;
            margin: 0 0 1.5rem 0;
            /* Text gradient using logo's golden yellow */
            background: linear-gradient(to right, #dca813, #f5cc3b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .error-message {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 0.75rem 0;
        }

        .error-desc {
            color: #6b7280;
            font-size: 0.875rem;
            margin: 0 0 2rem 0;
            line-height: 1.5;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(to right, #dca813, #ebbe28);
            border-radius: 0.75rem;
            text-decoration: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: linear-gradient(to right, #c6950f, #dca813);
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .btn-home svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="error-code">
            @yield('code')
        </h1>
        
        <h2 class="error-message">
            @yield('message')
        </h2>
        
        <p class="error-desc">
            Maaf, sepertinya ada sesuatu yang salah atau halaman tidak dapat diakses saat ini.
        </p>
        
        <a href="{{ url('/') }}" class="btn-home">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
