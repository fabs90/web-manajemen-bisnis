<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
    <div class="bg-white/80 backdrop-blur-md shadow-2xl rounded-2xl w-full max-w-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ config('app.name', 'MyApp') }}</h1>
            <p class="text-gray-600 text-sm mt-2">Selamat datang kembali 👋</p>
        </div>
        {{ $slot }}
    </div>
</body>
</html>
