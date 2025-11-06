<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-store" />

    <title>@yield('page-title')</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_web.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('./dist/assets/compiled/css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('./dist/assets/compiled/css/app-dark.css') }}" />
    <link rel="stylesheet" href="{{ asset('./dist/assets/compiled/css/iconly.css') }}" />
    <link rel="stylesheet" href="{{ asset('./datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('./select2/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('./dist/assets/dashboard.css') }}">
    @stack('styles')

</head>
<script src="{{ asset('./dist/assets/static/js/initTheme.js') }}"></script>
<div id="app">
